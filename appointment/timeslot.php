<?php
/*
### Get consultant's available time slot
```
REQUEST appointment/timeslot/[i:user_id]/[s:date]
```

#### Parameters
* `user_id` [integer]: user id, should be of 'consultant' 
* `date` [string]: in YYYY-MM-DD format (Y-m-d)

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error messages; if success, array of date_time representing **time start** of consultant's **AVAILABLE** time slot.

*/
$this->respond('/[i:user_id]/[:date]', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $user_id = intval($mysqli->escape_string($request->param('user_id')));
    $date = $mysqli->escape_string($request->param('date'));

    // error checking
    if (is_empty(trim($user_id)))     $service->flash("Please enter the user id.", 'error');
    if (is_empty(trim($date)))        $service->flash("Please enter the date of appointment.", 'error');
    if (($date_timestamp = strtotime($date)) === false)
                                      $service->flash("Please enter a valid date.", 'error');
    $date = date("Y-m-d", $date_timestamp);

    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $sql_query = "SELECT `date_time` FROM `appointment` WHERE `consultant_id` = ?";
        $sql_query .= " AND `status` != 'cancelled'";

        $stmt = $mysqli->prepare($sql_query);
        $num_rows = 0;
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $res = $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;

            if ($num_rows > 0) {
                $result = [];
                $candidate = [];
                // assume open from 0900 till 2100; and 1 slot is 30 mins
                for ($i = 0; $i < 24; $i++) {
                    array_push($candidate, date("Y-m-d H:i:s",
                        mktime(9 + ($i /2), ($i % 2) * 30, 0, date("n", $date_timestamp), date("j", $date_timestamp), date("Y", $date_timestamp))));
                }

                $stmt->bind_result($date_time);

                while ($stmt->fetch()) {
                    if (($key = array_search($date_time, $candidate)) !== false) {
                        unset($candidate[$key]);
                    }
                }
                foreach($candidate as $value) {
                    array_push($result, $value);
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
