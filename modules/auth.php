<?php

    class Auth {

        public function __construct() {
            session_start();
        }

        public static function login($username, $pwd) {
            require_once("db.php");

            $db = new DB;
            $username = Utils::sanitize($username);
            $pwd = Utils::sanitize($pwd);

            $query = "select id, username from users where username='$username' and pwd=md5('$pwd') and active=1";
            $result = $db->conn->query($query);
            if($result->num_rows > 0) {
                $_SESSION["user"] = $result->fetch_assoc();
            }
        }

        public static function isLoggedIn() {
            return isset($_SESSION["user"]);
        }
    }
    
    $auth = new Auth;
?>