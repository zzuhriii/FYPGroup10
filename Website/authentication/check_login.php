<?php
session_start();

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        die("You must be logged in to access this page.");
    }
}


?>
