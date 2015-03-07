<?php
/*
### Notify patient on appointment
```
POST /appointment/notify
```

#### Parameters
* `id`: (appointment_id) which appointment to be notified
* `session_id`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error/success messages

*/
$this->respond('POST', '/?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $id = intval($mysqli->escape_string($request->param('id')));
    $user_id = $_SESSION['user_id'];

    if (is_empty(trim($id)))
        $service->flash("Please enter the appointment id.", 'error');

    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $sql_query = "SELECT `patient`.`email` AS `patient_email`, `patient`.`full_name` AS `patient_name`, `consultant_id`, `patient`.`full_name` AS `consultant_name`, `date_time`, `health_issue`, `appointment`.`type`, `remarks`, `status` FROM `appointment` INNER JOIN `user` `patient` INNER JOIN `user` `consultant` WHERE `patient_id` = `patient`.`id` AND `consultant_id` = `consultant`.`id` AND `patient_id` = ? LIMIT 0,1";
        $stmt = $mysqli->prepare($sql_query);
        $num_rows = 0;
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $res = $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;

            if ($num_rows > 0) {
                $stmt->bind_result($patient_email, $patient_name, $consultant_id, $consultant_name, $date_time, $health_issue, $type, $remarks, $status);
                $stmt->fetch();

                // Send e-mail
                $recipient  = $patient_email;
                $subject = '[DroidCare] Appointment Reminder';
                $body = "
                <html>
                <head>
                  <title>[DroidCare] Appointment Reminder</title>
                </head>
                <body>
                  <p>Dear $patient_name,</p>
                  <p>This is a reminder that you have " . ($type !== "normal") ? "a " . $type : "an " . " appointment with $consultant_name at " . date("l, j F Y, H:i:s", $date_time) . ".</p>
                  <p>The health issue to be discussed is: $health_issue.</p>

                  <p>Please arrive at the clinic 5 minutes before the stated time.</p>
                  <br><br>
                  <p>Sincerely,</p>
                  <p>DroidCare team</p>
                </body>
                </html>
                ";
                $altBody = "Dear valued customer\r\n
                  This is a reminder that you have an appointment with $consultant_name at " . date("l, j F Y, H:i:s", strtotime($date_time)) . ".\r\n
                  Please arrive at the clinic 5 minutes before the stated time.\r\n\r\n
                  Sincerely,\r\n
                  DroidCare team";

                // Send e-mail via PHPMailer
                $mail = $app->mail;
                $mail->addAddress($recipient);     // Add a recipient

                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->AltBody = $altBody;

                if ($mail->send()) {
                    $service->flash('Appointment reminder e-mail sent', 'success');

                    $return['status'] = 0;
                    $return['message'] = $service->flashes('success');
                } else {
                    $service->flash("Failed to send e-mail." . $mail->ErrorInfo, 'error');
                    $return['status'] = -1;
                    $return['message'] = $service->flashes('error');
                }

            } else {
                $service->flash("Appointment not found", 'error');
                $return['status'] = -1;
                $return['message'] = $service->flashes('error');
            }
            $stmt->close();
        } else {
            $service->flash("Database error: ". $mysqli->error, 'error');
            $return['status'] = -1;
            $return['message'] = $service->flashes('error');
        }
    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
    }
    return json_encode($return);
});