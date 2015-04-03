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
        $sql_query = "SELECT `patient`.`email` AS `patient_email`, `patient`.`full_name` AS `patient_name`, `consultant_id`, `consultant`.`full_name` AS `consultant_name`, `consultant`.`email` AS `consultant_email`, `date_time`, `health_issue`, `appointment`.`type`, `remarks`, `status` FROM `appointment`, `user` `patient`, `user` `consultant` WHERE `patient_id` = `patient`.`id` AND `consultant_id` = `consultant`.`id` AND `appointment`.`id` = ? LIMIT 0,1";
        $stmt = $mysqli->prepare($sql_query);
        $num_rows = 0;
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $res = $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;

            if ($num_rows > 0) {
                $stmt->bind_result($patient_email, $patient_name, $consultant_id, $consultant_name, $consultant_email, $date_time, $health_issue, $type, $remarks, $status);
                $stmt->fetch();
                $fail = false;

                // Send e-mail
                if ($status == 'cancelled' || $status == 'rejected' || $status == 'finished') {
                    $service->flash("Cannot send email to $status appointment", 'error');
                    $return['status'] = -1;
                    $return['message'] = $service->flashes('error');
                } else {
                    if ($status == 'pending') {
                        // Remind the consultant (accept/ reject)
                        $recipient  = $consultant_email;
                        $subject = '[DroidCare] Appointment Reminder';
                        $body = "
                        <html>
                        <head>
                          <title>[DroidCare] Appointment Reminder</title>
                        </head>
                        <body>
                          <p>Dear $consultant_name,</p>
                          <p>You have a pending appointment which is due in 2 days, " . date("l, j F Y, H:i", strtotime($date_time)) . " with $patient_name.</p>
                          <p>Please respond to the appointment request as soon as possible.</p>

                          <br><br>
                          <p>Sincerely,</p>
                          <p>DroidCare team</p>
                        </body>
                        </html>
                        ";
                        $altBody = "Dear $consultant_name,\r\n"
                          ."You have a pending appointment which is due in 2 days, " . date("l, j F Y, H:i", strtotime($date_time)) . " with $patient_name.\r\n"
                          ."Please respond to the appointment request as soon as possible.\r\n\r\n"
                          ."Sincerely,\r\n"
                          ."DroidCare team";
                    } else if ($status == 'accepted') {
                        // Remind the patient
                        
                        $recipient  = $patient_email;
                        $subject = '[DroidCare] Appointment Reminder';
                        $body = "
                        <html>
                        <head>
                          <title>[DroidCare] Appointment Reminder</title>
                        </head>
                        <body>
                          <p>Dear $patient_name,</p>
                          <p>This is a reminder that you have an appointment with $consultant_name at " . date("l, j F Y, H:i", strtotime($date_time)) . ".</p>
                          <p>The health issue to be discussed is: $health_issue.</p>

                          <p>Please arrive at the clinic 5 minutes before the stated time.</p>
                          <br><br>
                          <p>Sincerely,</p>
                          <p>DroidCare team</p>
                        </body>
                        </html>
                        ";
                        $altBody = "Dear $patient_name,\r\n"
                          ."This is a reminder that you have an appointment with $consultant_name at " . date("l, j F Y, H:i", strtotime($date_time)) . ".\r\n"
                          ."The health issue to be discussed is: $health_issue.\r\n"
                          ."Please arrive at the clinic 5 minutes before the stated time.\r\n\r\n"
                          ."Sincerely,\r\n"
                          ."DroidCare team";
                        // Send e-mail via PHPMailer
                        $mail = $app->mail;
                        $mail->addAddress($recipient);     // Add a recipient

                        $mail->Subject = $subject;
                        $mail->Body    = $body;
                        $mail->AltBody = $altBody;
                        if (!$mail->send()) {
                            $fail = true;
                            $service->flash("Failed to send e-mail." . $mail->ErrorInfo, 'error');
                            $return['status'] = -1;
                            $return['message'] = $service->flashes('error');
                        } else {
                            $mail->clearAddresses();
                            
                            // Remind the consultant
                            $recipient  = $consultant_email;
                            $subject = '[DroidCare] Appointment Reminder';
                            $body = "
                            <html>
                            <head>
                              <title>[DroidCare] Appointment Reminder</title>
                            </head>
                            <body>
                              <p>Dear $consultant_name,</p>
                              <p>This is a reminder that you have an appointment with $patient_name at " . date("l, j F Y, H:i", strtotime($date_time)) . ".</p>
                              <p>The health issue to be discussed is: $health_issue.</p>

                              <p>Please arrive at the clinic 5 minutes before the stated time.</p>
                              <br><br>
                              <p>Sincerely,</p>
                              <p>DroidCare team</p>
                            </body>
                            </html>
                            ";
                            $altBody = "Dear $consultant_name,\r\n"
                              ."This is a reminder that you have an appointment with $patient_name at " . date("l, j F Y, H:i", strtotime($date_time)) . ".\r\n"
                              ."The health issue to be discussed is: $health_issue.\r\n"
                              ."Please arrive at the clinic 5 minutes before the stated time.\r\n\r\n"
                              ."Sincerely,\r\n"
                              ."DroidCare team";
                        }
                    }
                    if (!$fail) {
                        // Send e-mail via PHPMailer
                        $mail = $app->mail;
                        $mail->addAddress($recipient);     // Add a recipient

                        $mail->Subject = $subject;
                        $mail->Body    = $body;
                        $mail->AltBody = $altBody;

                        if ($mail->send()) {
                            if ($status == 'pending')
                                $service->flash("Appointment reminder e-mail sent to consultant.", 'success');
                            else
                                $service->flash("Appointment reminder e-mail sent to patient & consultant.", 'success');
                            $return['status'] = 0;
                            $return['message'] = $service->flashes('success');
                        } else {
                            $service->flash("Failed to send e-mail." . $mail->ErrorInfo, 'error');
                            $return['status'] = -1;
                            $return['message'] = $service->flashes('error');
                        }
                    }

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
