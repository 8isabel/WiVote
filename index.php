<?php
require 'config/con.php';

try{
    $query = "SELECT * FROM `login`";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    include 'views/index_view.php';
}catch(PDOException $e){
    echo $e->getMessage();
    exit;
}