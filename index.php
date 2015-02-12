<?php
// https://github.com/chriso/klein.php/issues/176
# $base  = dirname($_SERVER['PHP_SELF']);
# if (ltrim($base, '/')) $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], strlen($base));

require_once __DIR__ . '/vendor/autoload.php';

$klein = new \Klein\Klein();

$db_host = isset($_SERVER['OPENSHIFT_MYSQL_DB_HOST']) ?  $_SERVER['OPENSHIFT_MYSQL_DB_HOST'] : 'localhost';
$db_user = isset($_SERVER['OPENSHIFT_MYSQL_DB_USERNAME']) ?  $_SERVER['OPENSHIFT_MYSQL_DB_USERNAME'] : 'root';
$db_pass = isset($_SERVER['OPENSHIFT_MYSQL_DB_PASSWORD']) ?  $_SERVER['OPENSHIFT_MYSQL_DB_PASSWORD'] : '';
$db_name = isset($_SERVER['OPENSHIFT_APP_NAME']) ?  $_SERVER['OPENSHIFT_APP_NAME'] : 'droidcare';
$db_port = isset($_SERVER['OPENSHIFT_MYSQL_DB_PORT']) ?  $_SERVER['OPENSHIFT_MYSQL_DB_PORT'] : 3306;
# var_dump($GLOBALS);

$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    // Handle exceptions => flash the message and redirect to the referrer
    $klein->onError(function ($klein, $err_msg) {
        $klein->service()->flash($err_msg, 'error');
        echo json_encode($klein->service()->flashes());
        // $klein->service()->back();

    });
    $app->db = new mysqli($GLOBALS['db_host'], $GLOBALS['db_user'], $GLOBALS['db_pass'], $GLOBALS['db_name']);

});
foreach(array('register', 'login') as $controller) {
    $klein->with("/user/$controller", "user/$controller.php");
}
$klein->respond('GET', '/test/[:name]', function ($request, $response, $service, $app) {
    // $sql_query = "INSERT INTO test(`name`) VALUES(?)";
    // $stmt = $app->db->prepare($sql_query);

    // $stmt->bind_param("s", $request->name);
    // $res = $stmt->execute();
    // $stmt->close();
    // var_dump($stmt);
    return 'Hello World!';
});

$klein->dispatch();