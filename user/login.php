<?php
/*
POST /user/login

Params:
# email
# password

Return (JSON):
# status: 0 on success, -1 otherwise
# message: array of success/error messages

*/
$this->respond('POST', '/?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $email = $mysqli->escape_string($request->param('email'));
    $password = $mysqli->escape_string($request->param('password'));

    if (empty(trim($email)))    $service->flash("Please enter your e-mail address.", 'error');
    if (empty($password))       $service->flash("Please enter your password.", 'error');
    $error_msg = $service->flashes('error');

    if (empty($error_msg)) {
        $password = hash('sha512',hash('whirlpool', $password));
        $sql_query = "SELECT `email`, `password` FROM `user` WHERE `email` = ? AND `password` = ?";
        $stmt = $mysqli->prepare($sql_query);
        $num_rows = 0;
        if ($stmt) {
            $stmt->bind_param("ss", $email, $password);
            $res = $stmt->execute();

            $stmt->store_result();
            $num_rows = $res->num_rows();

            $stmt->close();
        }
        if ($num_rows === 1) {
            $service->flash("User successfully logged in.", 'success');
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