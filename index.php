<?php
// https://github.com/chriso/klein.php/issues/176
$base  = dirname($_SERVER['PHP_SELF']);
if (ltrim($base, '/')) $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], strlen($base));

// http://stackoverflow.com/questions/1075534/cant-use-method-return-value-in-write-context
function is_empty($var) {
    return empty($var);
}

// Include composer
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

$klein = new \Klein\Klein();

$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    $app->register('db', function() {
        // Connect to database
        global $db_host;
        global $db_user;
        global $db_pass;
        global $db_name;
        return new mysqli($db_host, $db_user, $db_pass, $db_name);
    });

    // Mail helper
    $app->register('mail', function () {
        $mail = new PHPMailer;
        global $smtp_host;
        global $smtp_auth;
        global $smtp_username;
        global $smtp_password ;
        global $smtp_secure;
        global $smtp_port;
        global $smtp_from;
        global $smtp_from_name;

        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = $smtp_auth;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = $smtp_secure; 
        $mail->Port = $smtp_port;
        $mail->From = $smtp_from;
        $mail->FromName = $smtp_from_name;
        $mail->addReplyTo($smtp_from, $smtp_from_name);

        $mail->isHTML(true); // Set email format to HTML

        return $mail;
    });

    // Check if authenticated, for certain actions
    function search_array($search, $array) {
        foreach($array as $key => $value) {
            if (!!stristr($search, $value)) {
                return true;
            }
        }
        return false;
    }
    if (
        // Authentication required for these actions:
        search_array($request->pathname(),
        array(
            '/user/update',
            '/user/logout',
            '/user',
            '/appointment/new',
            '/appointment/status',
            '/appointment/user',
            '/appointment/cancel',
            '/appointment/edit',
            '/appointment',
            )
        , TRUE) && 
        // No authentication required for these actions:
        !search_array($request->pathname(),
        array(
            '/user/login',
            '/user/register',
            '/user/forget',
            '/appointment/attachment',
            '/appointment/timeslot',
            '/user/consultant',
            )
        , TRUE)
        // Besides these actions, error 404 Not Found or 405 Method Not Allowed are returned (by klein.php)
        ) {

        function session_is_registered($x) {return isset($_SESSION[$x]);}
        // Start session; only start session when required.
        if (!is_empty($request->param('session_id'))) {
            // Take note on [Session Hijacking Attack](https://www.owasp.org/index.php/Session_hijacking_attack)
            session_id($request->param('session_id'));
        }
        session_start();


        if (null === $request->param('session_id')
            || !isset($_SESSION['login'])
            || $_SESSION['login'] !== TRUE) {
            session_regenerate_id();
            $service->flash("The action that you're trying to do requires you to log in.", 'error');
            $error_msg = $service->flashes('error');
            $return['status'] = -1;
            $return['message'] = $error_msg;
            echo json_encode($return);
            $response->send(); die();
        }
    }

        
    
    // Attachment folder
    $app->upload_dir = isset($_SERVER['OPENSHIFT_DATA_DIR']) ?  $_SERVER['OPENSHIFT_DATA_DIR'].'/attachments/' : __DIR__.'/attachments/';

});
foreach(array('register', 'login', 'update', 'logout', 'consultant', 'forget') as $controller) {
    $klein->with("/user/$controller", "user/$controller.php");
}
$klein->with("/user", "user/user.php");

foreach(array('new', 'attachment', 'status', 'user', 'cancel', 'edit', 'timeslot') as $controller) {
    $klein->with("/appointment/$controller", "appointment/$controller.php");
}
$klein->with("/appointment", "appointment/appointment.php");

$klein->dispatch();