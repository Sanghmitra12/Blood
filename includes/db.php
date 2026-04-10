<?php
//define('DB_HOST', 'localhost');
//define('DB_USER', 'root');
//define('DB_PASS', 'Sh@ilja2602');
//define('DB_NAME', 'blood');

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
//define('DB_NAME', 'blood');
define('DB_NAME', 'blood_donation_db');
define('DB_PORT', 3307);

function getDB() {
  // $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  //  $conn = new mysqli("localhost", "root", "", "blood");
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
//$conn = new mysqli("127.0.0.1", "root", "", "blood", 3307);

    if ($conn->connect_error) {
        die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

function sanitize($conn, $data) {
    return $conn->real_escape_string(trim($data));
}
?>
