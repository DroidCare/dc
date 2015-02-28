<?php
/*
### Update User Details
```
POST /user/update
```

#### Parameters
* `id` [integer]
* `password`
* `full_name`
* `address`
* `gender`
* `passport_number`
* `nationality`
* `date_of_birth`
* `session_id`, returned at login

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error/success messages

*/
$this->respond('POST', '/?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $id = intval($mysqli->escape_string($request->param('id')));
    $password = $mysqli->escape_string($request->param('password'));
    $full_name = $mysqli->escape_string($request->param('full_name'));
    $address = $mysqli->escape_string($request->param('address'));
    $gender = $mysqli->escape_string($request->param('gender'));
    $passport_number = $mysqli->escape_string($request->param('passport_number'));
    $nationality = $mysqli->escape_string($request->param('nationality'));
    $date_of_birth = $mysqli->escape_string($request->param('date_of_birth'));
    $type = 'patient'; // can be 'patient', 'admin', or 'consultant';

    // error checking
    if (is_empty(trim($id)))                $service->flash("Please enter a user id.", 'error');
    if (strlen($password) < 6)              $service->flash("Your password must be more than 6 characters.", 'error');
    else if (strlen($password) > 32)        $service->flash("Your password must be less than 32 characters.", 'error');
    if (is_empty(trim($full_name)))         $service->flash("Please enter your full name.", 'error');
    if (is_empty(trim($address)))           $service->flash("Please enter your address.", 'error');
    if (is_empty(trim($gender)))            $service->flash("Please specify your gender.", 'error');
    if (is_empty(trim($passport_number)))   $service->flash("Please enter your passport number.", 'error');
    if (is_empty(trim($nationality)))       $service->flash("Please enter your nationality.", 'error');
    if (is_empty(trim($date_of_birth)))     $service->flash("Please enter your date of birth.", 'error');
    if (($dob_timestamp = strtotime($date_of_birth)) === false)
                                            $service->flash("Please enter a valid date of birth.", 'error');
    $date_of_birth = date("Y-m-d", $dob_timestamp);

    if ($_SESSION['user_id'] !== $id && $_SESSION['user_type'] !== 'admin') {
        $service->flash("Sorry, you can't update other patient's details.", 'error');
    }


    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $password = hash('sha512', hash('whirlpool', $password));
        $sql_query = "UPDATE `user` SET `password` = ?, `full_name` = ?, `address` = ?, `gender` = ?, `passport_number` = ?, `nationality` = ?, `date_of_birth` = ?, `type` = ? WHERE `id` = ?";
        $stmt = $mysqli->prepare($sql_query);
        if ($stmt) {
            $stmt->bind_param("ssssssssi", $password, $full_name, $address, $gender, $passport_number, $nationality, $date_of_birth, $type, $id);
            $res = $stmt->execute();
            $stmt->close();
            if ($res) {
                $service->flash("User profile successfully updated.", 'success');
                $return['status'] = 0;
                $return['message'] = $service->flashes('success');
            }
        } else {
            $service->flash("User not found", 'error');
            $return['status'] = -1;
            $return['message'] = $service->flashes('error');
        }
    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
    }
    return json_encode($return);
});