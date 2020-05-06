<?php
 include_once ($_SERVER['DOCUMENT_ROOT'].'/pages/header.php');
 $id = $_REQUEST['z'];
 $msg = "";
 Notifications::sendNotification($id, "FabApp Notification", "You have less than 14 minutes waiting time left. Please make your way to the FabLab.", 'From: FabApp Notifications' . "\r\n" .'', 0);
 echo json_encode($id);
?>
