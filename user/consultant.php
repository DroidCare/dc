<?php
/*
### Get list of consultant
```
GET /user/consultant
```

#### Parameters
none

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error messages; or array of objects containing the data:
  * `id`: user id of consultant
  * `full_name`

*/
$this->respond('GET', '/?[i:id]?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $type = 'consultant';

    $sql_query = "SELECT `id`, `full_name`, `specialization` FROM `user` WHERE `type` = ?";
    $stmt = $mysqli->prepare($sql_query);
    $num_rows = 0;
    if ($stmt) {
        $stmt->bind_param("s", $type);
        $res = $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;

        if ($num_rows > 0) {
            $stmt->bind_result($id, $full_name);

            $result = [];
            while ($stmt->fetch()) {
                array_push($result, array(
                    "id" => $id,
                    "full_name" => $full_name,
                    "specialization" => $specialization
                ));
            }

            $return['status'] = 0;
            $return['message'] = $result;

        } else {
            $service->flash("No consultant found", 'error');
            $return['status'] = -1;
            $return['message'] = $service->flashes('error');
        }
        $stmt->close();
    }
    return json_encode($return);
});
