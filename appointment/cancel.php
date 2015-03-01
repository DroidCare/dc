<?php
/*
### Cancel Appointment (by Patient)
```
POST appointment/cancel
```

#### Parameters
* `id` [integer]: appointment_id
* `session_id`: returned at login

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of success/error messages

*/
$this->respond('POST', '/?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $id = intval($mysqli->escape_string($request->param('id')));

    // error checking
    if (is_empty(trim($id)))            $service->flash("Please enter the appointment id.", 'error');
    
    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        // Update database entry
        $status = 'finished';
        $sql_query = "UPDATE `appointment` SET `status` = ? WHERE `id` = ?";
        $stmt = $mysqli->prepare($sql_query);
        if ($stmt) {
            $stmt->bind_param("si", $status, $id);
            $res = $stmt->execute();
            $stmt->close();
            if ($res) {
                $service->flash("Appointment cancelled.", 'success');
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
