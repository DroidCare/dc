<?php
/*
### Get appointment's attachment
```
GET /appointment/attachment/[i:id]
```

#### Parameters
* `id`, appointment_id

#### Return
* On success, image stream; **NOTE:** there is no usual status/message return.
* On error
  * `status`: -1
  * `message`: array of error/success messages

*/
$this->respond('GET', '/[i:id]', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $id = $mysqli->escape_string($request->param('id'));

    // error checking
    if (is_empty(trim($id)))     $service->flash("Please enter the appointment id.", 'error');    

    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        $sql_query = "SELECT `attachment` FROM `appointment` WHERE `id` = ? LIMIT 0,1";
        $stmt = $mysqli->prepare($sql_query);
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($attachment);
        $stmt->fetch();

        // http://stackoverflow.com/a/6061602/917957
        $img = base64_decode($attachment);
        $f = finfo_open();

        $mime_type = finfo_buffer($f, $img, FILEINFO_MIME_TYPE);

        header('Content-Type: '. $mime_type);
        return $img;

    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
        return json_encode($return);
    }
    
});
