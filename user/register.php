<?php
/*
POST /user/register

Params:
# email
# password
# full_name
# address
# gender
# passport_number
# nationality
# date_of_birth

Return (JSON):
# status: 0 on success, -1 otherwise
# message: array of success/error messages


*/
$this->respond('POST', '/?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $password = $mysqli->escape_string($request->param('password'));
    $full_name = $mysqli->escape_string($request->param('full_name'));
    $email = $mysqli->escape_string($request->param('email'));
    $address = $mysqli->escape_string($request->param('address'));
    $gender = $mysqli->escape_string($request->param('gender'));
    $passport_number = $mysqli->escape_string($request->param('passport_number'));
    $nationality = $mysqli->escape_string($request->param('nationality'));
    $date_of_birth = $mysqli->escape_string($request->param('date_of_birth'));
    $type = 'patient'; // can be 'patient', 'admin', or 'consultant';

    $date_of_birth = date("Y-m-d H:i:s", strtotime($date_of_birth));

    // error checking
    if (strlen($password) < 6)          $service->flash("Your password must be more than 6 characters.", 'error');
    else if (strlen($password) > 32)    $service->flash("Your password must be less than 32 characters.", 'error');
    if (empty(trim($full_name)))        $service->flash("Please enter your full name.", 'error');
    if (empty(trim($email)))            $service->flash("Please enter your e-mail address.", 'error');
    if (empty(trim($address)))          $service->flash("Please enter your address.", 'error');
    if (empty(trim($gender)))           $service->flash("Please specify your gender.", 'error');
    if (empty(trim($passport_number)))  $service->flash("Please enter your passport number.", 'error');
    if (empty(trim($nationality)))      $service->flash("Please enter your nationality.", 'error');
    if (empty(trim($date_of_birth)))    $service->flash("Please enter your date of birth.", 'error');
    if (($dob_timestamp = strtotime($date_of_birth)) === false)
                                        $service->flash("Please enter a valid date of birth (YYYY-MM-DD).", 'error');
    $date_of_birth = date("Y-m-d H:i:s", $dob_timestamp);

    $num_rows = 0;
    $sql_query = "SELECT * FROM `user` WHERE `email` = ?";
    $stmt = $mysqli->prepare($sql_query);
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $res = $stmt->execute();

        $stmt->store_result();
        $num_rows = $res->num_rows();

        $stmt->close();
    }
    if ($num_rows === 1) {
        $service->flash("E-mail already in use, please use another e-mail.", 'error');
    }
    $error_msg = $service->flashes('error');

    if (empty($error_msg)) {
        $password = hash('sha512',hash('whirlpool', $password));
        $sql_query = "INSERT INTO user(`password`, `full_name`, `email`, `address`, `gender`, `passport_number`, `nationality`, `date_of_birth`, `type`)
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql_query);
        if ($stmt) {
            $stmt->bind_param("sssssssss", $password, $full_name, $email, $address, $gender, $passport_number, $nationality, $date_of_birth, $type);
            $res = $stmt->execute();
            $stmt->close();
            if ($res) {
                $service->flash("User successfully registered.", 'success');
            }
        }
        $return['status'] = 0;
        $return['message'] = $service->flashes('success');
    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
    }
    return json_encode($return);
});
