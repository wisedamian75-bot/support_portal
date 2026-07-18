<?php
session_start();
require_once "includes/auth.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
redirect_by_role();
?>