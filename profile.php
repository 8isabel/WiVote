<?php
session_start();
$isLoggedIn = isset($_SESSION['user_ID']);
include "views/profile_view.php";