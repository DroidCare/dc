<?php
/*
### Logout
```
POST /user/logout
```

#### Parameters
* `session_id`, returned at login

#### Return
* `status`: 0 on success, -1 otherwise
* `message`: array of success/error messages

*/
$this->respond('POST', '/?', function ($request, $response, $service, $app) {
    $mysqli = $app->db;

    $session_id = $mysqli->escape_string($request->param('session_id'));
    if (is_empty(trim($session_id)))
        $service->flash("Please log in for you to be able to log out.", 'error');
    else if (!isset($_SESSION['login']) || $_SESSION['login'] !== TRUE)
        $service->flash("Please log in for you to be able to log out.", 'error');
    
    $error_msg = $service->flashes('error');
    if (is_empty($error_msg)) {
        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();

        $service->flash('Successfully logged out!', 'success');
        $return['status'] = 0;
        $return['message'] = $service->flashes('success');
    } else {
        $return['status'] = -1;
        $return['message'] = $error_msg;
    }
    return json_encode($return);
});