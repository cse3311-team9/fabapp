<?php
/**************************************************
*
*	@author Michael Teixeira on 3.22.20
*
*	-Displays the modal for changing the notification
*	 settings for the currently logged in user.
*
**************************************************/

?>
<link href="/vendor/w3/toggle.css" rel="stylesheet" type="text/css">

<div id="settingsModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Notification Settings</h4>
            </div>
            <form name="form" action="" method="post">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="float: right;">Cancel</button>
                    <?php
                        if(array_key_exists('btnSave', $_POST)) { 
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
                                echo '<script>alert("Update Succesful");</script>';
                            }
                            else
                            {
                                echo '<script>alert("Update failed");</script>';
                            }

                            header("Refresh:0");
                        }

                        if(array_key_exists('btnDrop', $_POST)) { 
                            $operator = isset($staff->operator) ? $staff->operator : "";

                            $sql = $mysqli->prepare("
                                UPDATE wait_queue
                                SET Op_email = '', Op_phone = '', carrier = ''
                                WHERE Operator = ?
                            ");
                            $sql->bind_param("s", $operator);
                            $sql->execute();

                            header("Refresh:0");
                        }
                        echo "<script>console.log('$staff->operator')</script>";
                    ?>
                    <button type="submit" name="btnSave" class="btn btn-default" style="float: right; margin-right: 10px; background-color: #337ab7; color: white;">Save</button>
                    <button type="submit" name="btnDrop" class="btn btn-default" style="float: left; background-color: red;" title="Erase contact information from database">Drop</button>
                </div>
            </form>
        </div>
    </div>
</div>
