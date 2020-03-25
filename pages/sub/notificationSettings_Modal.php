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

<div id="settingsModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Notification Settings</h4>
            </div>
            <form name="form" action="" method="post">
                <?php
                    if($result = $mysqli->query("
                        SELECT *
                        FROM contact_info
                        WHERE userId = $staff->operator
                    "))
                    {
                        $row = $result->fetch_assoc();
                        $email = $row['email'];
                        $phone = $row['phone'];
                    }
                    else
                    {
                        $email = "";
                        $phone = "";
                    }
                ?>
                <div id="settingsBody" class="modal-body">
                    <div>
                        <label>Email: </label>
                        <input type="text" name="email" id="email" class="form-control" value="<?php echo $email ?>">
                    </div>
                    <div>
                        <label>Phone: </label>
                        <input type="text" name="phone" id="phone" class="form-control" value="<?php echo $phone ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="float: right;">Cancel</button>
                    <?php
                        if(array_key_exists('btnSave', $_POST)) { 
                            $email = $_POST['email'];
                            $phone = $_POST['phone'];

                            if ($mysqli->query("
                                    UPDATE contact_info
                                    SET email = '$email', phone = '$phone'
                                    WHERE userId = $staff->operator
                                ") === TRUE)
                            {
                                echo '<script>console.log("Inserting!");</script>';
                                $mysqli->query("
                                    INSERT INTO contact_info (userId, email, phone, collectedOn)
                                    VALUES ('$staff->operator', '$email', '$phone', CURRENT_DATE())
                                ");
                            }

                            header("Refresh:0");
                        }

                        if(array_key_exists('btnDrop', $_POST)) { 
                            $mysqli->query("
                                DELETE FROM contact_info
                                WHERE userId = $staff->operator
                            ");

                            header("Refresh:0");
                        }
                    ?>
                    <button type="submit" name="btnSave" class="btn btn-default" style="float: right; margin-right: 10px;">Save</button>
                    <button type="submit" name="btnDrop" class="btn btn-default" style="float: left; background-color: red;" title="Erase contact information from system">Drop</button>
                </div>
            </form>
        </div>
    </div>
</div>
