<?php
require_once "db.php";

session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>
