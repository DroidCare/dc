<?php
/*
### Reset password
```
GET /user/reset/[s:password_token]
```
Users are expected to access this URL from e-mail sent by `/user/forget`

#### Parameters
* `password_token`: Generated from `/user/forget`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error/success messages

*/
$this->respond('GET', '/[s:password_token]', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $password_token = $mysqli->escape_string($request->param('password_token'));

    if (is_empty(trim($password_token))) {
        $service->flash("Reset password URL invalid", 'error');
    }

    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $sql_query = "SELECT `id`, `token_expiry` FROM `user` WHERE `password_token` = ? LIMIT 0,1";
        $stmt = $mysqli->prepare($sql_query);
        $num_rows = 0;
        if ($stmt) {
            $stmt->bind_param("s", $password_token);
            $res = $stmt->execute();

            $stmt->store_result();
            $num_rows = $stmt->num_rows;

            $stmt->bind_result($user_id, $token_expiry);
            $stmt->fetch();

            $stmt->close();

            if (time() <= strtotime($token_expiry)) {
                // update the password
                $password_plain = substr(hash('whirlpool', $token_expiry . $password_token), rand(0, 20), 10);
                $password = hash('sha512', hash('whirlpool', $password_plain));
                $sql_query = "UPDATE `user` SET `password` = ?, `token_expiry` = ?, `password_token` = ? WHERE `id` = ?";
                $stmt = $mysqli->prepare($sql_query);
                if ($stmt) {
                    // Invalidate the token
                    $token_expiry = NULL;
                    $password_token = NULL;

                    $stmt->bind_param("sssi", $password, $token_expiry, $password_token, $user_id);
                    $res = $stmt->execute();
                    $stmt->store_result();

                    
                    if ($res && $stmt->affected_rows > 0) {
                        $service->flash('Your password has been changed to: ' . $password_plain, 'success');
                        $return['status'] = 0;
                        $return['message'] = $service->flashes('success');
                    } else {
                        $service->flash('Database error: '. $mysqli->error, 'error');
                        $return['status'] = 0;
                        $return['message'] = $service->flashes('error');
                    }
                    $stmt->close();
                } else {
                    $service->flash('SQL statement error', 'error');
                    $return['status'] = 0;
                    $return['message'] = $service->flashes('error');
                }

            } else {
                // token expired
                $service->flash('Token expired, please request reset password again', 'error');

                $return['status'] = -1;
                $return['message'] = $service->flashes('error');
            }
        }

    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
    }
    $service->render('user/reset.phtml', array('return' => $return));

    //return json_encode($return);
});