<?php
class Notifications {
    private $operator;
    private $phone_num;
    private $email;
    private $last_contacted;

    public static function listCarriers(){
        global $mysqli;
        $list = "";

        if ($result = $mysqli->query("
            SELECT `provider`
            FROM `carrier`
            WHERE 1;
        ")){
            while($row = $result->fetch_assoc()){
                $list .= "$row[provider], ";
            }
            return substr($list, 0, -2);
        } else {
            return "Error Listing Providers";
        }

    }

    public static function sendNotification($q_id, $subject, $message, $markContact) {
        global $mysqli;
        $hasbeenContacted = $setLastContacted = false;
        // This function queries the carrier table and sends an email to all combinations

            //Query the phone number and email
        if ($result = $mysqli->query("
            SELECT `Op_phone` AS `Phone`, `Op_email` AS `Email`, `carrier` AS `Provider`
            FROM `wait_queue`
            WHERE `Q_id` = $q_id AND valid='Y'
        "))
        {
            $row = $result->fetch_assoc();
            $phone = $row['Phone'];
            $email = $row['Email'];
            $provider = $row['Provider'];


            if (!empty($phone)) {
                if ($result = $mysqli->query("
                    SELECT `email`
                    FROM `carrier`
                    WHERE `provider` = '$provider';
                ")) {
                    while ($row = $result->fetch_assoc()) {
                        list($a, $b) = explode('number', $row['email']);

                        $hasbeenContacted = self::SendMail("".$phone."".$b."", $subject, $message);
                        if ($markContact == 1) {
                            $setLastContacted = true;
                        }
                    }
                } else {
                    echo("Carrier query failed!");
                }
            }

            if (!empty($email)) {
                $hasbeenContacted = self::SendMail($email, $subject, $message);

                if ($markContact == 1) {
                    $setLastContacted = true;
                }
            }

            if ($setLastContacted == true) {
                // Update the database to display that the student has been contacted
                if ($mysqli->query("
                    UPDATE `wait_queue`
                    SET `last_contact` = CURRENT_TIMESTAMP
                    WHERE `Q_id` = $q_id AND valid='Y'
                ")) {
                }
            }
            return $hasbeenContacted;
        }
    }


//This fuction takes the Student ID, Subject of the meassage and actual message and sends email and sms notification to
//the associated student
//It also sets last contact to true to denote that user is already contacted and now their information can be deleted
        public static function sendLastNotification($op, $subject, $message, $markContact) {
            global $mysqli;
            $hasbeenContacted = $setLastContacted = false;
            // This function queries the carrier table and sends an email to all combinations

                //Query the phone number and email
            if ($result = $mysqli->query("
                SELECT `Op_phone` AS `Phone`, `Op_email` AS `Email`, `carrier` AS `Provider`
                FROM `wait_queue`
                WHERE `Operator` = $op
            "))
            {
              //fetch every result that matches this condotion
                $row = $result->fetch_assoc();
                $phone = $row['Phone'];
                $email = $row['Email'];
                $provider = $row['Provider'];

              //sends sms notification if the phone number is present in the datatbase
                if (!empty($phone)) {
                    if ($result = $mysqli->query("
                        SELECT `email`
                        FROM `carrier`
                        WHERE `provider` = '$provider';
                    ")) {
                        while ($row = $result->fetch_assoc()) {
                            list($a, $b) = explode('number', $row['email']);

                            $hasbeenContacted = self::SendMail("".$phone."".$b."", $subject, $message);
                            if ($markContact == 1) {
                                $setLastContacted = true;
                            }
                        }
                    } else {
                        echo("Carrier query failed!");
                    }
                }

              //sends email notification if the email address is present in trhe database
                if (!empty($email)) {
                    $hasbeenContacted = self::SendMail($email, $subject, $message);

                    if ($markContact == 1) {
                        $setLastContacted = true;
                    }
                }



                if ($setLastContacted == true) {
                    // Update the database to display that the student has been contacted
                    if ($mysqli->query("
                        UPDATE `wait_queue`
                        SET `last_contact` = CURRENT_TIMESTAMP
                        WHERE `Operator` = $op
                    ")) {
                    }
                }


                //if sucess delete the contact
                 $data = Wait_queue::deleteContactInfo($op);
                  if (is_string($data)) {
                    return $data;
                  }


                return $hasbeenContacted;
            }
          }



    public static function SendMail($to, $subject, $message){
        $headers =  'From: no-reply@fablab.uta.edu' . "\r\n".
                    'Reply-To: no-reply@fablab.uta.edu' . "\r\n".
                    'X-Mailer: PHP/' . phpversion();
        if ( mail($to, $subject, $message, $headers) ){
            return true;
        } else {
            return false;
        }
    }

    public static function setLastNotified($q_id){
        global $mysqli;
        if ($mysqli->query("
            UPDATE `wait_queue`
            SET `last_contact` = CURRENT_TIMESTAMP
            WHERE `Q_id` = $q_id AND valid='Y'
        ")) {
        }
      }
    }
?>
