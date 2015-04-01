<?php
/*
### Update Appointment Status
```
POST appointment/status
```

#### Parameters
* `id` [integer]: appointment_id
* `status`: new status ('pending', 'accepted', 'rejected', 'finished')
* `remarks`
* `session_id`: returned at login

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of success/error messages

*/
$this->respond('POST', '/?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $id = intval($mysqli->escape_string($request->param('id')));
    $status = $mysqli->escape_string($request->param('status'));
    $remarks = $mysqli->escape_string($request->param('remarks'));

    // error checking
    if (is_empty(trim($id)))            $service->flash("Please enter the appointment id.", 'error');
    if (is_empty(trim($status)))        $service->flash("Please enter the appointment status.", 'error');
    
    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        // Update database entry
        $sql_query = "UPDATE `appointment` SET `status` = ?, `remarks` = ? WHERE `id` = ?";
        $stmt = $mysqli->prepare($sql_query);
        if ($stmt) {
            $stmt->bind_param("ssi", $status, $remarks, $id);
            $res = $stmt->execute();
            if ($res && $stmt->affected_rows > 0) {
                $stmt->close();
                // get patient's details
                $sql_query = "SELECT `patient`.`email` AS `patient_email`, `patient`.`full_name` AS `patient_name`, `consultant_id`, `consultant`.`full_name` AS `consultant_name`, `date_time`, `remarks`, `status` FROM `appointment` INNER JOIN `user` `patient` INNER JOIN `user` `consultant` WHERE `patient_id` = `patient`.`id` AND `consultant_id` = `consultant`.`id` AND `appointment`.`id` = ? LIMIT 0,1";
                $stmt = $mysqli->prepare($sql_query);
                $stmt->bind_param("i", $id);
                $res = $stmt->execute();
                $stmt->bind_result($patient_email, $patient_name, $consultant_id, $consultant_name, $date_time, $remarks, $status);
                $stmt->fetch();
                $stmt->close();

                if ($_SESSION['user_type'] !== 'patient') {
                    // Send e-mail to patient
                    $remarks_html = is_empty($remarks) ? "." : " with the following remarks: </p><p>" . $remarks;
                    $remarks_plain = is_empty($remarks) ? "." : " with the following remarks: \r\n" . $remarks;

                    $recipient  = $patient_email;
                    $subject = '[DroidCare] Appointment Status Updated';
                    $body = "
                    <html>
                    <head>
                      <title>[DroidCare] Appointment Status Updated</title>
                    </head>
                    <body>
                      <p>Dear $patient_name,</p>
                      <p>This is a notice that your appointment with $consultant_name at " . date("l, j F Y, H:i", strtotime($date_time)) . " is $status"."$remarks_html</p>
                      <br><br>
                      <p>Sincerely,</p>
                      <p>DroidCare team</p>
                    </body>
                    </html>
                    ";
                    $altBody = "Dear $patient_name,\r\n"
                      ."This is a notice that your appointment with $consultant_name at " . date("l, j F Y, H:i", strtotime($date_time)) . " is $status"."$remarks_plain.\r\n"
                      ."Sincerely,\r\n"
                      ."DroidCare team";

                    // Send e-mail via PHPMailer
                    $mail = $app->mail;
                    $mail->addAddress($recipient);     // Add a recipient

                    $mail->Subject = $subject;
                    $mail->Body    = $body;
                    $mail->AltBody = $altBody;

                    if ($mail->send()) {
                        $service->flash("Appointment status successfully updated and notification email sent.", 'success');

                        $return['status'] = 0;
                        $return['message'] = $service->flashes('success');
                    } else {
                        $service->flash("Appointment status successfully updated but notification email failed to sent. " . $mail->ErrorInfo, 'error');
                        $return['status'] = -1;
                        $return['message'] = $service->flashes('error');
                    }
                } else { // if user_type is patient
                    $service->flash("Appointment status successfully updated", 'success');
                    $return['status'] = 0;
                    $return['message'] = $service->flashes('success');
                }
            } else if ($stmt->affected_rows == 0) {
                $service->flash("Appointment not found", 'error');
                $return['status'] = -1;
                $return['message'] = $service->flashes('error');
                $stmt->close(); 
            } else {
                $service->flash("Failed to update data to database: " . $stmt->error, 'error');
                $return['status'] = -1;
                $return['message'] = $service->flashes('error');
                $stmt->close(); 
            }
            

        } else {
            $service->flash("SQL statement error", 'error');
            $return['status'] = -1;
            $return['message'] = $service->flashes('error');
        }
    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
    }
    return json_encode($return);
});
