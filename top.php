<div class="top">
<div class="logo_wrapper">
	<div class="logo"><a href="<?php echo $site_root;?>"><img src="images/logo.png" /></a></div>
</div>
	
	<div class="infobar">
		<?php if (isset($uname)){
			echo $greeting.$uname;?>
		<?php }?>
	</div>
	<div id="msg_box"></div>
	<nav class="mainmenu navbar navbar-inverse">
		<div class="container-fluid">
		<div class="navbar-header">
		  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#mainNavbar">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		  </button>
		</div>
		<div class="collapse navbar-collapse" id="mainNavbar">
		<ul class="nav navbar-nav">
			<li <?php if ($menuitem == 1) echo 'class="active"';?>>
				<a href="index.php">Home</a>
			</li>
<?php 	// Menu items only for logged in users
		if(isset($uid)){
?>
			<li <?php if ($menuitem == 2) echo 'class="active"';?>>
				<a href="data_analysis.php">Data Analysis</a>
			</li>
			<?php if($_SESSION['urole'] == 1 || $_SESSION['urole'] == 2 ){?>
			<li <?php if ($menuitem == 3) echo 'class="active"';?>>
				<a href="upload_measurements.php">Upload Measurements</a>
			</li>
			<?php }?>
<?php }?>
			<li class="dropdown<?php if ($menuitem == 4) echo ' active';?>">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#">About
				<span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li><a href="partners.php">Partners</a></li>
					<li><a href="faq.php">F.A.Q.</a></li>
				</ul>
			</li>
			<li <?php if ($menuitem == 5) echo 'class="active"';?>>
				<a href="contacts.php">Contacts</a>
			</li>
		</ul>
		<ul class="nav navbar-nav navbar-right">
		<?php if (isset($uname)){?>
			<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> <?php echo $logout_btn_text;?></a></li>
			<a class="logoutbtn" href="logout.php"></a>
		<?php }else{?>
			<li <?php if ($menuitem == 6) echo 'class="active"';?>><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> <?php echo $login_btn_text;?></a></li>
		<?php }?>
		</ul>
		</div>
		</div>
	</nav>
</div>
