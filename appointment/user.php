<?php
/*
### Get List of Appointments
```
POST /appointment/user/[i:id]
```
List down appointments:
* created by user (if user type is 'patient') OR
* assigned to user (if user type is 'consultant') OR
* of all users (if user_type is 'admin').

#### Parameters
* `id` [integer]: user id (optional; if not set, user id used is from the session, i.e. current logged in user)
* `session_id`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error messages; or array of objects containing the data:
  * `id`: appointment id
  * `patient_id`
  * `patient_name`
  * `consultant_id`
  * `consultant_name
  * `date_time` 
  * `health_issue`
  * `attachment` [base64-encoded string of the image]
  * `type` 
  * `referrer_name` 
  * `referrer_clinic` 
  * `previous_id` 
  * `remarks`
  * `status`
*/
$this->respond('POST', '/?[i:id]?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $id = intval($mysqli->escape_string($request->param('id')));
    // $session_id = $mysqli->escape_string($request->param('session_id'));
    if (is_empty(trim($id)) && isset($_SESSION['user_id'])) {
        $id = intval($_SESSION['user_id']);
    }

    // error checking
    // none
    
    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $sql_query = "SELECT `appointment`.`id`, `patient_id`, `patient`.`full_name` AS `patient_name`, `consultant_id`, `consultant`.`full_name` AS `consultant_name`, `date_time`, `health_issue`, `attachment`, `appointment`.`type`, `referrer_name`, `referrer_clinic`, `previous_id`, `remarks`, `status` FROM `appointment`, `user` `patient`, `user` `consultant` WHERE `patient_id` = `patient`.`id` AND `consultant_id` = `consultant`.`id` AND ";
        if ($_SESSION['user_type'] === 'patient') {
            $sql_query .= "`patient_id` = ?";
        } else if ($_SESSION['user_type'] === 'consultant') {
            $sql_query .= "`consultant_id` = ?";
        } else { // admin can view all
            $sql_query .= "1";
        }
        $sql_query .= " AND `status` != 'cancelled'";
        
        $stmt = $mysqli->prepare($sql_query);
        $num_rows = 0;
        if ($stmt) {
            if ($_SESSION['user_type'] !== 'admin')
                $stmt->bind_param("i", $id);
            $res = $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;

            if ($num_rows > 0) {
                $stmt->bind_result($appointment_id, $patient_id, $patient_name, $consultant_id, $consultant_name, $date_time, $health_issue, $attachment, $type, $referrer_name, $referrer_clinic, $previous_id, $remarks, $status);
                $result = [];
                while ($stmt->fetch()) {
                    array_push($result,
                        array(
                            "id" => $appointment_id,
                            "patient_id" => $patient_id,
                            "patient_name" => $patient_name,
                            "consultant_id" => $consultant_id,
                            "consultant_name" => $consultant_name,
                            "date_time" => $date_time,
                            "health_issue" => $health_issue,
                            "attachment" => $attachment,
                            "type" => $type,
                            "referrer_name" => $referrer_name,
                            "referrer_clinic" => $referrer_clinic,
                            "previous_id" => $previous_id,
                            "remarks" => $remarks,
                            "status" => $status
                        )
                    );
                    
                }
                $return['status'] = 0;
                $return['message'] = $result;

            } else {
                $service->flash("Appointment not found", 'error');
                $return['status'] = -1;
                $return['message'] = $service->flashes('error');
            }
            $stmt->close();
        }
    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
    }
    return json_encode($return);
});
