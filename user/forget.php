<?php
/*
### Forget password
```
POST /user/forget
```

#### Parameters
* `email`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error/success messages

*/
$this->respond('POST', '/?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $email = $mysqli->escape_string($request->param('email'));

    if (is_empty(trim($email))
        || !filter_var($email, FILTER_VALIDATE_EMAIL))
                                $service->flash("Please enter a valid e-mail address.", 'error');
    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $sql_query = "SELECT `id` FROM `user` WHERE `email` = ? LIMIT 0,1";
        $stmt = $mysqli->prepare($sql_query);
        $num_rows = 0;
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $res = $stmt->execute();

            $stmt->store_result();
            $num_rows = $stmt->num_rows;

            $stmt->bind_result($user_id);
            $stmt->fetch();

            $stmt->close();
        }
        if ($num_rows == 0) {
            $service->flash("User does not exist", 'error');
            $return['status'] = -1;
            $return['message'] = $service->flashes('error');
        } else {
            // Generate password token
            $token_expiry = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("n"), date("j")+1, date("Y")));
            $password_token = substr(hash('whirlpool', $token_expiry . $email . $user_id . $email), rand(0, 10), 30);
            $url = "http://dc.kenrick95.org/user/reset/$password_token"; //haven't been implemented yet
            // Store it at database
            $sql_query = "UPDATE `user` SET `password_token` = ?, `token_expiry` = ? WHERE `id` = ?";
            $stmt = $mysqli->prepare($sql_query);
            if ($stmt) {
                $stmt->bind_param("ssi", $password_token, $token_expiry, $user_id);
                $res = $stmt->execute();
                $stmt->store_result();
                $stmt->close();
            } else {
                die($mysqli->error);
            }
            

            // Send e-mail
            $recipient  = $email;
            $subject = '[DroidCare] Password Reset Request';
            $body = "
            <html>
            <head>
              <title>[DroidCare] Password Reset Request</title>
            </head>
            <body>
              <p>Dear valued customer,</p>
              <p>We have received a request for password reset. If you think you have done this request, please click the link below.</p>
              <p><a href=\"$url\">$url</a></p>
              <p>This link will be active for the next 24 hours</p>
              <p>If you have not requested a password reset, kindly ignore this e-mail.</p>
              <br><br>
              <p>Sincerely,</p>
              <p>DroidCare team</p>
            </body>
            </html>
            ";
            $altBody = "Dear valued customer\r\n
              We have received a request for password reset. If you think you have done this request, please click the link below.\r\n
              $url\r\n
              If you have not requested a password reset, kindly ignore this e-mail.\r\n\r\n
              Sincerely,\r\n
              DroidCare team";

            // Send e-mail via PHPMailer
            $mail = $app->mail;
            $mail->addAddress($recipient);     // Add a recipient

            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $altBody;

            if ($mail->send()) {
                $service->flash('Reset password e-mail sent', 'success');

                $return['status'] = 0;
                $return['message'] = $service->flashes('success');
            } else {
                $service->flash("Failed to send e-mail." . $mail->ErrorInfo, 'error');
                $return['status'] = -1;
                $return['message'] = $service->flashes('error');
            }
        }
        
        

    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
    }
    return json_encode($return);
});