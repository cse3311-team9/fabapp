<?php

//////// ALPHA TRAINING MODULE SEARCH ////////
// Provide a search field by operator to query all TMs a person has completed
// Edit button next to each TM- allow revocation, require reason, timestamp, staff_id. Restrict action to $sv['minRoleTrainer']
// Search by TMs and display all users. Show if current, date, and staff that issued the cert, etc

include_once ($_SERVER['DOCUMENT_ROOT'].'/pages/header.php');

// staff clearance
if (!$staff || $staff->getRoleID() < $sv['LvlOfStaff']){
    //Not Authorized to see this Page
    header('Location: /index.php');
    $_SESSION['error_msg'] = "Insufficient role level to access, You must be a Trainer.";
}

if (filter_input(INPUT_GET, 'operator')){
    //regex operator
    $trainee_ID = filter_input(INPUT_GET, 'operator');
   $trainings = IndividualsCertificates::get_individuals_trainings ($trainee_ID);
}

// fire off modal & timer
if($_SESSION['type'] == 'success'){
    echo "<script type='text/javascript'> window.onload = function(){success()}</script>";
}

// find trainings for individual
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['search_button'])) {
    $trainee_ID = filter_input(INPUT_POST, 'get_trainee_ID');  // regex'd in input
    header("Location:training_revoke.php?operator=$trainee_ID");
} 
// browse all
elseif($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['browse_all'])) {
    $trainings = TrainingModule::get_all_certificates();
} 
// revoke
elseif($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_revoke'])) {
    $expiration = date('Y-m-d', strtotime(filter_input(INPUT_POST, 'expiration')));
	$reason = filter_input(INPUT_POST, 'reason');
	$tme_key = filter_input(INPUT_POST, 'tme_key');
	if(IndividualsCertificates::revoke_training($expiration, $reason, $staff, $tme_key)) {
        $_SESSION['success_msg'] = 'Training Revoked';
        header("Location:training_revoke.php?operator=$trainee_ID");
    } else {
        $_SESSION['error_msg'] = "Unable to revoke training";
        header("Location:training_revoke.php?operator=$trainee_ID");
	}
} 
// restore
elseif($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['restore_training'])) {
	$tme_key = filter_input(INPUT_POST, 'restore_training');
	$staff_id = $staff->getOperator();
	if(IndividualsCertificates::restore_training($staff_id, $tme_key)) {
        $_SESSION['success_msg'] = 'Training Restored';
        header("Location:training_revoke.php?operator=$trainee_ID");
    } else {
        $_SESSION['error_msg'] = "Unable to restore training";
        header("Location:training_revoke.php?operator=$trainee_ID");
	}
}
?>


<!-- create page -->
<title><?php echo $sv['site_name'];?> Issued Trainings</title>
<div id="page-wrapper">
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-header">Issued Trainings</h1>
        </div>
        <!-- /.col-md-12 -->
    </div>

	<!-- search box -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fas fa-book fa-lg"></i> Look Up Completed Trainings
        </div>
        <div class="panel-body">
            <table class='table'> <tr>
                <!-- search individual's training -->
                <td class='col-md-11'>
                    <form name="teForm" method="POST" action="" autocomplete="off" onsubmit="return stdRegEx('get_trainee_ID', /^\d{10}$/, 'Please enter ID #2')">
                        <div class="input-group custom-search-form">
                            <input type="text" name="get_trainee_ID" id="get_trainee_ID" class="form-control" placeholder="Enter ID #" maxlength="10" size="10"
                                   value="<?php if (isset($id)) echo $id; ?>">
                            <span class="input-group-btn">
                            <button class="btn btn-default" type="submit" name="search_button">
                                <i class="fas fa-search"></i>
                            </button>
                            </span>
                        </div>
                    </form>
                </td>
                <!-- browse all -->
                <td class='col-md-1'>
                    <form method='POST'> 
                        <button class='btn btn-default' type='submit' name='browse_all'>View All</button>
                    </form>
                </td>
            </tr> </table>
            <?php if(isset($trainings)){ ?>
                <table id="teTable" class="table table-striped">
                    <thead>
                        <tr>
							<th class='col-md-1' align='center'>ID</th>
                            <th class='col-md-1' align='center'>Completed</th>
							<th class='col-md-2' align='center'>Staff Approval</th>
							<th class='col-md-3'>Training Module</th>
                  			<th class='col-md-5'>Validity</th>
                        </tr>
                    </thead>
                    <?php
                    for ($x = 0; $x < count($trainings); $x++){
                        $row = $trainings[$x];
                        echo "<tr";
                        	if($row['current'] == 'N') echo " style='background-color:#ffcccc;'";  // highlight if revoked; '>' in next line is very important
							echo ">";
							$issuer = Users::withID($row['staff_id']);
							?>
							<td>
                                <div class="btn-group">
                                   <button type="button" class="btn btn-default btn-s dropdown-toggle" data-toggle="dropdown">
                                        <?php echo "<i class='fas fa-user fa-lg' title='".date($sv['dateFormat'], strtotime($row['completed']))."'></i>"; ?>
                                    </button>
                                    <ul class="dropdown-menu pull-right" role="menu">
                                        <li style="padding-left: 5px;"> <?php echo $row['operator']; ?> </li>
                                    </ul>
                                </div>
                            </td>
                            <td style="padding-left: 15px;">  <!-- date completed -->
								<div class="btn-group">
								   <button type="button" class="btn btn-default btn-s dropdown-toggle" data-toggle="dropdown">
										<?php echo "<i class='far fa-clock fa-lg' title='".date($sv['dateFormat'], strtotime($row['completed']))."'></i>"; ?>
	                                </button>
	                                <ul class="dropdown-menu pull-right" role="menu">
										<li style="padding-left: 5px;"> <?php echo date($sv['dateFormat'], strtotime($row['completed'])); ?> </li>
	                                </ul>
	                            </div>
                            </td>
	                        <td style="padding-left: 15px;">  <!-- approved by -->
	                            <div class="btn-group">
	                                <button type="button" class="btn btn-default btn-s dropdown-toggle" data-toggle="dropdown">
										<?php echo "<i class='".$issuer->getIcon()." fa-lg' title='".$issuer->getOperator()."'></i>"; ?>
	                                </button>
	                                <ul class="dropdown-menu pull-right" role="menu">
										<li style="padding-left: 5px;"><?php echo $issuer->getOperator();?></li>
	                                </ul>
	                            </div>
                            </td>
                            <td>  <!-- training module description -->
                                <?php echo $row['title']; ?>
                                <div class="btn-group">
									<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
										<span class="fas fa-info-circle" title="Desc"></span>
									</button>
									<ul class="dropdown-menu pull-right" role="menu">
										<li style="padding-left: 5px;"><?php echo $row['tm_desc'];?></li>
									</ul>
                                </div>
                            </td>
                            <td>
                                <table> <tr> 
                                <!-- cell for revoke -->
                                <td class='col-sm-2'>
                                    <?php if($staff && $staff->getRoleID() >= $sv['minRoleTrainer']) {
                                        if($row['current'] === 'N') { ?>
                                        <form method="post">
                                            <button type='submit' value=<?php echo "'".$row['tme_key']."'"; ?> class='btn btn-success' name='restore_training' >Restore
                                            </button>
                                        </form>
                                        <?php } else { ?>
                                            <button type='button' value='Revoke' class='btn btn-danger' <?php echo "onclick='revoke_training(".$row['tme_key'].")'" ?> >Revoke
                                            </button>
                                        <?php }
                                    } elseif($row['current'] === 'N') {
                                        echo "<b>Revoked</b>";
                                    }
                                echo "</td>";
                                if($row['altered_date'] !== NULL) 
                                { ?>
                                    <td class='col-sm-2'>   
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                Time Frame
                                            </button>
                                            <ul class="dropdown-menu pull-right" role="menu">
                                                <li style="padding: 5px;"><?php echo "ALTERED DATE: ".$row['altered_date'].
                                                            "\nEXPIRATION DATE: ".$row['expiration_date'];?></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class='col-sm-2'>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                Changed By
                                            </button>
                                            <ul class="dropdown-menu pull-right" role="menu">
                                                <li style="padding: 5px;"><?php echo $row['altered_by'];?></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class='col-sm-1'>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                Reason
                                            </button>
                                            <ul class="dropdown-menu pull-right" role="menu">
                                                <li style="padding: 5px;"><?php echo $row['altered_notes'];?></li>
                                            </ul>
                                        </div>
                                    </td>
								<?php } ?>
							    </tr> </table>
							</td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>
        </div> <!-- /.panel-body -->
    </div> <!-- /.panel -->
</div>

<!-- modal to change info -->
<div id="revokeModal" class="modal">
</div>

<?php
//Standard call for dependencies
include_once ($_SERVER['DOCUMENT_ROOT'].'/pages/footer.php');
?>

<script type="text/javascript">
    $('#teTable').DataTable();


    function revoke_training(training_ID){
        if (Number.isInteger(training_ID)){
            if (window.XMLHttpRequest) {
                // code for IE7+, Firefox, Chrome, Opera, Safari
                xmlhttp = new XMLHttpRequest();
            } else {
                // code for IE6, IE5
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("revokeModal").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "sub/revoke_training.php?training_ID=" + training_ID, true);
            xmlhttp.send();
        }
        $('#revokeModal').modal('show');
    }

 </script>