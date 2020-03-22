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
<script>
    
</script>

<div id="settingsModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Notification Settings</h4>
            </div>
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
                <button type="button" class="btn btn-default" style="float: right; margin-right: 10px;">Save</button>
                <button type="button" class="btn btn-default" style="float: left; background-color: red;" title="Erase contact information from system">Drop</button>
            </div>
        </div>
    </div>
</div>