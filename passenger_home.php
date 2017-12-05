<?php
include 'dbinfo.php' ;
session_start();
date_default_timezone_set('America/New_York');

$queryString = 'SELECT BreezecardNum, Tripfare, StartsName, EndsName, Value, BelongsTo, B.IsTrain FROM (SELECT A.*, Station.Name AS EndsName FROM (SELECT Trip.*, Station.Name as StartsName, Station.IsTrain FROM Trip LEFT JOIN Station ON Trip.StartsAt=Station.StopID) AS A LEFT JOIN Station ON A.EndsAt = Station.StopID) AS B NATURAL LEFT JOIN Breezecard';
$sth = $pdo->query($queryString." WHERE BelongsTo='".$_SESSION["CurrentUser"]."' AND EndsName IS NULL");
if (!sth) {
    echo "Unable to load data<br>";
} else {
    $temp = $sth->fetch();
    if ($sth->rowCount() > 0) {
        $breezecard = $temp["BreezecardNum"];
    }
}
if (isset($_POST["action"])) {
    if ($_POST["action"] == "Start Trip") {
        $breezecardNum = explode(':', $_POST["SelectedCard"])[0];
        $cardValue = explode(':', $_POST["SelectedCard"])[1];
        $startID = $_POST["StartStation"];
        $yth = $pdo->query("SELECT Name, EnterFare FROM Station WHERE StopID ='".$startID."'");
        //echo "SELECT Name, EnterFare FROM Station WHERE StopID ='".$startID."'"."<br>";
        if (!$yth) {
            echo "Unable to retrieve stations.<br>";
        }
        $row = $yth->fetch();
        $startName = $row["Name"];
        $tripFare = $row["EnterFare"];
        $date = date("Y-m-d H:i:s", time());
        if ($tripFare > $cardValue) {
            echo "Insufficient funds. Please choose a different card or reload.<br>";
        } else {
            $insertString = "INSERT INTO Trip(Tripfare, StartTime, BreezecardNum, StartsAt, EndsAt) VALUES ('".$tripFare."','".$date."','".$breezecardNum."','".$startID."',NULL)";
            //echo $insertString;
            $rth = $pdo->query($insertString);
            if (!$rth) {
                echo "Unable to add trip.<br>";
            }
            //echo $cardValue."-".$tripFare;
            $wth = $pdo->query("UPDATE Breezecard SET Value='".((float)$cardValue - (float)$tripFare)."' WHERE BreezecardNum='".$breezecardNum."'");
            //echo "UPDATE Breezecard SET Value='".((float)$cardValue - (float)$tripFare)."'"."<br>";
            if (!$wth) {
                echo "Unable to update card balance.<br>";
            }
        }
    } else if ($_POST["action"] == "End Trip") {
        $stopID = $_POST["EndStation"];
        $breezecardNum = explode(':', $_POST["SelectedCard"])[0];
        $yth = $pdo->query("UPDATE Trip SET EndsAt='".$stopID."' WHERE BreezecardNum = '".$breezecard."' AND EndsAt IS NULL");
        //echo "UPDATE Trip SET EndsAt='".$stopID."' WHERE BreezecardNum = '".$breezecard."' AND EndsAt IS NULL"."<br>";
        if (!$yth) {
            echo "Unable to end trip.<br>";
        }
    }
}
?>

<!DOCTYPE html>
<head>
    <title>Welcome to MARTA</title>
    <link rel='stylesheet' href='main.css' type='text/css' media='all'/>
</head>
<body>
<form id="mainForm" method="POST" action="passenger_home.php">
    <table>
        <tr>
            <td><b>Breeze Card</b></td>
            <td><select id="CardDropdown" form="mainForm" name="SelectedCard" onchange="updateValue()">
                <?php
                $currentUser = $_SESSION["CurrentUser"];
                $sth = $pdo->query("SELECT BreezecardNum, Value FROM Breezecard WHERE BelongsTo='".$currentUser."' AND BreezecardNum NOT IN (SELECT BreezecardNum FROM Conflict)");
                if (!$sth) {
                    echo "Unable to fetch Breeze Cards";
                }
                for ($i = 0; $i < $sth->rowCount(); $i++) {
                    $row = $sth->fetch();
                    $cardNum = $row["BreezecardNum"];
                    while (strlen($cardNum) < 16) {
                        $cardNum = "0".$cardNum;
                    }
                    echo '<option value="'.$row["BreezecardNum"].':'.$row["Value"].'">'.$cardNum.'</option>';
                }

                ?>
                </select>
            </td>
            <td><a href="passenger_card_management.php">Manage Cards</a></td>
        </tr>
        <tr>
            <td><b>Balance</b></td>
            <td><label id="balanceOfSelected"></label></td>
        </tr>
        <tr>
            <td><b>Start at</b></td>
                <?php
                $queryString = 'SELECT BreezecardNum, Tripfare, StartsName, EndsName, Value, BelongsTo, B.IsTrain FROM (SELECT A.*, Station.Name AS EndsName FROM (SELECT Trip.*, Station.Name as StartsName, Station.IsTrain FROM Trip LEFT JOIN Station ON Trip.StartsAt=Station.StopID) AS A LEFT JOIN Station ON A.EndsAt = Station.StopID) AS B NATURAL LEFT JOIN Breezecard';
                    $sth = $pdo->query($queryString." WHERE BelongsTo='".$_SESSION["CurrentUser"]."' AND EndsName IS NULL");
                    if (!sth) {
                        echo "Unable to load data<br>";
                    }
                    else if ($sth->rowCount() > 0) {
                        $row = $sth->fetch();
                        //currently in a trip
                        $inTrip = TRUE;
                        $isTrain = $row["IsTrain"];
                        echo '<td>';
                        echo '<select id="StartStationDropdown" form="mainForm" name="StartStation" disabled>';
                        echo '<option value="'.$row["StopID"].'">'.$row["StartsName"].'</option>';
                        echo '</select>';
                        echo '</td>';
                        echo '<td>Trip in Progress</td>';
                    } else {
                        $inTrip = FALSE;
                        //not in a trip
                        echo '<td>';
                        echo '<select id="StartStationDropdown" form="mainForm" name="StartStation">';
                        $pth = $pdo->query('SELECT Name, EnterFare, StopID FROM Station');
                        for ($i = 0; $i < $pth->rowCount(); $i++) {
                            $row = $pth->fetch();

                            echo '<option value="'.$row["StopID"].'">'.$row["Name"]." - $".$row["EnterFare"].'</option>';
                        }
                        echo '</select>';
                        echo '</td>';

                        echo '<td><input type="submit" name="action" value="Start Trip"></td>';
                    }
                ?>
        </tr>
        <tr>
            <td><b>Ending at</b></td>
            <?php
                if ($inTrip) {
                    $queryString = "SELECT Name, StopID FROM Station WHERE isTrain ='".$isTrain."'";
                    $rth = $pdo->query($queryString);
                    if (!$rth) {
                        echo "Unable to retrieve stations.<br>";
                    }
                    echo '<td>';
                    echo '<select id="EndStationDropdown" form="mainForm" name="EndStation">';
                    for ($i = 0; $i < $rth->rowCount(); $i++) {
                        $row = $rth->fetch();
                        echo '<option value="'.$row["StopID"].'">'.$row["Name"].'</option>';
                    }
                    echo '</select>';
                    echo '</td>';

                    echo '<td><input type="submit" name="action" value="End Trip"></td>';
                } else {
                    echo '<td>';
                    echo '<select id="EndStationDropdown" form="mainForm" name="EndStation" disabled>';
                    echo '<option></option>';
                    echo '</select>';
                    echo '</td>';
                    echo '<td>Trip not started</td>';
                }

            ?>
        </tr>
    </table>
</form>
<br><br>
<form action="trip_history.php">
    <input type="submit" value="View Trip History">
</form>
<form action="logout.php">
        <input type="submit" id="logoutFromAdmin" value="Log Out"/>
    </form>
</body>
<script>
function updateValue() {
    var e = document.getElementById("CardDropdown");
    var currBalance = e.options[e.selectedIndex].value.split(":")[1];
    document.getElementById("balanceOfSelected").innerHTML = currBalance;
}
window.onload = updateValue();
</script>
</html>
