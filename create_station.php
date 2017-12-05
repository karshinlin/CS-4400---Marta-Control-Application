<?php
include 'dbinfo.php' ;
session_start();

if (isset($_POST["stationName"])) {
    if ($_POST["EnterFare"] < 0 || $_POST["EnterFare"] > 50) {
        echo "Unable to create station. Fares must be between $0 and $50!<br>";
    } else {
        $queryString = "INSERT INTO Station(StopID, Name, EnterFare, ClosedStatus, IsTrain) VALUES ('".$_POST["StopID"]."','".$_POST["stationName"]."','".$_POST["EnterFare"]."','".!$_POST["stationOpen"]."',";
        if ($_POST["stationType"] == "bus") {
            $queryString .= "'0')";
            //echo $queryString."<br>";
            $sth = $pdo->query($queryString);
            if (!$sth) {
                echo "Unable to create new station.<br>";
            }
            if (empty($_POST["NearestIntersection"])) {
                $addBus = "INSERT INTO BusStationIntersection(StopID, Intersection) VALUES ('".$_POST["StopID"]."', NULL)";
                $pth = $pdo->query($addBus);
                if (!$sth) {
                    echo "Unable to create new bus station.<br>";
                }
            } else {
                $addBus = "INSERT INTO BusStationIntersection(StopID, Intersection) VALUES ('".$_POST["StopID"]."','".$_POST["NearestIntersection"]."')";
                $pth = $pdo->query($addBus);
                if (!$sth) {
                    echo "Unable to create new bus station.<br>";
                }
            }
        } else if ($_POST["stationType"] == "train") {
            $queryString .= "'1')";
            $sth = $pdo->query($queryString);
            //echo $queryString."<br>";
            if (!$sth) {
                echo "Unable to create new station.<br>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<head>
<title>Create New Station</title>
</head>
<body>
<form action="create_station.php" method="POST" onsubmit="return validateForm()">
    <table>
        <tr>
            <td>Station Name</td>
            <td><input id="stationName" type="text" name="stationName" required></td>
        </tr>
        <tr>
            <td>Stop ID</td>
            <td><input id="StopID" type="text" name="StopID" required></td>
        </tr>
        <tr>
            <td>Entry Fare</td>
            <td><input id="EnterFare" type="number" step="0.01" name="EnterFare" required></td>
        </tr>
        <tr>
            <td rowspan="3">Station Type</td>
            <td><input id="busStationType" type="radio" name="stationType" value="bus" required>Bus Station</td>
        </tr>
        <tr>
            <td><input id="nearestIntersection" type="text" name="NearestIntersection" placeholder="Nearest Intersection"></td>
        </tr>
        <tr>
            <td><input id="trainStationType" type="radio" name="stationType" value="train">Train Station</td>
        </tr>
        <tr>
            <td colspan="2"><input id="stationOpen" type="checkbox" name="stationOpen">Open Station. *When checked, passengers can enter at this station"</td>
        </tr>
    </table>
    <input type="submit" value="Create Station" onclick="return validateForm();">
</form>
<form action="admin_home.php">
        <input type="submit" value="Back to Admin Home">
</form>
</body>

<script>
    function validateForm() {
        if (document.getElementById('stationName') == "" ||
            document.getElementById('StopID') == "" ||
            document.getElementById('EnterFare') == "") {
            return false;
        }
        if (!document.getElementById("busStationType").checked && !document.getElementById("trainStationType").checked) {
            return false;
        }
        // if (document.getElementById("busStationType").checked && document.getElementById('nearestIntersection') == "") {
        //     return false;
        // }
        return true;
    }
</script>
</html>