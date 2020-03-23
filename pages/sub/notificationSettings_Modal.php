<?php
/**************************************************
*
*	@author Michael Teixeira on 3.22.20
*
*	-Displays the modal for changing the notification
*	 settings for the currently logged in user.
*
**************************************************/

include_once ($_SERVER['DOCUMENT_ROOT'].'/connections/db_connect8.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/connections/ldap.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/class/all_classes.php');

$staff = unserialize($_SESSION['staff']);
?>

<div id="settingsModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Notification Settings</h4>
            </div>
            <form name="form" action="" method="post">
                <div id="settingsBody" class="modal-body">
                    <div>
                        <label>Email: </label>
                        <input type="text" name="email" id="email" class="form-control">
                    </div>
                    <div>
                        <label>Phone: </label>
                        <input type="text" name="phone" id="phone" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="float: right;">Cancel</button>
                    <?php
                        if(array_key_exists('btnSave', $_POST)) { 
                            $query = 'UPDATE contact_info
                                      SET email = ' . $_POST['email'] . ', phone = ' . $_POST['phone'] .
                                     'WHERE userId = ' . $staff->operator;

                            $mysqli->query($query);
                        }

                        if(array_key_exists('btnDrop', $_POST)) { 
                            echo "Drop Clicked!!";
                        }
                    ?>
                    <button type="submit" name="btnSave" class="btn btn-default" style="float: right; margin-right: 10px;">Save</button>
                    <button type="submit" name="btnDrop" class="btn btn-default" style="float: left; background-color: red;" title="Erase contact information from system">Drop</button>
                </div>
            </form>
        </div>
    </div>
</div>
