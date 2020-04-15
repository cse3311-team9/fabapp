<!DOCTYPE html>
<!--
 *   CC BY-NC-AS UTA FabLab 2016-2018
 *   FabApp V 0.91
 -->
<html lang="en">
<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="FabApp, track your equipment">
	<meta name="author" content="UTA FabLab">
	<link rel="shortcut icon" href="/images/fa-icon.png" type="image/png">
	
	<link href="/vendor/blackrock-digital/css/sb-admin-2.css" rel="stylesheet">
	<link href="/vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="/vendor/bs-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
	<link href="/vendor/datatables/css/dataTables.bootstrap.css" rel="stylesheet" type="text/css">
	<link href="/vendor/fabapp/fabapp.css" rel="stylesheet">
	<link href="/vendor/fontawesome/css/all.min.css" rel="stylesheet">
	<link href="/vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
	<link href="/vendor/morrisjs/morris.css" rel="stylesheet">
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
<?php
$staff = null;
ob_start();
session_start();
include_once ($_SERVER['DOCUMENT_ROOT'].'/connections/db_connect8.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/connections/ldap.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/class/all_classes.php');

date_default_timezone_set($sv['timezone']);
if(!$mysqli->query("SET NAMES 'utf8';")) throw new Exception("Could not set MySqli encoding to UTF-8");

if( isset($_SESSION['staff']) ){
	$staff = unserialize($_SESSION['staff']);
	$_SESSION['loc'] = $_SERVER['PHP_SELF'];
	//Logout if session has timed out.
	if ($_SESSION["timeOut"] < time()) {
		header("Location:/logout.php");
	}
	else {
		//echo $_SESSION["timeOut"] ." - ". time();
		$_SESSION["timeOut"] = (intval(time()) + $staff->getTimeLimit());
	}
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	if( isset($_POST['signBtn']) ){
		if ( empty($_POST["netID"])){
			$_SESSION['error_msg'] = 'No User Name';
		}
		elseif (empty($_POST["pass"]) ){
			$_SESSION['error_msg'] = 'Missing Password';
		}
		else {
			//Remove 3rd argument, define attribute in ldap.php
			$operator = AuthenticateUser($_POST["netID"],$_POST["pass"]);
			if (array_key_exists('netID', $_SESSION)){
				if ($_SESSION['netID'] != $_POST["netID"]){
					unset($_SESSION['loc']);
				}
				$_SESSION['netID'] = $_POST["netID"];
			}
			if (Users::regexUser($operator)) {
				$staff = Staff::withID($operator);
				//staff get either limit or limit_long as their auto logout timer
				if ($staff->getRoleID() > $sv["LvlOfStaff"])
					$staff->setTimeLimit( $sv["limit_long"] );
				else
					$staff->setTimeLimit( $sv["limit"] );
				//set the timeOut = current + limit of login
				$_SESSION["timeOut"] = (intval(time()) + $staff->getTimeLimit());
				$_SESSION["staff"] = serialize($staff);
				if ( isset($_SESSION['loc']) ){
					header("Location:$_SESSION[loc]");
				}
				if (!headers_sent()){
					echo "<script>window.location.href='/index.php';</script>";
				}
				exit();
			}
			else {
				echo "<script type='text/javascript'> window.onload = function(){goModal('Invalid','Invalid user name and/or password!', false)}</script>";
			}
		}
	}
	elseif( isset($_POST['searchBtn']) ){
		if(filter_input(INPUT_POST, 'searchField')){
			$searchField = filter_input(INPUT_POST, 'searchField');
			if(filter_input(INPUT_POST, 'searchType')){
				$searchType = filter_input(INPUT_POST, 'searchType');
				if(strcmp($searchType, "s_trans") == 0){
					$trans_id = $searchField;
					header("location:/pages/lookup.php?trans_id=$trans_id");
				}
				elseif (strcmp($searchType, "s_operator") == 0){
					$operator = $searchField;
					header("location:/pages/lookup.php?operator=$operator");
				}
				else {
					echo "<script type='text/javascript'> window.onload = function(){goModal('Invalid','Illegal Search Condition', false)}</script>";
				}
			}
			else {
				echo "<script type='text/javascript'> window.onload = function(){goModal('Invalid','Illegal Search Condition', false)}</script>";
			}
		}
		else {
			echo "<script type='text/javascript'> window.onload = function(){goModal('Invalid','Please enter a number.', false)}</script>";
		}
	}
	elseif( filter_input(INPUT_POST, 'pickBtn') !== null ){
		if( filter_input(INPUT_POST, 'pickField') !== null){
			if(!Users::regexUser(filter_input(INPUT_POST, 'pickField'))){
				echo "<script>window.onload = function(){goModal('Success',\"Invalid ID # ".filter_input(INPUT_POST, pickField)."\", true)}</script>";
			}
			else {
				$operator = filter_input(INPUT_POST, 'pickField');
				header("location:/pages/pickup.php?operator=$operator");
			}
		}
	}
}
//Display a Successful message from a previous page
if (isset($_SESSION['success_msg']) && $_SESSION['success_msg']!= ""){
	echo "<script>window.onload = function(){goModal('Success',\"$_SESSION[success_msg]\", true)}</script>";
	unset($_SESSION['success_msg']);
}
elseif (isset($_SESSION['error_msg']) && $_SESSION['error_msg']!= ""){
	echo "<script>window.onload = function(){goModal('Error',\"$_SESSION[error_msg]\", false)}</script>";
	unset($_SESSION['error_msg']);
}

include_once ($_SERVER['DOCUMENT_ROOT'].'/pages/sub/notificationSettings_Modal.php');
?>
</head>
<body>
	<div id="wrapper">
		<!-- Navigation -->
		<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0" id='navbar'>
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" id="navbar-brand" href="http://fablab.uta.edu"><img src="/images/FLlogo_143.png" type="image/png"></a>
			</div>
			<!-- /.navbar-header -->
			<ul class="nav navbar-top-links navbar-right">
				<!--php class Staff if not logged in-->
				<?php if(!isset($staff)){ ?>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#" id="loginlink"> 
							<i class="fas fa-sign-in-alt fa-lg"></i> <i class="fas fa-caret-down"></i>
						</a>
						<ul class="dropdown-menu dropdown-alerts">
							<form role="form" class="form-horizontal" method="POST" action="" autocomplete="off">
							<div class="form-group">
								<label for="email" class="col-sm-3 control-label">
									NetID</label>
								<div class="col-sm-9">
									<input type="text" class="form-control" id="netID" name="netID" placeholder="NetID" value="<?php if(isset($_SESSION['netID'])) echo $_SESSION['netID'];?>"/>
								</div>
							</div>
							<div class="form-group">
								<label for="exampleInputPassword1" class="col-sm-3 control-label">
									Password</label>
								<div class="col-sm-9">
									<input type="password" class="form-control" name="pass" placeholder="Password" />
								</div>
							</div>
							<div class="row">
								<div class="col-sm-12 col-sm-offset-1">
									<button type="submit" class="btn btn-primary btn-sm" name="signBtn" onclick="loadingModal()">
										Sign In</button>
									<a href="http://<?php echo $sv["forgotten"];?>">Forgot your password?</a>
								</div>
							</div>
							</form>
						</ul>
						<!-- /.dropdown-login -->
					</li>
				<!--php class Staff if logged in-->
				<?php }
				else {?>
					<li>
						<p style="color: white">Current User ID: <?php echo $staff->getOperator(); ?></p>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
							<i class="fas fa-bell fa-2x"></i> <i class="fas fa-caret-down"></i>
						</a>
						<?php if ($staff->getRoleID() < $sv['serviceTechnican'] || $staff->getRoleID() == $sv['LvlOfStaff']){ ?> <!-- Dropdown for operators with role less than that of serviceTechnician (Visitors, Learners, Learners with Rfid, Community Members) or role of Staff-->
							<ul class="dropdown-menu dropdown-user" style="padding-bottom: 0; padding-top: 0; width: 240px;">
							    <li style = "background-color: lightgrey; border: 1px solid #888; margin-bottom: 9px; padding: 6px 4px; color: black">Notifications</li>
								<?php $wait_position = Wait_queue::getWaitPosition($staff->getOperator());
								$ticket_history = $staff->history();
								if( !$wait_position && !$ticket_history ){ ?> 
									<p style="margin: 5px 0; padding-left: 30px">No recent notifications</p>
								<?php }
								else
								{
									if($wait_position){ ?> <!-- Disply active wait queue positions -->
										<li style = "margin:0 18px;">
											<!--<a href="/pages/info.php" onclick="loadingModal()">-->
											<i class="fas fa-list-ol"></i> <b>Queue Info:</b><br>
											<?php foreach ($wait_position as $device => $position){ ?>
			                               		<p style="margin: 0px; padding-left: 30px"><?php echo $position[0].": "."Position ".$position[1]." in line" ; ?></p>        
			                    			<?php } ?>
											<!--</a>-->
										</li>
										<li class="divider"></li>
									<?php }
									if($ticket_history){ ?> <!-- Display ticket balance and ticket status (Active, complete, cancelled etc) -->
										<li style ="margin:0 18px;">
											<!--<a href="/pages/info.php" onclick="loadingModal()"> -->
											<i class="fas fa-money-check-alt"></i> <b>Balance:</b>
											<?php foreach ($ticket_history as $ticket){ ?>			            
			                               		<p style="margin: 0px; padding-left: 30px"><a href="/pages/lookup.php?trans_id=<?php echo $ticket[0];?>" style = "padding-right:0px; padding-left: 0px;"><?php echo "Ticket ".$ticket[0];?></a>: <?php echo (!isset($ticket[4]))? "$0.00 Due": $ticket[4]." owed";?></p>
			                				<?php } ?>
											<!--</a>-->
										</li>
										<li class="divider"></li>
										<li style = "margin:0 18px;">
											<i class="fas fa-ticket-alt"></i> <b>Ticket Status:</b>
											<?php foreach ($ticket_history as $ticket){ ?>			            
				                            	<p style="margin: 0px; padding-left: 30px"><a href="/pages/lookup.php?trans_id=<?php echo $ticket[0];?>" style = "padding-right:0px; padding-left: 0px;"><?php echo "Ticket ".$ticket[0];?></a>: <?php echo $ticket[3];?></p>
			                    			<?php } ?>		       
										</li>
										<li class="divider"></li>
									<?php } 
									if($staff->getRoleID() == $sv['LvlOfStaff']){ ?> <!-- If operator has role of staff then display device status -->
										<li style = "margin:0 18px;">
											<i class="fas fa-ticket-alt"></i> <b>Device Status:</b>
											<p style="margin: 0px; padding-left: 30px"> 3D Scanner Station: In Use</p>
										</li>
									<?php }
								} ?>
								<li class="divider" style="margin-bottom: 0;"></li>
								<li> 
									<button type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#settingsModal" 
									style="background-color: lightgrey; color: black; border: 1px solid #888; padding:4px">
										<i class="fas fa-cog"></i>  Settings
									</button>
								</li>
							</ul>							
						<?php } 
						elseif ($staff->getRoleID() == $sv['serviceTechnican']) { ?> <!-- Dropdown for Service member (Displays a maximum of three tickets for each category oldest  tickets and newest tickets-->
							<ul class="dropdown-menu dropdown-user" style="padding-bottom: 0; padding-top: 0; width: 250px;">
								<li style = "background-color: lightgrey; border: 1px solid #888; margin-bottom: 9px; padding: 6px 4px; color: black">Notifications</li>
									<p style = "display:inline-block; margin-bottom: 5px; margin-left:18px"><i class="fas fa-ticket-alt"></i><b> Service Ticket:</b></p>
								    <?php 
								    $result = Service_call::openSC_notifications();
								    $ticket_count  = count($result);
								    if($ticket_count == 0) { //If there are no tickets?> 
								    	<li style ="margin:0 18px;">
											<p> No recent tickets </p>
										</li>
									<?php }
									elseif($ticket_count <= 3) { //If  there are less than three tickets ?>
										<li style ="margin:0 18px;">
											<p> Oldest tickets </p>
											<?php foreach($result as $ticket) { ?>
												<p style="margin-bottom: 2px; padding-left: 30px">
													<?php echo Service_lvl::getDot($ticket[1])?><a href="/pages/sr_log.php?sc_id=<?php echo $ticket[0];?>" onclick="loadingModal()" style = "margin-left: 5px; padding-left:0; padding-right:0"><?php echo "Service Ticket ".$ticket[0];?></a>	
												</p>
											<?php } ?>
										</li>
									<?php } 
									else{ //If there are more than three service tickets ?> 
										<li style ="margin:0 18px;">
											<p> Oldest tickets </p>
											<?php 
											$ticket = $result;
											for($i = 0; $i < 3; $i++){ //Display the first 3 tickets in the array as oldest tickets ?>
												<p style="margin-bottom: 2px; padding-left: 30px">
													<?php echo Service_lvl::getDot($ticket[$i][1])?><a href="/pages/sr_log.php?sc_id=<?php echo $ticket[$i][0];?>" onclick="loadingModal()" style = "margin-left: 5px; padding-left:0; padding-right:0"><?php echo "Service Ticket ".$ticket[$i][0];?></a>	
												</p>
											<?php } ?>
										</li>
										<li class="divider" style="margin-bottom: 0;"></li>
										<li style ="margin:0 18px;">
											<p> Most recent tickets </p>
											<?php if($ticket_count <= 6){ //Display the tickets(maximum of three) starting from the end of the array as most recent tickets 
											    for($i = $ticket_count - 1; $i > 2; $i--){ ?>
													<p style="margin-bottom: 2px; padding-left: 30px;">
														<span style = "display:inline-block;min-width:15px"><?php echo Service_lvl::getDot($ticket[$i][1])?></span><a href="/pages/sr_log.php?sc_id=<?php echo $ticket[$i][0];?>" onclick="loadingModal()" style = "margin-left: 5px; padding-left:0; padding-right:0"><?php echo "Service Ticket ".$ticket[$i][0];?></a>	
													</p>
											<?php }
											}
											else{
												for($i = $ticket_count - 1; $i > $ticket_count - 4; $i--){ ?>
													<p style="margin-bottom: 2px; padding-left: 30px">
														<?php echo Service_lvl::getDot($ticket[$i][1])?><a href="/pages/sr_log.php?sc_id=<?php echo $ticket[$i][0];?>" onclick="loadingModal()" style = "margin-left: 5px; padding-left:0; padding-right:0"><?php echo "Service Ticket ".$ticket[$i][0];?></a>	
													</p>
												<?php }
											} ?>
										</li>	
									<?php } ?>
								</li>
								<li class="divider" style="margin-bottom: 0;"></li>
								<li>
									<button type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#settingsModal" 
									style="background-color: lightgrey; color: black; border: 1px solid #888; padding:4px">
										<i class="fas fa-cog"></i>  Settings
									</button>
								</li>
							</ul>
							<!-- /.dropdown-notification -->
						<?php } ?>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
							<i class="<?php echo $staff->getIcon();?> fa-2x"></i> <i class="fas fa-caret-down"></i>
						</a>
						<ul class="dropdown-menu dropdown-user">
							<li><a href="/pages/info.php" onclick="loadingModal()"><i class="fas fa-info"></i> Information</a></li>
							<li class="divider"></li>
							<li><a href="/logout.php?n=n" onclick="loadingModal()"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
						</ul>
						<!-- /.dropdown-user -->
					</li>
					<!-- /.dropdown -->
				<?php } ?>	
			</ul>
			<!-- /.navbar-top-links -->
			<div class="navbar-default sidebar" role="navigation">
				<div class="sidebar-nav navbar-collapse">
					<ul class="nav" id="side-menu">
						<li>
							<a href="/index.php"><i class="fas fa-ticket-alt"></i> FabApp</a>
						</li>
						<?php if (isset($staff) && $staff->getRoleID() >=  $sv['LvlOfStaff']) { ?>
							<li>
								<a href="/admin/error.php"><i class="fas fa-bolt"></i> Error</a>
							</li>
						<?php } 
						if(isset($staff) && $staff->getRoleID() >= $sv['LvlOfLead']) { ?>
							<li>
								<a href="#"><i class="fas fa-warehouse"></i> Inventory<span class="fas fa-angle-left"></span></a>
								<ul class="nav nav-second-level">
									<li>
										<a href="/pages/inventory.php"><i class="fas fa-box"></i> On Hand</a>
									</li>
									<li>
										<a href="/pages/inventory_processing.php"><i class="fas fa-shipping-fast"></i> Edit Inventory</a>
									</li>
									<?php if(isset($staff) && $staff->getRoleID() >= $sv['minRoleTrainer']) { ?>
									<li>
										<a href="/pages/current_inventory.php"><i class="far fa-check-square"></i> Usable Inventory</a>
									</li>
									<?php } ?>
									<li>
										<a href="/pages/sheet_goods.php"><i class="fas fa-square"></i> Sheet Goods</a>
									</li>
								</ul>
								<!-- /.nav-second-level -->
							</li>
						<?php } 
						else {
							if(isset($staff) && $staff->getRoleID() >= $sv['LvlOfStaff']) { ?>
								<li>
									<a href="#"><i class="fas fa-warehouse"></i> Inventory<span class="fas fa-angle-left"></span></a>
									<ul class="nav nav-second-level">
										<li>
											<a href="/pages/inventory.php"><i class="fas fa-box"></i> On Hand</a>
										</li>
										<li>
											<a href="/pages/sheet_goods.php"><i class="fas fa-square"></i> Sheet Goods</a>
										</li>
									</ul>
									<!-- /.nav-second-level -->
								</li>
						<!-- if role > 6 {show} -->
						<?php } }
						if (isset($staff) && $staff->getRoleID() >=  $sv['LvlOfStaff']) { ?>
							<li>
								<a href="#" id="searchLink"><i class="fas fa-search"></i> Look-Up By<span class="fas fa-angle-left"></span></a>
								<ul class="nav nav-second-level">
								<form name="searchForm" method="POST" action="" autocomplete="off"  onsubmit="return validateNum('searchForm')"> 
									<li class="sidebar-radio">
										<input type="radio" name="searchType" value="s_trans" id="s_trans" checked onchange="searchF()" onclick="searchF()"><label for="s_trans">Ticket</label>
										<input type="radio" name="searchType" value="s_operator" id="s_operator" onchange="searchF()" onclick="searchF()"><label for="s_operator">ID #</label>
									</li>
									<li class="sidebar-search">
										<div class="input-group custom-search-form">
											<input type="number" name="searchField" id="searchField" class="form-control" placeholder="Search..." name="searchField" onclick="searchF()">
											<span class="input-group-btn">
											<button class="btn btn-default" type="submit" name="searchBtn">
												<i class="fas fa-search"></i>
											</button>
											</span>
										</div>
									</li>
								</form>
								</ul>
							</li>
							<li>
								<a href="#" id="pickLink"><i class="fas fa-gift"></i> Pick Up 3D Print<span class="fas fa-angle-left"></span></a>
								<ul class="nav nav-second-level">
								<form name="pickForm" method="POST" action="" autocomplete="off" onsubmit="return validateNum('pickForm')">
									<li class="sidebar-search">
										<div class="input-group custom-search-form">
											<input type="text" name="pickField" id="pickField" class="form-control" placeholder="Enter ID #" maxlength="10" size="10">
											<span class="input-group-btn">
											<button class="btn btn-default" type="submit" name="pickBtn">
												<i class="fas fa-search"></i>
											</button>
											</span>
										</div>
									</li>
								</form>
								</ul>
							</li>
							<?php if ($sv['wait_system'] != "new") { ?>
								<li>
									<a href="/admin/now_serving.php"><i class="fas fa-list-ol"></i> Now Serving</a>
								</li>
							<?php 
							}
						}
						if (isset($staff) && ($staff->getRoleID() >=  $sv['LvlOfStaff'] || $staff->getRoleID() ==  $sv['serviceTechnican'])) { ?>
							<li>
								<a href="#"><i class="fa fa-wrench"></i> Service<span class="fa arrow"></span></a>
								<ul class="nav nav-second-level">
									<li>
										<a href="/pages/sr_history.php"><i class="fas fa-history"></i> Device History</a>
									</li>
									<li>
										<a href='/pages/open_sr.php'><i class='far fa-comment'></i> Open Service Issues</a>
									</li>
									<li>
										<a href="/pages/sr_issue.php"><i class="fas fa-fire"></i> Report Issue</a>
									</li>
								</ul>
								<!-- /.nav-second-level -->
							</li>
						<?php } ?>
							<li>
								<a href="/pages/tools.php"><i class="fas fa-toolbox"></i> Tools</a>
							</li>
						<?php
						if (isset($staff) && $staff->getRoleID() >=  $sv['LvlOfLead']) { ?>
							<li>
								<a herf="#"><i class="fas fa-book"></i> Training<span class="fas fa-angle-left"></span></a>
								<ul class="nav nav-third-level">
									<li>
										<a href="/admin/training_certificate.php"><i class="far fa-check-circle"></i> Issue Certificate</a>
									</li>
									<li>
										<a href="/admin/training_revoke.php"><i class="fas fa-search"></i> Issued Trainings</a>
									</li>
									<li>
										<a href="/admin/manage_trainings.php"><i class="fas fa-edit"></i> Manage Trainings</a>
									</li>
								</ul>
							</li>
						<?php }
						if(isset($staff) && $staff->getRoleID() >=  $sv['LvlOfStaff'] && $sv['wait_system'] == "new"){ ?>
							<li>
								<a href="/pages/wait_ticket.php"><i class="fas fa-list-ol"></i> Wait Queue Ticket</a>
							</li>
						<?php } 
						if(isset($staff) && $staff->getRoleID() >= 10) {
						?>
							<li>
								<a href="#"><i class="fas fa-sitemap"></i> Admin<span class="fas fa-angle-left"></span></a>
								<ul class="nav nav-second-level">
									<li>
										<a href="/admin/stats.php"><i class="fas fa-chart-line"></i> Data Reports</a>
									</li>
									<li>
										<a href="/admin/manage_device.php"><i class="fas fa-edit"></i> Manage Devices</a>
									</li>
									<li>
										<a href="/admin/objbox.php"><i class="fas fa-gift"></i> Objects in Storage</a>
									</li>
									<li>
										<a herf="#"><i class="fas fa-users"></i> Users<span class="fas fa-angle-left"></span></a>
										<ul class="nav nav-third-level">
											<li>
												<a href="/admin/onboarding.php"><i class="fas fa-user-plus"></i> OnBoarding</a>
											</li>
											<li>
												<a href="/admin/offboarding.php"><i class="fas fa-user-times"></i> OffBoarding</a>
											</li>
											<li>
												<a href="/admin/addrfid.php"><i class="fas fa-wifi"></i> Add RFID</a>
											</li>
										</ul>
									</li>
								</ul>
								<!-- /.nav-second-level -->
							</li>
						<li>
						<?php }
						if(isset($staff) && $staff->getRoleID() >= 11) {
						?>
							<a href="#"><i class="fas fa-user-cog"></i> Site Tools<span class="fas fa-angle-left"></span></a>
							<ul class="nav nav-second-level">
								<li>
									<a href="/admin/sv.php"><i class="fas fa-sliders-h"></i> Site Variables</a>
								</li>
								<li>
									<a href="/admin/storage_unit_creator.php"><i class="fas fa-inbox"></i> Storage Box</a>
								</li>
							</ul>
						</li>
						<?php } ?>
					</ul>
				</div>
				<!-- /.sidebar-collapse -->
			</div>
			<!-- /.navbar-static-side -->
		</nav>
