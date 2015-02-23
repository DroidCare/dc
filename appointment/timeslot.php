<?php
/*
### Get consultant's unavailable time slot
```
GET appointment/timeslot/[i:user_id]
```

#### Parameters
* `user_id`: user id, should be of 'consultant' type

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error messages; if success, array of date_time representing consultant's **UNAVAILABLE** time slot.

*/
$this->respond('GET', '/[i:user_id]', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $user_id = $mysqli->escape_string($request->param('user_id'));
    // error checking
    if (is_empty(trim($user_id)))     $service->flash("Please enter the user id.", 'error');    

    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $sql_query = "SELECT `date_time` FROM `appointment` WHERE `consultant_id` = ?";

        $stmt = $mysqli->prepare($sql_query);
        $num_rows = 0;
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $res = $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;

            if ($num_rows > 0) {
                $stmt->bind_result($date_time);
                $result = [];
                while ($stmt->fetch()) {
                    array_push($result,$date_time);
                }
                $return['status'] = 0;
                $return['message'] = $result;

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
