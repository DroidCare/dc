<?php
/*
### Get appointment's attachment
```
GET /appointment/attachment/[s:attachment_id]
```

#### Parameters
* `attachment_name` from `attachment_paths`

#### Return
* `status`: `0` on success, `-1` otherwise
* `message`: array of error messages; if success, base-64 encoded string of the image file

*/
$this->respond('GET', '/[:attachment_id]', function ($request, $response, $service, $app) {
    $mysqli = $app->db;
    $attachment_id = $mysqli->escape_string($request->param('attachment_id'));

    // error checking
    if (is_empty(trim($attachment_id)))     $service->flash("Please enter the attachment id.", 'error');    

    $error_msg = $service->flashes('error');

    if (is_empty($error_msg)) {
        if (!file_exists($app->upload_dir . $attachment_id)) {
            //error
            $service->flash("Error: File not found.", 'error');
            $return['status'] = -1;
            $return['message'] = $service->flashes('error');
        } else {
            // ok
            $return['status'] = 0;
            $return['message'] = base64_encode(file_get_contents($app->upload_dir . $attachment_id));
        }

        
    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
    }
    return json_encode($return);
});
