<?php
/*
### Make new appointment
```
POST /appointment/new
```

#### Parameters
* `patient_id` [integer] (Optional; if not set, user id used is from the session, i.e. current logged in user)
* `consultant_id` [integer]
* `date_time` (YYYY-MM-DD HH:mm:SS; Y-M-D H:i:s; should be multiple of 30 minutes)
* `health_issue`
* `attachment` [base64-encoded string of the image]: for 'follow-up' type;
* `type`: 'follow-up', 'referral'
* `referrer_name`: may NULL if `type` is not 'referral'
* `referrer_clinic`: may NULL if `type` is not 'referral'
* `previous_id` [integer]: may NULL if `type` is not 'follow-up'
* `session_id`: returned at login

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of success/error messages

*/
$this->respond('POST', '/?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $patient_id = intval($mysqli->escape_string($request->param('patient_id')));
    $consultant_id = intval($mysqli->escape_string($request->param('consultant_id')));
    $date_time = $mysqli->escape_string($request->param('date_time'));
    $health_issue = $mysqli->escape_string($request->param('health_issue'));
    $attachment = $mysqli->escape_string($request->param('attachment'));
    $type = $mysqli->escape_string($request->param('type'));
    $referrer_name = $mysqli->escape_string($request->param('referrer_name'));
    $referrer_clinic = $mysqli->escape_string($request->param('referrer_clinic'));
    $previous_id = intval($mysqli->escape_string($request->param('previous_id')));
    $status = 'pending';
    $attachment_path = '';


    if (is_empty(trim($patient_id)) && isset($_SESSION['user_id'])) {
        $patient_id = $_SESSION['user_id'];
    }

    // error checking

    if (is_empty(trim($patient_id)))        $service->flash("Please enter the patient id.", 'error');
    if (is_empty(trim($consultant_id)))     $service->flash("Please enter the consultant id.", 'error');
    if (is_empty(trim($health_issue)))      $service->flash("Please enter the health issue.", 'error');
    if ($type === 'follow-up' && is_empty($attachment))
                                            $service->flash("Please enter the attachment image for follow up appointment.", 'error');
                                            
    if (is_empty(trim($type)))              $service->flash("Please enter the type of appointment.", 'error');
    if ($type === 'referral' && is_empty(trim($referrer_name)))
                                            $service->flash("Please enter the referrer name.", 'error');
    if ($type === 'referral' && is_empty(trim($referrer_clinic)))
                                            $service->flash("Please enter the referrer clinic.", 'error');
    if ($type === 'follow-up' && is_empty($previous_id))
                                            $service->flash("Please enter the previous appointment ID.", 'error');
    if (is_empty(trim($date_time)))         $service->flash("Please enter the date and time of appointment.", 'error');
    if (($dob_timestamp = strtotime($date_time)) === false)
                                            $service->flash("Please enter a valid date and time.", 'error');
    $date_time = date("Y-m-d H:i:s", $dob_timestamp);

    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        // Store entry to database
        $sql_query = "INSERT INTO `appointment`(`patient_id`, `consultant_id`, `date_time`, `health_issue`, `attachment`, `type`, `referrer_name`, `referrer_clinic`, `previous_id`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql_query);
        if ($stmt) {

            $stmt->bind_param("iissssssis", $patient_id, $consultant_id, $date_time, $health_issue, $attachment, $type, $referrer_name, $referrer_clinic, $previous_id, $status);
            $res = $stmt->execute();
            $stmt->close();
            if ($res) {
                $service->flash("Appointment successfully created.", 'success');
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
