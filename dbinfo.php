<?php

$username = 'cs4400_Group_94';
$password = 'QPodIQLM';
$host = 'academic-mysql.cc.gatech.edu';
$database = 'cs4400_Group_94';

$pdo = new PDO("mysql:host=".$host.";dbname=".$database,$username,$password);
$salt = '$1$rasmusle$';

?>