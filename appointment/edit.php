<?php
/*
### Edit Appointment
```
POST /appointment/edit
```

#### Parameters
* `id` [integer]: appointment id
* `patient_id` [integer] (Optional; if not set, user id used is from the session, i.e. current logged in user)
* `consultant_id` [integer]
* `date_time`
* `health_issue`
* `referrer_name`: may NULL if `type` is not 'referral'
* `referrer_clinic`: may NULL if `type` is not 'referral'
* `session_id`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error/success messages

*/
$this->respond('POST', '/?[i:id]?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $id = intval($mysqli->escape_string($request->param('id')));
    $patient_id = intval($mysqli->escape_string($request->param('patient_id')));
    $consultant_id = intval($mysqli->escape_string($request->param('consultant_id')));
    $date_time = $mysqli->escape_string($request->param('date_time'));
    $health_issue = $mysqli->escape_string($request->param('health_issue'));
    $referrer_name = $mysqli->escape_string($request->param('referrer_name'));
    $referrer_clinic = $mysqli->escape_string($request->param('referrer_clinic'));

    if (is_empty(trim($patient_id)) && isset($_SESSION['user_id'])) {
        $patient_id = intval($_SESSION['user_id']);
    }

    // error checking
    if (is_empty(trim($id)))                $service->flash("Please enter the appointment id.", 'error');
    if (is_empty(trim($patient_id)))        $service->flash("Please enter the patient id.", 'error');
    if (is_empty(trim($consultant_id)))     $service->flash("Please enter the consultant id.", 'error');
    if (is_empty(trim($health_issue)))      $service->flash("Please enter the health issue.", 'error');
    // if ($type === 'referral' && is_empty(trim($referrer_name)))
    //                                         $service->flash("Please enter the referrer name.", 'error');
    // if ($type === 'referral' && is_empty(trim($referrer_clinic)))
    //                                         $service->flash("Please enter the referrer clinic.", 'error');
    if (is_empty(trim($date_time)))         $service->flash("Please enter the date and time of appointment.", 'error');
    if (($dob_timestamp = strtotime($date_time)) === false)
                                            $service->flash("Please enter a valid date and time.", 'error');
    $date_time = date("Y-m-d H:i:s", $dob_timestamp);

    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $sql_query = "UPDATE `appointment` SET `patient_id` = ?, `consultant_id` = ?, `health_issue` = ?, `date_time` = ?, `referrer_name` = ?, `referrer_clinic` = ? WHERE `id` = ?";
        $stmt = $mysqli->prepare($sql_query);
        if ($stmt) {
            $stmt->bind_param("iissssi", $patient_id, $consultant_id, $health_issue, $date_time, $referrer_name, $referrer_clinic, $id);
            $res = $stmt->execute();
            if ($res && $stmt->affected_rows > 0) {
                $service->flash("Appointment successfully updated.", 'success');
                $return['status'] = 0;
                $return['message'] = $service->flashes('success');
            } else if ($stmt->affected_rows == 0) {
                $service->flash("Appointment not found", 'error');
                $return['status'] = -1;
                $return['message'] = $service->flashes('error');
            } else {
                $service->flash("Failed to update data to database: " . $stmt->error, 'error');
                $return['status'] = -1;
                $return['message'] = $service->flashes('error');
            }
            $stmt->close();
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