<?php
require_once("modules/auth.php");

if(Auth::isLoggedIn()) {
    session_destroy();
}
header("Location: login.php");

?>