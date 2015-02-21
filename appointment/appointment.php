<?php
/*
### Get Details of Appointment
```
POST /appointment/[i:id]
```

#### Parameters
* `id` [integer]: appointment id
* `session_id`: returned at login

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error messages; or object containing the data:
  * `patient_id` 
  * `consultant_id` 
  * `date_time` 
  * `health_issue` 
  * `attachment_paths` 
  * `type` 
  * `referrer_name` 
  * `referrer_clinic` 
  * `previous_id` 
  * `remarks`
  * `status`
*/
$this->respond('POST', '/[i:id]', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $id = intval($mysqli->escape_string($request->param('id')));
    // $session_id = $mysqli->escape_string($request->param('session_id'));

    // error checking
    // if (is_empty(trim($session_id)))    $service->flash("Please log in to view the appointment details.", 'error');
    // else if (!isset($_SESSION['login']) || $_SESSION['login'] !== TRUE)
    //                                     $service->flash("Please log in to view the appointment  details.", 'error');
    if (is_empty(trim($id)))            $service->flash("Please enter the appointment id.", 'error');                               

    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $sql_query = "SELECT `patient_id`, `consultant_id`, `date_time`, `health_issue`, `attachment_paths`, `type`, `referrer_name`, `referrer_clinic`, `previous_id`, `remarks`, `status` FROM `appointment` WHERE `id` = ?";
        $stmt = $mysqli->prepare($sql_query);
        $num_rows = 0;
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $res = $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;

            if ($num_rows > 0) {
                $stmt->bind_result($patient_id, $consultant_id, $date_time, $health_issue, $attachment_paths, $type, $referrer_name, $referrer_clinic, $previous_id, $remarks, $status);
                $stmt->fetch();

                // "admin" can see other user's details
                // "consultant" can see appointment assigned to him/her
                // "patient" can see one's appointment
                if ($_SESSION['user_id'] !== $patient_id && $_SESSION['user_type'] === 'patient') {
                    $service->flash("Sorry, you can't view this appointment details.", 'error');
                    $return['status'] = -1;
                    $return['message'] = $service->flashes('error');

                } else if ($_SESSION['user_id'] !== $consultant_id && $_SESSION['user_type'] === 'consultant') {
                    $service->flash("Sorry, you can't view this appointment details.", 'error');
                    $return['status'] = -1;
                    $return['message'] = $service->flashes('error');

                } else {
                    $result = array(
                        "patient_id" => $patient_id,
                        "consultant_id" => $consultant_id,
                        "date_time" => $date_time,
                        "health_issue" => $health_issue,
                        "attachment_paths" => $attachment_paths,
                        "type" => $type,
                        "referrer_name" => $referrer_name,
                        "referrer_clinic" => $referrer_clinic,
                        "previous_id" => $previous_id,
                        "remarks" => $remarks,
                        "status" => $status
                    );

                    $service->flash(json_encode($result), 'success');
                    $return['status'] = 0;
                    $return['message'] = json_decode($service->flashes('success')[0]);
                }

            } else {
                $service->flash("User not found", 'error');
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