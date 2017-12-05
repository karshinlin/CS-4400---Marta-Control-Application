<?php
include 'dbinfo.php' ;
session_start();
?>

<!DOCTYPE html>
<title>Administrator</title>
<body>
    <button onclick="location.href='admin_station_listing.php'" value="Station Management">Station Management</button><br>
    <button onclick="location.href='suspended_cards.php'" value="Suspended Cards">Suspended Cards</button><br>
    <button onclick="location.href='admin_card_management.php'" value="Breeze Card Management">Breeze Card Management</button><br>
    <button onclick="location.href='passenger_flow.php'" value="Passenger Flow Report">Passenger Flow Report</button><br>
    <br>
    <form action="logout.php">
        <input type="submit" id="logoutFromAdmin" value="Log Out"/>
    </form>
</body>
</html>
