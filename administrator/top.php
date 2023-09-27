<div class="top">
	<div class="logo"><a href="<?php echo $site_root;?>"><img src="../images/logo.png" /></a></div>
	<div class="title"><h2><?php echo $admin_title;?></h2></div>
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
			<?php if (isset($uname)){?>
			<ul class="nav navbar-nav">
				<li <?php if ($menuitem == 1) echo 'class="active"';?>>
					<a href="index.php">Users</a>
				</li>
				<li <?php if ($menuitem == 2) echo 'class="active"';?>>
					<a href="stations.php">Stations</a>
				</li>
				<li class="dropdown<?php if ($menuitem == 3) echo ' active';?>">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">Metrics
					<span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="categories.php">Manage Categories</a></li>
						<li><a href="metrics.php">Manage Metrics</a></li>
					</ul>
				</li>
			</ul>
			<?php }?>
			<ul class="nav navbar-nav navbar-right">
			<?php if (isset($uname)){?>
				<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> <?php echo $logout_btn_text;?></a></li>
				<a class="logoutbtn" href="logout.php"></a>
			<?php }else{?>
				<li <?php if ($menuitem == 4) echo 'class="active"';?>><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> <?php echo $login_btn_text;?></a></li>
			<?php }?>
			</ul>
			</div>
		</div>
	</nav>
</div>
