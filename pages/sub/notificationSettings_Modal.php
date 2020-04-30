<?php
/**************************************************
*
*	@author Michael Teixeira on 3.22.20
*
*	-Displays the modal for changing the notification
*	 settings for the currently logged in user.
*
**************************************************/
$settingsIndex = 0;
?>
<link href="/vendor/w3/toggle.css" rel="stylesheet" type="text/css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script>
var activeTab = 0;
var messages = {}

    $("#globalDiv").hide();
    $("#mySettingsDiv").show();
    $("#btnDrop").show();

    function showMySettings() {
        $("#globalDiv").hide();
        $("#mySettingsDiv").show();
        $("#btnDrop").html("Drop");
        $("#btnDrop").prop("disabled", false);
        $("#btnDrop").prop("title", "Remove your information from the database");
        activeTab = 0;
        <?php $settingsIndex = 0 ?>
    }

    function showGlobalSettings() {
        $("#mySettingsDiv").hide();
        $("#globalDiv").show();
        $("#btnDrop").html("Delete");
        $("#btnDrop").prop("disabled", true);
        $("#btnDrop").prop("title", "Delete the current alert message");
        activeTab = 1;
        <?php $settingsIndex = 1 ?>
    }

    function changeMessage() {
        if ($("#messageSelector").val() == 0) {
            $("#messageName").val("");
            $("#btnDrop").prop("disabled", true);
            $("#alertMessage").val("");
        }
        else {
            $("#btnDrop").prop("disabled", false);
            $("#messageName").val($("#messageSelector option:selected").html());
            $("#alertMessage").val(messages[$("#messageSelector").val()]);
        }
    }

    function getActiveTab() {
        return activeTab;
    }
</script>

<div id="settingsModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Notification Settings</h4>
            </div>
            <form name="form" action="" method="post">
            <div id="menuTabs" class="tabs btn-group">
            <?php
                if (isset($staff)) {
                    if($staff->getRoleID() == $sv['LvlOfStaff']) {
            ?>
                <button name="mySettings" class="menuTab btn btn-default" href="#" onclick="showMySettings(); return false;">My Settings</button>
                <button name="globalSettings" class="menuTab btn btn-default" href="#" onclick="showGlobalSettings(); return false;">Global Settings</button>
            <?php
                    }
                    else
                    {
            ?>
                <button name="mySettings" class="menuTab btn btn-default" href="#" onclick="showMySettings(); return false;" style="width: 100%">My Settings</button>
            <?php
                    }
                }
            ?>
                    
            </div>
                <?php
                    $operator = isset($staff->operator) ? $staff->operator : "";
                    $sql = $mysqli->prepare("
                        SELECT Op_email, Op_phone, carrier
                        FROM wait_queue
                        WHERE Operator = ?
                    ");
                    $sql->bind_param("s", $operator);

                    if($sql->execute())
                    {
                        $result = $sql->get_result();
                        $row = $result->fetch_assoc();
                        $email = $row['Op_email'];
                        $phone = $row['Op_phone'];
                        $carrier = $row['carrier'];
                    }
                    else
                    {
                        echo '<script>alert("Failed to pull contact info from user ' + $staff->operator + '");</script>';
                        $email = "";
                        $phone = "";
                        $carrier = "";
                    }
                ?>
                <div id="settingsBody" class="modal-body">
                    <div id="mySettingsDiv">
                        <div>
                            <label>Email:</label>
                            <input type="text" name="email" id="email" class="form-control" value="<?php echo $email ?>">
                        </div>
                        <div>
                            <p></p>
                            <label>Phone:</label>
                            <input type="text" name="phone" id="phone" class="form-control" value="<?php echo $phone ?>">
                        </div>
                        <div>
                            <p></p>
                            <label>Carrier:</label>
                            <select type="text" name="carrier" id="carrier" class="form-control" value="<?php echo $carrier ?>">
                                <option value="">--- Select Cell Carrier ---</option>
                                <option value="AT&T" <?php if ($carrier == "AT&T" ) echo 'selected' ; ?>>AT&T</option>
                                <option value="Verizon" <?php if ($carrier == "Verizon" ) echo 'selected' ; ?>>Verizon</option>
                                <option value="T-Mobile" <?php if ($carrier == "T-Mobile" ) echo 'selected' ; ?>>T-Mobile</option>
                                <option value="Sprint" <?php if ($carrier == "Sprint" ) echo 'selected' ; ?>>Sprint</option>
                                <option value="Virgin Mobile" <?php if ($carrier == "Virgin Mobile" ) echo 'selected' ; ?>>Virgin Mobile</option>
                                <option value="Project Fi" <?php if ($carrier == "Project Fi" ) echo 'selected' ; ?>>Project Fi</option>
                            </select>
                        </div>
                        <div align="right">
                            <p style="height: 10px;"></p>
                            <label id="muteNotifications" title="Turn on/off email and/or text nofifications and just receive notifications through FabApp" class="switch">
                                <input type="checkbox">
                                <span class="slider round"></span>
                            </label>
                            <label class="custom-control-label" for="muteNotifications">Mute Notifications</label>
                        </div>
                    </div>
                    <div id="globalDiv" hidden>
                        <div class="globalHeader">
                            <div class="selectorWrapper">
                                <label for="messageSelector">Select Message to Edit:</label>
                                <select name="messageSelector" id="messageSelector" class="form-control" onChange="changeMessage();">
                                    <option value=0>Add New Message</option>
                                    <?php
                                        if ($result = $mysqli->query("SELECT * FROM alert_messages")) 
                                        {
                                            while ( $rows = mysqli_fetch_array ( $result ) ) 
                                            {
                                                echo "<option value='" . $rows ['Id'] . "'>" . $rows ['Name'] . "</option>";
                                                echo "<script>messages[" . $rows['Id'] . "] = '" . $rows['Message']. "'</script>";
                                            }
                                        } 
                                        else 
                                        {
                                            die('There was an error loading the device groups.');
                                        } 
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="globalDivider"></div>
                        <div class="globalBody">
                            <div id="messageNameDiv">
                                <label for="messageName">Message Name:</label>
                                <input id="messageName" name="messageName" type="text" class="form-control" />
                            </div>
                            <label for="alertMessage">Alert Message:</label>
                            <div>
                                <textarea name="alertMessage" id="alertMessage" class="bigTextBox form-control" rows="10"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="float: right;">Cancel</button>
                    <?php
                        if(array_key_exists('btnSave', $_POST)) {
                            switch ($settingsIndex) {
                                case 0: // Save My Settings
                                    $email = $_POST['email'];
                                    $phone = $_POST['phone'];
                                    $carrier = $_POST['carrier'];
                                    $operator = isset($staff->operator) ? $staff->operator : "";

                                    $sql = $mysqli->prepare("
                                        UPDATE wait_queue
                                        SET Op_email = ?, Op_phone = ?, carrier = ?
                                        WHERE Operator = ?
                                    ");
                                    $sql->bind_param("ssss", $email, $phone, $carrier, $operator);

                                    if ($sql->execute())
                                    {
                                        echo '<script>console.log("Update Succesful");</script>';
                                    }
                                    else
                                    {
                                        echo '<script>console.error("Update failed");</script>';
                                    }

                                    break;

                                case 1: // Save Gloabl Settings
                                    $message = $_POST['alertMessage'];
                                    $name = $_POST['messageName'];
                                    $Id = $_POST['messageSelector'];

                                    if ($Id == 0)
                                    {
                                        $sql = $mysqli->prepare("
                                            INSERT INTO alert_messages
                                            SET Name = ?, Message = ?
                                        ");
                                        
                                        $sql->bind_param("ss", $name, $message);
                                    }
                                    else
                                    {
                                        $sql = $mysqli->prepare("
                                            UPDATE alert_messages
                                            SET Name = ?, Message = ?
                                            WHERE Id = ?
                                        ");

                                        $sql->bind_param("ssi", $name, $message, $Id);
                                    }

                                    if ($sql->execute())
                                    {
                                        echo '<script>console.log("Update Succesful");</script>';
                                    }
                                    else
                                    {
                                        echo '<script>console.error("Update failed");</script>';
                                    }

                                    break;
                            }

                            header("Refresh:0");
                        }

                        if(array_key_exists('btnDrop', $_POST)) {
                            switch ($settingsIndex) {
                                case 0: // Drop for My Settings
                                    $operator = isset($staff->operator) ? $staff->operator : "";

                                    $sql = $mysqli->prepare("
                                        UPDATE wait_queue
                                        SET Op_email = '', Op_phone = '', carrier = ''
                                        WHERE Operator = ?
                                    ");
                                    $sql->bind_param("i", $operator);

                                    if ($sql->execute())
                                    {
                                        echo '<script>console.log("Drop Succesful");</script>';
                                    }
                                    else
                                    {
                                        echo '<script>console.error("Drop failed");</script>';
                                    }

                                    break;

                                case 1: // Delete for Gloabl Settings
                                    $Id = $_POST['messageSelector'];

                                    $sql = $mysqli->prepare("DELETE FROM alert_messages 
                                                             WHERE Id = ?");

                                    $sql->bind_param("i", $Id);

                                    if ($sql->execute())
                                    {
                                        echo '<script>console.log("Delete Succesful");</script>';
                                    }
                                    else
                                    {
                                        echo '<script>console.error("Delete failed");</script>';
                                    }

                                    break;
                            }
                            
                            header("Refresh:0");
                        }
                    ?>
                    <button type="submit" name="btnSave" class="btnSave btn btn-default">Save</button>
                    <button id="btnDrop" type="submit" name="btnDrop" class="btnDrop btn btn-default" title="Remove your information from the database">Drop</button>
                </div>
            </form>
        </div>
    </div>
</div>
