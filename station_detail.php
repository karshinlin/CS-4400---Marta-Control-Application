<?php
include 'dbinfo.php' ;
session_start();
?>
<html>
<title>Station Detail - <?php echo $_GET["StopID"]?></title>
<body>
<?php
if (isset($_GET["StopID"])) {
    $sth = $pdo->query("SELECT * FROM Station WHERE StopID='".$_GET["StopID"]."'");
    $row = $sth->fetch();
    echo '<h2 style="display:inline;">'.$row["Name"].'</h2><br>';
    //echo '<span style="display:inline;>'.$row["StopID"].'</span>';
}

if (isset($_POST["NewFare"])) {
    if ($_POST["NewFare"] < 0 || $_POST["NewFare"] > 50) {
        echo "Unable to update fare. Fares must be between $0 and $50!.<br>";
    } else {
        $pth = $pdo->query("UPDATE Station SET EnterFare='".$_POST["NewFare"]."' WHERE StopID='".$_POST["StopID"]."'");
        if (!$pth) {
            echo "Unable to update fare. Fares must be between $0 and $50!";
        }
    }
}
if (isset($_POST["checkSwitched"])) {
    $sth = $pdo->query("SELECT * FROM Station WHERE StopID='".$_GET["StopID"]."'");
    $row = $sth->fetch();
    $gth = $pdo->query("UPDATE Station SET ClosedStatus='".!$row["ClosedStatus"]."' WHERE StopID='".$_GET["StopID"]."'");
}
?>
<br>
<form method="POST" action="station_detail.php?StopID=<?php echo $_GET['StopID'] ?>">
    Fare &nbsp
<?php>
    $sth = $pdo->query("SELECT * FROM Station WHERE StopID='".$_GET["StopID"]."'");
    $row = $sth->fetch();
    echo '<input type="number" step="0.01" name="NewFare" placeholder="'.$row["EnterFare"].'">';
?>
    <input type="submit" value="Update Fare"/>
    <input type="hidden" value="<?php echo $_GET['StopID'] ?>" name="StopID">
</form>

<div>
<span><b>Nearest Intersection:</b></span>
<span>
<?php
    if (isset($_GET["StopID"]) || isset($_POST["StopID"])) {
        if (isset($_GET["StopID"])) {
            $stop = $_GET["StopID"];
        } else if (isset($_POST["StopID"])) {
            $stop = $_POST["StopID"];
        }
        $sth = $pdo->query("SELECT * FROM Station WHERE StopID='".$stop."'");
        $row = $sth->fetch();
        if ($row["IsTrain"]) {
            echo "Not available for train stations";
        } else {
            $pth = $pdo->query("SELECT Intersection FROM BusStationIntersection WHERE StopID='".$stop."'");
            $curr = $pth->fetch();
            echo $curr["Intersection"];
        }
    }

?>
</span>
<br>
<form method="POST" action="station_detail.php?StopID=<?php echo $_GET['StopID'] ?>">
    <input type="hidden" value="switched" name="checkSwitched">
<?php
$sth = $pdo->query("SELECT * FROM Station WHERE StopID='".$stop."'");
$row = $sth->fetch();
if ($row["ClosedStatus"]) {
    echo '<input id="StationIsOpen" name="StationCheck" value="0" onChange="this.form.submit()" type="checkbox">';
} else {
    echo '<input id="StationIsOpen" name="StationCheck" value="1" onChange="this.form.submit()" type="checkbox" checked>';
}

?>
&nbsp Open Station<br>
    &nbsp&nbsp&nbsp When checked, passengers can enter at this station.
</form>

</div>
</body>
<br>
<form action="admin_station_listing.php">
    <input type="submit" value="Back to Station Listing">
</form>
</html>