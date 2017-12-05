<?php
include 'dbinfo.php' ;

$users = array(
            array("admin","admin123"),
            array("kparker","imtheCEO"),
            array("eoneil","interimCEO"),
            array("commuter14","choochoo"),
            array("busrider73","roundandround"),
            array("sandrapatel","iphonex"),
            array("ignacio.john","tohellwga"),
            array("riyoy1996","Riyo4LIFE"),
            array("kellis","martapassword"),
            array("ashton.woods","2Factor"),
            array("adinozzo","V3rySpecialAgent")
            );
foreach($users as $user) {
    $hashedPassword = password_hash($user[1], PASSWORD_BCRYPT);
    $sth = $pdo->query("UPDATE User SET Password='".$hashedPassword."' WHERE Username='".$user[0]."'");
    echo "UPDATE User SET Password='".$hashedPassword."' WHERE Username='".$user[0]."'<br>";
    if (!$sth) {
        echo "Unable to complete request";
        break;
    }
}