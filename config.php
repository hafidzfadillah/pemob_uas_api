<?php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'db_pemob_uas');

    define('FCM_SERVER_KEY','e2b7438ea8a64af3e664709eb852c2e1bea0bdb4');
    
    function getDBConnection() {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if($conn->connect_error) {
            die("Connection Failed: ".$conn->connect_error);
        }

        return $conn;
    }
?>