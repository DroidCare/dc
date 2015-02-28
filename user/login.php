<?php
/*
### Login
```
POST /user/login
```

#### Parameters
* `email`
* `password`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error messages; or `session_id` on success, please save this `session_id` locally as it will be used for authentication for other method.

*/
$this->respond('POST', '/?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $email = $mysqli->escape_string($request->param('email'));
    $password = $mysqli->escape_string($request->param('password'));

    if (is_empty(trim($email))) $service->flash("Please enter your e-mail address.", 'error');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                                $service->flash("Please enter a valid e-mail address.", 'error');
    if (is_empty($password))    $service->flash("Please enter your password.", 'error');
    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $password = hash('sha512',hash('whirlpool', $password));
        $sql_query = "SELECT `id`, `type` FROM `user` WHERE `email` = ? AND `password` = ?  LIMIT 0,1";
        $stmt = $mysqli->prepare($sql_query);
        $num_rows = 0;
        if ($stmt) {
            $stmt->bind_param("ss", $email, $password);
            $res = $stmt->execute();

            $stmt->store_result();
            $num_rows = $stmt->num_rows;

            $stmt->bind_result($user_id, $user_type);
            $stmt->fetch();

            $stmt->close();
        }
        if ($num_rows === 1) {
            $_SESSION['login'] = TRUE;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = $user_type;

            $service->flash(session_id(), 'success');

            $return['status'] = 0;
            $return['message'] = $service->flashes('success');

        } else {
            $service->flash("Wrong e-mail address or password.", 'error');
            $return['status'] = -1;
            $return['message'] = $service->flashes('error');
        }
    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
    }
    return json_encode($return);
});