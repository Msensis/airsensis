#!/usr/bin/env python3.6
# -*- coding: utf-8 -*-
"""

Created on Thu Apr 19 10:50:16 2018
@author: Sergiu <s.ungureanu@msensis.com>"

"""

import os
from pymongo import MongoClient
import csv
import mysql.connector
import datetime
import re
import shutil
import logging
import fnmatch
import sys
import datetime
from datetime import timedelta
import traceback




logger = logging.getLogger('realtimeprocessor.py')
hdlr = logging.FileHandler('/var/www/html/airsensis/airfile_5.log')
formatter = logging.Formatter('%(asctime)s %(levelname)s %(message)s')
hdlr.setFormatter(formatter)
logger.addHandler(hdlr)
logger.setLevel(logging.ERROR)

config = {
    'user': 'root', #user
    'password': 'nano', #password
    'host': '127.0.0.1', #host
    'database': 'nanomonitor', #database
    'charset': 'utf8' #charset
    }

path = '/home/airftp/'
#path = '/home/airsensis/testt/'

destination_path = '/home/airsensis/transferednanofiles/'
false_ids_path = '/home/airsensis/false_ids/'

dummy_path = '/home/airsensis/noncompliantnanofiles/'

mandatory_HEADERS = ['StationID', 'Date', 'Time']

optional_HEADERS = ['Location', 'Latitude', 'Longitude', 'Altitude', 'Comment', 'TimeZone']

metrics = []
station = []
labels = []
exist = []
check = {}

#take absolute path from files
abs_source=[]
source = os.listdir(path)
if len(source) == 0:
    sys.exit();
for relpathfile in source:
    abs_source.append(path+relpathfile)

client = MongoClient('localhost', 27017)
db = client.nanomonitor

def getMetrics():
    try:
        cnx = mysql.connector.connect(**config)
        cursor = cnx.cursor(dictionary=True)
        query = ('select label, min, max from metrics where category=2;')
        cursor.execute(query)
        metric = cursor.fetchall()
        for i in metric:
            metrics.append(i)
        cursor.close()
        cnx.close()
    except mysql.connector.Error as err:
        logger.error("Something went wrong in getMetrics: {}".format(err))

def getStations():
    try:

        cnx = mysql.connector.connect(**config)
        cursor = cnx.cursor(dictionary=True)
        query = ("select id from station")
        cursor.execute(query)
        stations = cursor.fetchall()
        for i in stations:
            station.append(i)
        cursor.close()
        cnx.close()

    except mysql.connector.Error as err:
        logger.error("Something went wrong in exist_station: {}".format(err))

def getTimezone(station_id):

    try:
        cnx = mysql.connector.connect(**config)
        cursor = cnx.cursor(dictionary=True)
        query = ("select timezone from station where id = %s")
        params=(station_id,)
        cursor.execute(query,params)
        timezone_ = cursor.fetchall()
        timezone=timezone_[0]
        cursor.close()
        cnx.close()
        return timezone
    except mysql.connector.Error as err:
        logger.error("Something went wrong in getTimezone: {}".format(err))


getMetrics()
getStations()


for iterator in range(0, len(metrics), 1):
    labels.append(metrics[iterator]['label'])
    
def checkMetricsInHeader(row):
    check = filter(lambda x: x not in labels and \
                             x not in optional_HEADERS and \
                             x not in mandatory_HEADERS, row.keys())
    return list(check)

def checkMandatoryInHeader(row):
    return set(mandatory_HEADERS).issubset(set(row.keys()))

def existStation(rows):
    return {'id': int(rows.get('StationID').lstrip("0"))} in station

def checkDate(date):
    if len(date) != 10:
        logger.error('Date is not valid: {}  the format should be like 14/02/2049'.format(date))
    else:
        check = datetime.datetime.strptime(date, '%d/%m/%Y')
        if not check:
            return False
        else:
            return True

def checkTime(time):
    check = datetime.datetime.strptime(time, '%H:%M:%S')
    if not check:
        return False
    else:
        return True

def createDateAndTimestamp(rows, station_id):
    date = rows.get('Date')
    time = rows.get('Time')
    checkDate(date)
    checkTime(time)
    time_obj = datetime.datetime.strptime(time, '%H:%M:%S')
    date_time_obj=datetime.datetime.strptime(date +' '+ time, '%d/%m/%Y %H:%M:%S')
    #get timezone of station
    timezone_=getTimezone(station_id)
    timezone = timezone_["timezone"]
    hours_str = timezone[1]+timezone[2]
    minutes_offset=int(hours_str)*60
    #make date_time_obj utc by subtracting the timezone as minutes
    date_time_obj_utc=date_time_obj - timedelta(minutes=minutes_offset)
    date_time_str_utc=datetime.datetime.strftime(date_time_obj_utc, '%d/%m/%Y %H:%M:%S')
    if checkDate(date) == True and checkTime(time) == True:
        rows['timestamp'] = datetime.datetime.strptime(date_time_str_utc, '%d/%m/%Y %H:%M:%S')


def checkLongitude(longitude):
    longitude
    longitude_check = re.compile('('r'\d+:('r'\d+:)?)?'r'\d+('r'\.'r'\d+)?\ (W|E)')

    if longitude_check.match(longitude):
        return True
    else:
        logger.warn('Wrong longtitude pattern {}'.format(longitude))
        return False

def checkLatitude(latitude):
    latitude
    latitude_check = re.compile('('r'\d+:('r'\d+:)?)?'r'\d+('r'\.'r'\d+)?'r'\ (N|S)')

    if latitude_check.match(latitude):
        return True
    else:
        logger.warn('wrong latitude pattern {}'.format(latitude))
        return False

def checkForCACondition(document):
    check = ['PM', 'T ambient', 'Diameter']

    if check[0] and check[1] and check[2] in document.keys():
        document['CA'] = document.get('PM')
    return document

def checkStationsValues(rows):
    try:
        checkType = int(rows.get('StationID'))
        checkType = isinstance(checkType, int)
        return checkType
    except ArithmeticError:
        logger.error('Station ID {} is not numeric'.format(rows.get('StationID')))

def parseFloat(string):
    """parse"""
    try:
        return float(string)
    except ValueError:
        return None

def parseInt(string):
    try:
        return int(string)
    except ValueError:
        return None

def parse(items):
    document = {}
    for key in items.keys():
        string = items[key]
        if key == 'StationID' or key == 'stationID':
            document['Station ID'] = string.lstrip("0")
        elif key == 'Location':
            document[key] = string
        elif key == 'timestamp':
            document[key] = string
        elif key == 'Latitude':
            if checkLatitude(string) == True:
                document[key] = string
        elif key == 'Longitude':
            if checkLongitude(string) == True:
                document[key] = string
        elif key == 'Altitude':
            document[key] = string
        elif key == 'Comment':
            document[key] = string
        elif key == 'TimeZone':
            document[key] = string
        else:
            val = parseFloat(string)
            if val == None:
                continue
            metric = list(filter(lambda x: x.get('label') == key, metrics))[0]
            if metric.get('min') != None:
                if metric.get('min') > val:
                    continue
            if metric.get('max') != None:
                if metric.get('max') < val:
                    continue
            if '.' in key:
                document[key.replace('.', 'point')] = val
            else:
                document[key] = val
    checkForCACondition(document)
    document['status'] = 'non validated'

    return document
source.sort()
#source.pop()logger
#print(abs_source)
for txt_file in source:
    dt = datetime.datetime.now()
    dt_string=dt.strftime('%Y-%m-%d %H:%M:%S')
    try:
        if not os.path.isdir(os.path.join(path, txt_file)):
            if fnmatch.fnmatch(txt_file, '*.txt'):
                m=re.match(r'(\d{7})(_|-)(\d{14}|\d{10})',txt_file)
                sID=m.group(1).lstrip("0")
                datetime_object = datetime.datetime.strptime(m.group(3),'%Y%m%d%H%M%S')
                with open(path+txt_file, 'r') as file:
                    data = csv.DictReader(file, delimiter=',')
                    error_counter = 0
                    for items in data:
                        if not checkMandatoryInHeader(items):
                            error_counter += 1
                            logger.error('Missing required field: {}'.format(checkMandatoryInHeader(items)))
                        elif checkMetricsInHeader(items):
                            error_counter += 1
                            logger.error('Field {} is not a metric'.format(checkMetricsInHeader(items)))
                        elif checkStationsValues(items) == None:
                            error_counter += 1
                            continue
                        else:
                            if existStation(items) == True:
                                createDateAndTimestamp(items, sID)
                                document = parse(items)
                                if sID!=document['Station ID']:
                                    logger.error(txt_file + ' Station ID in record '+document['Station ID']+' doesn\'t match the Station ID in file name '+sID+'.Ommiting row')
                                    continue
                                existing = db.nano.find({"Station ID":document['Station ID'], "timestamp":document['timestamp']})
                                for i in existing:
                                    exist.append(i)
                                for i in range(0, len(exist), 1):
                                    check['_id'] = exist[i]['_id']
                                if existing:
                                    db.nano.delete_one({'_id':check.get('_id')})
                                    db.nano.insert_one(document)
                                else:
                                    db.nano.insert_one(document)
                            else:
                                logger.error('Ommiting row')
                                continue
                if error_counter == 0:
                    shutil.move(path+txt_file, destination_path+dt_string+'_'+txt_file)
                else:
                    shutil.move(path+txt_file, dummy_path+dt_string+'_'+txt_file)
            else:
                shutil.move(path+txt_file, dummy_path+dt_string+'_'+txt_file)
    except Exception as e:
        logger.error('Skipping file {} due to error: {}'.format(txt_file, e) )
        print(traceback.format_exc())
        shutil.move(path+txt_file, dummy_path+dt_string+'_'+txt_file)
client.close()
