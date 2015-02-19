<?php
// https://github.com/chriso/klein.php/issues/176
$base  = dirname($_SERVER['PHP_SELF']);
if (ltrim($base, '/')) $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], strlen($base));
// http://stackoverflow.com/questions/1075534/cant-use-method-return-value-in-write-context
function is_empty($var) {
    return empty($var);
}
require_once __DIR__ . '/vendor/autoload.php';

$klein = new \Klein\Klein();

$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    // Handle exceptions => flash the message and redirect to the referrer
    $klein->onError(function ($klein, $err_msg) {
        $klein->service()->flash($err_msg, 'error');
        echo json_encode($klein->service()->flashes());
        // $klein->service()->back();

    });

    // Connect to database
    $db_host = isset($_SERVER['OPENSHIFT_MYSQL_DB_HOST']) ?  $_SERVER['OPENSHIFT_MYSQL_DB_HOST'] : 'localhost';
    $db_user = isset($_SERVER['OPENSHIFT_MYSQL_DB_USERNAME']) ?  $_SERVER['OPENSHIFT_MYSQL_DB_USERNAME'] : 'root';
    $db_pass = isset($_SERVER['OPENSHIFT_MYSQL_DB_PASSWORD']) ?  $_SERVER['OPENSHIFT_MYSQL_DB_PASSWORD'] : '';
    $db_name = isset($_SERVER['OPENSHIFT_APP_NAME']) ?  $_SERVER['OPENSHIFT_APP_NAME'] : 'droidcare';
    $db_port = isset($_SERVER['OPENSHIFT_MYSQL_DB_PORT']) ?  $_SERVER['OPENSHIFT_MYSQL_DB_PORT'] : 3306;

    $app->db = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Start session
    if (!is_empty($request->param('session_id'))) {
        // Take note on [Session Hijacking Attack](https://www.owasp.org/index.php/Session_hijacking_attack)
        session_id($request->param('session_id'));
    }
    session_start();

    // Attachment folder
    $app->upload_dir = isset($_SERVER['OPENSHIFT_DATA_DIR']) ?  $_SERVER['OPENSHIFT_DATA_DIR'].'/attachments/' : __DIR__.'/attachments/';

});
foreach(array('register', 'login', 'update', 'logout') as $controller) {
    $klein->with("/user/$controller", "user/$controller.php");
}
$klein->with("/user", "user/user.php");

foreach(array('new', 'attachment', 'status', 'user'/*, 'edit', 'delete'*/) as $controller) {
    $klein->with("/appointment/$controller", "appointment/$controller.php");
}
$klein->with("/appointment", "appointment/appointment.php");

$klein->dispatch();