<?php
/*
 *  CC BY-NC-AS UTA FabLab 2016-2018
 *
 *  flud.php : Fab Lab User Data
 *
 *  Michael Doran, Systems Librarian
 *  University of Texas at Arlington
 *
 *  Jonathan Le & Arun Kalahasti
 *  FabLab @ University of Texas at Arlington
 *  version: 0.91
 *
*/

// Requests/replies via JSON data exchange
// =======================================
// 1) PrintTransaction 
// 2) EndTransaction

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Cache-Control, Origin, X-Requested-With, Content-Type, Accept, Key, X-Api-Key, Authorization");

require_once($_SERVER['DOCUMENT_ROOT']."/connections/db_connect8.php");
include_once ($_SERVER['DOCUMENT_ROOT'].'/class/all_classes.php');
include_once 'gatekeeper.php';
$json_out = array();

/*
//Test Data
$input_data["type"] = "print";
$input_data["uta_id"] = "1000000016";
$input_data["device_id"] = "0021";
$input_data["m_id"] = "63";
$input_data["est_filament_used"] = "1";
$input_data["est_build_time"] = "04:01:00";
$input_data["filename"] = "Medium_Test_1.000@13_2.25@63_3@16_colorSwap.gcode";
$input_data["p_id"] = "3";

//Test End
//$input_data["type"] = "update_end_time";
//$input_data["device_id"] = 21;
*/
//Compare Header API Key with site variable's API Key
$headers = apache_request_headers();
if ($sv['api_key'] == "") {
    $json_out["api_key"] = "Not Set";
} elseif (isset($headers['authorization'])) {
    if ($sv['api_key'] != $headers['authorization'] ){
        $json_out["ERROR"] = "Unable to authenticate Device";
        ErrorExit(1);
    }
} elseif (isset($headers['Authorization'])) {
    if ($sv['api_key'] != $headers['Authorization'] ){
        $json_out["ERROR"] = "Unable to Authenticate Device";
        ErrorExit(1);
    }
} else {
    $json_out["ERROR"] = "Header Are Not Set";
    ErrorExit(1);
}


// Input posted with "Content-Type: application/json" header
$input_data = json_decode(file_get_contents('php://input'), true);
if (! ($input_data)) {
    $json_out["ERROR"] = "Unable to decode JSON message - check syntax";
    ErrorExit(1);
}

// Extract message type from incoming JSON
$type = $input_data["type"];

// Check the request type
if (strtolower($type) == "print") {
    $operator  = $input_data["uta_id"];
    $device_id = $input_data["device_id"];
    PrintTransaction ($operator, $device_id);

} elseif (strtolower($type) == "update_end_time") {
    $device_id = $input_data["device_id"];
    update_end_time( $device_id );

} elseif(strtolower($type) == "device_status") {
    $device_id = $input_data["device_id"];
    get_printer_status($device_id);
} else {
    $json_out["ERROR"] = "Unknown type: $type";
    ErrorExit(1);
}


// Output JSON and exit
header("Content-Type: application/json");
echo json_encode($json_out);
exit(0);


////////////////////////////////////////////////////////////////
//                           Functions
////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////
//
//  Check device status
//  Get's last ticket for device and cross
//   checks with service tickets to see 
//   state of device

function get_printer_status($device_id) {
    global $input_data, $json_out, $mysqli, $status;

    // select status, service_level from transactions for current device
    if($results = $mysqli->query(   "SELECT  `status`.`variable` AS status, 
                                                (SELECT `sl_id`
                                                 FROM `service_call`
                                                 WHERE `d_id` = '$device_id'
                                                 AND `solved` = 'N'
                                                 ORDER BY `sl_id` DESC
                                                 LIMIT 1) AS service_issue
                                    FROM `transactions`
                                    LEFT JOIN `status`
                                    ON `status`.`status_id` = `transactions`.`status_id`
                                    WHERE `d_id` = '$device_id'
                                    ORDER BY `transactions`.`t_start` DESC
                                    LIMIT 1;"
    )) {
        $result = $results->fetch_assoc();
        $json_out["service_issue"] = $result["service_issue"];  // service issues
        $json_out["transaction_state"] = $result["status"];  // print state
    }
}


////////////////////////////////////////////////////////////////
//
//  PrintTransaction
//  Inserts entry into the 'transactions' table

function PrintTransaction ($operator, $device_id) {
    global $json_out;
    global $mysqli;
    global $input_data;
    $json_out["authorized"] = "N";
        
    foreach (gatekeeper($operator, $device_id) as $key => $value){
        $json_out[$key] =  $value;
    }

    if ($json_out["authorized"] == "N"){
        ErrorExit(0);
    }
    $auth_status = $json_out["status_id"];

    if ($device_name_result = mysqli_query($mysqli, "
        SELECT  `device_desc`, `d_id`
        FROM  `devices` 
        WHERE  `device_id` =  '$device_id';
    ")){
        $row = $device_name_result->fetch_array();
        if (!is_null($row)){
            $d_id = $row["d_id"];
        } else {
            $json_out["device_name"] = "Not found";
        }
        $device_name_result->close();
    }
    
    if ($input_data["m_id"]){
        $m_id = $input_data["m_id"];
        $material_name_result = mysqli_query($mysqli, "
            SELECT `materials`.`m_name`, `materials`.`price`, `materials`.`unit` 
            FROM `materials` 
            WHERE `materials`.`m_id` = '$m_id'
            LIMIT 1;
        ");
        
        $row = $material_name_result->fetch_array();
        if (!is_null($row["m_name"])){
            $json_out["m_name"] = $print_json["m_name"] = $row["m_name"];
        } else {
            $json_out["m_name"] = "Not found";
        }
        $material_name_result->close();
    }

    if ($input_data["est_build_time"]){
        $est_build_time = $input_data["est_build_time"];
    }

    if ($input_data["filename"]){
        $filename = "$input_data[filename]⦂";
    }

    if ($input_data["p_id"]){
        $p_id = $input_data["p_id"];
    }
	
    //Deny if they are not the next person in line to use this device
    $msg = Wait_queue::transferFromWaitQueue($operator, $d_id);
    if (is_string($msg)){
        $json_out["ERROR"] = $msg;
        return;
    }

    if ($insert_result = $mysqli->query("
        INSERT INTO transactions
            (`operator`,`d_id`,`t_start`,`status_id`,`p_id`,`est_time`) 
        VALUES
            ('$operator','$d_id',CURRENT_TIMESTAMP,'$auth_status','$p_id','$est_build_time');
    ")){
        $trans_id = $json_out["trans_id"] = $mysqli->insert_id;
        $print_json["trans_id"] = $trans_id;
        
        if ($stmt = $mysqli->prepare("
            INSERT INTO mats_used
                (`trans_id`,`m_id`, `quantity`, `status_id`, `mu_notes`, `mu_date`) 
            VALUES
                (?, ?, ?, ?, ?, CURRENT_TIMESTAMP);
        ")){
            $bind_param = $stmt->bind_param("iidis", $trans_id, $m_id, $input_data["est_filament_used"], $auth_status, $filename);
            $stmt->execute();
            $stmt->close();
        } else {
            $json_out["ERROR"] = $mysqli->error;
            $json_out["authorized"] = "N";
            return;
        }
    } else {
        $json_out["ERROR"] = $mysqli->error;
        $json_out["authorized"] = "N";
        return;
    }
	
    $msg = Transactions::printTicket($trans_id);
    if (is_string($msg)){
        $json_out["ERROR"] = $msg;
    }
}


////////////////////////////////////////////////////////////////
//
//  update_end_time
//  updates the database with a given device ID. Will not close the ticket, only updates the time and status

function update_end_time( $dev_id ){
    global $json_out;
    global $mysqli;
    
	// Check for deviceID value
    if (! (preg_match("/^\d*$/", $dev_id))) {
        $json_out["ERROR"] = "Invalid transaction number";
        ErrorExit(1);
    }
    
    if ($result = $mysqli->query("
            SELECT *
            FROM `transactions`
            WHERE `d_id` = '$dev_id' AND `t_end` is NULL
    ")){
        $row = $result->fetch_assoc();
        $ticket = new Transactions($row['trans_id']);
    }

    // $ticket->t_end = date("Y-m-d H:i:s");  //UPDATE
	
    if ($ticket->end_octopuppet()){
        $json_out["success"] = "Update Successful for ".$ticket->getTrans_id();
    } else {
        $json_out["ERROR"] = "Check function End Octopuppet";
    }
}


////////////////////////////////////////////////////////////////
//
//  ErrorExit
//  Sends error message and quits 

function ErrorExit ($exit_status) {
    global $mysqli;
    global $json_out;
    
    header("Content-Type: application/json");
    $mysqli->close();
    echo json_encode($json_out);
    exit($exit_status);
}
?>