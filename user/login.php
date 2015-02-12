<?php
/*
### Login
```
POST /user/login
```

#### Parameters
* email
* password

#### Return
* status: 0 on success, -1 otherwise
* message: array of error messages; or session_id() if success

*/
$this->respond('POST', '/?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $email = $mysqli->escape_string($request->param('email'));
    $password = $mysqli->escape_string($request->param('password'));

    if (is_empty(trim($email))) $service->flash("Please enter your e-mail address.", 'error');
    if (!filter_var($email_a, FILTER_VALIDATE_EMAIL))
                                $service->flash("Please enter a valid e-mail address.", 'error');
    if (is_empty($password))    $service->flash("Please enter your password.", 'error');
    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $password = hash('sha512',hash('whirlpool', $password));
        $sql_query = "SELECT `id` FROM `user` WHERE `email` = ? AND `password` = ?";
        $stmt = $mysqli->prepare($sql_query);
        $num_rows = 0;
        if ($stmt) {
            $stmt->bind_param("ss", $email, $password);
            $res = $stmt->execute();

            $stmt->store_result();
            $num_rows = $stmt->num_rows;

            $stmt->bind_result($user_id);
            $stmt->fetch();

            $stmt->close();
        }
        if ($num_rows === 1) {
            $_SESSION['login'] = TRUE;
            $_SESSION['user_id'] = $user_id;
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