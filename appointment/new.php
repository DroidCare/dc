<?php
/*
### Make new appointment
```
POST /appointment/new
```

#### Parameters
* `patient_id`
* `consultant_id`
* `date_time` (YYYY-MM-DD HH:mm:SS; Y-M-D H:i:s)
* `health_issue`
* `attachment` [file: png, jpg, gif], stored as `attachment_paths`: for 'follow-up' type; uploaded image inaccessible directly, must be routed via API [todo]
* `type`: 'follow-up', 'referral'
* `referrer_name`: may NULL if `type` is not 'referral'
* `referrer_clinic`: may NULL if `type` is not 'referral'
* `previous_id`: may NULL if `type` is not 'follow-up'
* `remarks`
* `session_id`: returned at login

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of success/error messages

*/
$this->respond('POST', '/?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $patient_id = $mysqli->escape_string($request->param('patient_id'));
    $consultant_id = $mysqli->escape_string($request->param('consultant_id'));
    $date_time = $mysqli->escape_string($request->param('date_time'));
    $health_issue = $mysqli->escape_string($request->param('health_issue'));
    $attachment = $request->files()['attachment'];
    $type = $mysqli->escape_string($request->param('type'));
    $referrer_name = $mysqli->escape_string($request->param('referrer_name'));
    $referrer_clinic = $mysqli->escape_string($request->param('referrer_clinic'));
    $previous_id = $mysqli->escape_string($request->param('previous_id'));
    $remarks = $mysqli->escape_string($request->param('remarks'));
    $status = 'pending';
    $session_id = $mysqli->escape_string($request->param('session_id'));
    $attachment_path = '';

    // error checking

    if (is_empty(trim($patient_id)))        $service->flash("Please enter the patient id.", 'error');
    if (is_empty(trim($consultant_id)))     $service->flash("Please enter the consultant id.", 'error');
    if (is_empty(trim($health_issue)))      $service->flash("Please enter the health issue.", 'error');
    if ($type === 'follow-up' && is_empty($attachment))
                                            $service->flash("Please enter the attachment image for follow up appointment.", 'error');
    if ($type === 'follow-up' && !is_empty($attachment)) {
        if ($attachment['error'] !== UPLOAD_ERR_OK) {
            switch ($attachment['error']) {
                case UPLOAD_ERR_NO_FILE:        $service->flash("Please enter the attachment image for follow up appointment.", 'error');
                break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:      $service->flash("Please enter smaller size attachment image for follow up appointment.", 'error');
                break;
                default:                        $service->flash("Please enter the attachment image for follow up appointment: unkown error.", 'error');
            }
        } else { // $attachment['error'] === UPLOAD_ERR_OK
            if ($attachment['size'] > 1000000) {
                $service->flash("Please enter smaller size attachment image for follow up appointment.", 'error');
            }
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(strtolower($finfo->file($attachment['tmp_name'])),
                array(
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                ), true)) {
                $service->flash("Please enter attachment image (jpg, png, or gif) for follow up appointment.", 'error');
            }
        }

    }
    
                                            
    if (is_empty(trim($type)))              $service->flash("Please enter the type of appointment.", 'error');
    if ($type === 'referral' && is_empty(trim($referrer_name)))
                                            $service->flash("Please enter the referrer name.", 'error');
    if ($type === 'referral' && is_empty(trim($referrer_clinic)))
                                            $service->flash("Please enter the referrer clinic.", 'error');
    if ($type === 'follow-up' && is_empty(trim($previous_id)))
                                            $service->flash("Please enter the previous appointment ID.", 'error');
    if (is_empty(trim($session_id)))        $service->flash("Please login before creating new appointment.", 'error');

    if (is_empty(trim($date_time)))         $service->flash("Please enter the date and time of appointment.", 'error');
    if (($dob_timestamp = strtotime($date_time)) === false)
                                            $service->flash("Please enter a valid date and time.", 'error');
    $date_time = date("Y-m-d H:i:s", $dob_timestamp);

    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        // Store the image somewhere
        $upload_file = sprintf('%s.%s', sha1_file($attachment['tmp_name']), $ext);

        // If folder does not exists, create it!
        if (!file_exists($app->upload_dir)) {
            mkdir($app->upload_dir, 0777, true);
        }

        if (!move_uploaded_file($attachment['tmp_name'], $app->upload_dir . $upload_file)) {
            // error
            $service->flash("Error: failed to move uploaded file.", 'error');
            $return['status'] = -1;
            $return['message'] = $service->flashes('error');
        } else {
            // Store entry to database
            $sql_query = "INSERT INTO `appointment`(`patient_id`, `consultant_id`, `date_time`, `health_issue`, `attachment_paths`, `type`, `referrer_name`, `referrer_clinic`, `previous_id`, `remarks`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql_query);
            if ($stmt) {
                $attachment_paths = $upload_file;

                $stmt->bind_param("iissssssiss", $patient_id, $consultant_id, $date_time, $health_issue, $attachment_paths, $type, $referrer_name, $referrer_clinic, $previous_id, $remarks, $status);
                $res = $stmt->execute();
                $stmt->close();
                if ($res) {
                    $service->flash("Appointment successfully created.", 'success');
                }
            }
            $return['status'] = 0;
            $return['message'] = $service->flashes('success');
        }

        
    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
    }
    return json_encode($return);
});
