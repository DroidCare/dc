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
        // Generate temporary password
        $password = substr(crypt(date("Y-m-d H:i:s") . $email, $user_id), 0, 10);
        $hash = crypt($password, $user_id);
        $url = "http://dc.kenrick95.org/user/reset/$user_id/$hash"; //haven't been implemented yet

        // Send e-mail
        $to  = $email;

        // subject
        $subject = '[DroidCare] Password Reset Request';

        // message
        $message = "
        <html>
        <head>
          <title>[DroidCare] Password Reset Request</title>
        </head>
        <body>
          <p>Dear valued customer,</p>
          <p>We have received a request for password reset. If you think you have done this request, please click the link below.</p>
          <p><a href=\"$url\">$url</a></p>
          <p>If you have not requested a password reset, kindly ignore this e-mail.</p>
          <p>Sincerely,</p>
          <p>DroidCare team</p>
        </body>
        </html>
        ";

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Additional headers
        $headers .= 'To: ' . $email . "\r\n";
        $headers .= 'From: DroidCare <noreply@kenrick95.org>' . "\r\n";

        // Mail it
        mail($to, $subject, $message, $headers);
        
        $service->flash('Reset password e-mail sent', 'success');

        $return['status'] = 0;
        $return['message'] = $service->flashes('success');

    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
    }
    return json_encode($return);
});