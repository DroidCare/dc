<?php
/*
### Edit Appointment
```
POST /appointment/edit
```

#### Parameters
* `id` [integer]: appointment id
* `patient_id` (Optional; if not set, user id used is from the session, i.e. current logged in user)
* `consultant_id`
* `date_time`
* `health_issue`
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

    if (is_empty(trim($patient_id)) && isset($_SESSION['user_id'])) {
        $patient_id = intval($_SESSION['user_id']);
    }

    // error checking
    if (is_empty(trim($id)))                $service->flash("Please enter the appointment id.", 'error');
    if (is_empty(trim($patient_id)))        $service->flash("Please enter the patient id.", 'error');
    if (is_empty(trim($consultant_id)))     $service->flash("Please enter the consultant id.", 'error');
    if (is_empty(trim($health_issue)))      $service->flash("Please enter the health issue.", 'error');
    if (is_empty(trim($date_time)))         $service->flash("Please enter the date and time of appointment.", 'error');
    if (($dob_timestamp = strtotime($date_time)) === false)
                                            $service->flash("Please enter a valid date and time.", 'error');
    $date_time = date("Y-m-d H:i:s", $dob_timestamp);

    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $sql_query = "UPDATE `appointment` SET `patient_id` = ?, `consultant_id` = ?, `health_issue` = ?, `date_time` = ? WHERE `id` = ?";
        $stmt = $mysqli->prepare($sql_query);
        if ($stmt) {
            $stmt->bind_param("iissi", $patient_id, $consultant_id, $health_issue, $date_time, $id);
            $res = $stmt->execute();
            $stmt->close();
            if ($res) {
                $service->flash("Appointment successfully updated.", 'success');
                $return['status'] = 0;
                $return['message'] = $service->flashes('success');
            }
        } else {
            $service->flash("Appointment not found", 'error');
            $return['status'] = -1;
            $return['message'] = $service->flashes('error');
        }
    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
    }
    return json_encode($return);
});