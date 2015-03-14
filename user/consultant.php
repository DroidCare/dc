<?php
/*
### Get list of consultant
```
GET /user/consultant/[s:location]
```

#### Parameters
* `location`: (optional) country location of consultant

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of error messages; or array of objects containing the data:
  * `id`: user id of consultant
  * `full_name`

*/
$this->respond('GET', '/?[s:location]?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $type = 'consultant';
    $location = $mysqli->escape_string($request->param('location'));

    $sql_query = "SELECT `id`, `full_name`, `specialization` FROM `user` WHERE `type` = ?";
    if (!is_empty(trim($location))) {
        $sql_query .= " AND `location` = ?";
    }
    $stmt = $mysqli->prepare($sql_query);
    $num_rows = 0;
    if ($stmt) {
        if (!is_empty(trim($location))) {
            $stmt->bind_param("ss", $type, $location);
        } else {
            $stmt->bind_param("s", $type);
        }
        
        $res = $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;

        if ($num_rows > 0) {
            $stmt->bind_result($id, $full_name, $specialization);

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
