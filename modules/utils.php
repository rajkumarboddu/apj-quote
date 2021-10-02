<?php

class Utils {
    public static function sanitize($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    public static function checkAuth() {
        if(!Auth::isLoggedIn()) {
            header("Location: login.php");
        }
    }

    public static function printError() {
        if(isset($_SESSION["error"])) {
            echo "<div class='text-danger mb-3'>".$_SESSION['error']."</div>";
            unset($_SESSION["error"]);
        }
    }

    public static function printSuccess() {
        if(isset($_SESSION["success"])) {
            echo "<div class='text-success mb-3'>".$_SESSION['success']."</div>";
            unset($_SESSION["success"]);
        }
    }
}

?>