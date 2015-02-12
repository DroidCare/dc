<?php
// https://github.com/chriso/klein.php/issues/176
$base  = dirname($_SERVER['PHP_SELF']);
if (ltrim($base, '/')) $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], strlen($base));

require_once __DIR__ . '/vendor/autoload.php';

$klein = new \Klein\Klein();

$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    // Handle exceptions => flash the message and redirect to the referrer
    $klein->onError(function ($klein, $err_msg) {
        $klein->service()->flash($err_msg, 'error');
        echo json_encode($klein->service()->flashes());
        // $klein->service()->back();

    });

    $db_url         = 'localhost';
    $db_username    = 'root';
    $db_password    = '';
    $db_name        = 'droidcare';

    $app->db = new mysqli($db_url, $db_username, $db_password, $db_name);

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