<?php
/*
GET /user/[i:id]

Params:
* id

Return (JSON):
* status: 0 on success, -1 otherwise
* message: array of error messages; or object containing the data:
** email
** full_name
** address
** gender
** passport_number
** nationality
** date_of_birth

*/
$this->respond('GET', '/[i:id]', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $id = $mysqli->escape_string($request->param('id'));

    if (is_empty(trim($id)))    $service->flash("Please enter a user id.", 'error');
    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $sql_query = "SELECT `full_name`, `email`, `address`, `gender`, `passport_number`, `nationality`, `date_of_birth`, `type` FROM `user` WHERE `id` = ?";
        $stmt = $mysqli->prepare($sql_query);
        $num_rows = 0;
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $res = $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;

            if ($num_rows > 0) {
                $stmt->bind_result($full_name, $email, $address, $gender, $passport_number, $nationality, $date_of_birth, $type);
                $stmt->fetch();
                $result = array(
                    "email" => $email,
                    "full_name" => $full_name,
                    "address" => $address,
                    "gender" => $gender,
                    "passport_number" => $passport_number,
                    "nationality" => $nationality,
                    "date_of_birth" => $date_of_birth
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