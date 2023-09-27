<?php
	require_once 'helper.php';
	$menuitem = 3;
	
	// deny access to public
	if (!isset($uid)){
		header('Location: index.php');
	}

?>
<!DOCTYPE html>
<html>
<head>
	<?php include 'head.php'; ?>
	<script src="js/dropzone.js"></script>
	<link rel="stylesheet" href="css/dropzone.css">
</head>
<body>
	<div class="page-wrapper">
		<div class="container main-container">
			<?php include 'top.php'; ?>
			<div class="content-area">
				<div id="parameters">
					<div class="row">
						<div class="title">
								<label>Upload Measurements</label>
						</div>
						<div class="upload-content">
							Drop or click to select files
							<form id="myAwesomeDropzone" action="import_nano.php" class="dropzone"></form>
							<div id="messages" style="margin-top:15px">
								
								
							</div>
						</div>
						
						
						
					</div>
				</div>
				
				
			</div>
		</div>
		<?php include 'footer.php'; ?>
	</div>
	
</body>
<script>

	

	 Dropzone.options.myAwesomeDropzone = {
		addRemoveLinks: true,
		accept: function(file, done) {
            //console.log(file);
            var res = file.name.split("."); 
            if (res[res.length-1] != "csv") {
                done("Error! Files of this type are not accepted");
                var msg = "<p  id='"+(file.lastModified+"t"+file.size)+"'>File: <strong>" + file.name +
							"</strong> is not accepted";
               output(msg);
            }
            else { done(); }
       },
            success: function(file, response){
				console.log(response)
                //alert(response);
                file.test = "12";
                data = JSON.parse(response);
            		
                if(data['status'] == 1)
                {
                	 var msg = "<p id='"+(file.lastModified+"t"+file.size)+"'>File: <strong>" + data["name"] +
					" | </strong> inserted <strong>" + data['inserted'] + "</strong> and updated <strong>" + data['updated'] + "</strong> measurements | <a href='"+data['log_file']+"'>Download Analytic report</a>";
               		output(msg);
               		 file.previewElement.classList.add("dz-success");
                }
                else if(data['status'] == 2)
                {
                	
                	var msg = "<p id='"+(file.lastModified+"t"+file.size)+"'>File: <strong>" + data["name"] +
                		" | </strong> <span style='color:#f00;'>" + data["error"] + "</span>";
            		output(msg);
            		 file.previewElement.classList.add("dz-error");
                }else
                {
                	var msg = "<p id='"+(file.lastModified+"t"+file.size)+"'>File: <strong>" + data["name"] +
                		" | </strong> inserted <strong>" + data['inserted'] + "</strong>, updated <strong>" + data['updated'] + 
                		"</strong> and <span style='color:#f00;'>failed " + data["failed"] + "</span> measurements | <a href='"+data['log_file']+"'>"+data['error']+"</a>";
            		output(msg);
            		
            		if(data['inserted'] == 0 && data['updated'] == 0)
        		 		file.previewElement.classList.add("dz-error");
            		else
            		{
						
            			file.previewElement.classList.add("dz-warming");
            		}
            			
                }
                
               
               
            }
            
        };
        
        
        function output(msg)
        {
        	 var m = document.getElementById('messages');
				m.innerHTML = msg + m.innerHTML;
        }
</script>
</html>
