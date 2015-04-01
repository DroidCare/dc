<?php
/*
### View User Details
```
POST /user/
POST /user/[i:id]
```

#### Parameters
* `id` [integer] (optional; if not set, user id used is from the session, i.e. current logged in user)
* `session_id`

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error messages; or object containing the data:
  * `id`: user id
  * `email`
  * `full_name`
  * `address`
  * `phone_number`
  * `gender`: 'male', or 'female'
  * `passport_number`
  * `nationality`
  * `date_of_birth`
  * `notification`: 'local', 'email', 'sms', or 'all'
  * `location`: country
  * `specialization`: not empty if consultant
  * `type`: 'patient', 'admin', or 'consultant'

*/
$this->respond('POST', '/?[i:id]?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $id = intval($mysqli->escape_string($request->param('id')));
    $session_id = $mysqli->escape_string($request->param('session_id'));

    if (is_empty(trim($id)) && isset($_SESSION['user_id'])) {
        $id = $_SESSION['user_id'];
    }

    // error checking
    // "admin" and "consultant" can see other user's details
    else if ($_SESSION['user_id'] !== $id && $_SESSION['user_type'] === 'patient')
                                        $service->flash("Sorry, you can't view other patient's details.", 'error');

    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $sql_query = "SELECT `full_name`, `email`, `address`, `gender`, `passport_number`, `nationality`, `date_of_birth`, `notification`, `location`, `specialization`, `type` FROM `user` WHERE `id` = ? LIMIT 0,1";
        $stmt = $mysqli->prepare($sql_query);
        $num_rows = 0;
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $res = $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;

            if ($num_rows > 0) {
                $stmt->bind_result($full_name, $email, $address, $phone_number, $gender, $passport_number, $nationality, $date_of_birth, $notification, $location, $specialization, $type);
                $stmt->fetch();
                $result = array(
                    "id" => $id,
                    "email" => $email,
                    "full_name" => $full_name,
                    "address" => $address,
                    "phone_number" => $phone_number,
                    "gender" => $gender,
                    "passport_number" => $passport_number,
                    "nationality" => $nationality,
                    "date_of_birth" => $date_of_birth,
                    "notification" => $notification,
                    "location" => $location,
                    "specialization" => $specialization,
                    "type" => $type
                );

                $service->flash(json_encode($result), 'success');
                $return['status'] = 0;
                $return['message'] = json_decode($service->flashes('success')[0]);

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
