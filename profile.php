<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
include "views/profile_view.php";