<?php
include 'dbinfo.php' ;
session_start();
ini_set('display_errors', 1);

?>

<!DOCTYPE html>
<head>
<title>Trip History</title>
</head>
<body>
    <form method="POST" action="trip_history.php">
        <table>
            <tr>
                <td>Start Time</td>
                <td><input type="text" name="StartTime"></td>
                <td rowspan="2"><button onclick="reset()">Reset</button><input type="submit" value="Update"></td>
            </tr>
            <tr>
                <td>End Time</td>
                <td><input type="text" name="EndTime"></td>
            </tr>
        </table>
    </form>
    <table id="trips" border="1px:solid-black" class="flexy">
    <thead>
        <tr>
            <th style="cursor:pointer" onclick="sortTable(0)">Time</th>
            <th style="cursor:pointer" onclick="sortTable(1)">Source</th>
            <th style="cursor:pointer" onclick="sortTable(2)">Destination</th>
            <th style="cursor:pointer" onclick="sortTable(3)">Fare Paid</th>
            <th style="cursor:pointer" onclick="sortTable(4)">Card #</th>
        </tr>
    </thead>
    <tbody>
<?php

$queryString = "SELECT T.StartTime as Time, T.StartsAt as Source, T.EndsAt as Destination, T.Tripfare as 'Fare Paid', T.BreezecardNum FROM (SELECT StartTime, StartsAt, Name as EndsAt, Tripfare, BreezecardNum FROM (SELECT StartTime, Name as StartsAt, EndsAt, Tripfare, BreezecardNum FROM Trip LEFT JOIN Station ON StartsAt = Station.StopID) as A LEFT JOIN Station ON A.EndsAt = Station.StopID) as T NATURAL LEFT JOIN Breezecard as B WHERE B.BelongsTo = '".$_SESSION["CurrentUser"]."'";
if (isset($_POST["StartTime"]) && !empty($_POST["StartTime"])) {
    $filterStart = date("Y-m-d H:i:s", strtotime($_POST["StartTime"]));
    $queryString .= " AND T.StartTime >= '".$filterStart."'";
}
if (isset($_POST["EndTime"]) && !empty($_POST["EndTime"])) {
    $filterEnd = date("Y-m-d H:i:s", strtotime($_POST["EndTime"]));
    $queryString .= " AND T.StartTime <= '".$filterEnd."'";
}
//echo $queryString."<br>";
$sth = $pdo->query($queryString);
for ($i = 0; $i < $sth->rowCount(); $i++) {
    $row = $sth->fetch();
    echo '<tr>';
    //add leading 0s to make card num 16 digits long
    $cardNum = $row["BreezecardNum"];
    while (strlen($cardNum) < 16) {
        $cardNum = "0".$cardNum;
    }
    echo '<td>'.$row["Time"].'</td>';
    echo '<td>'.$row["Source"].'</td>';
    echo '<td>'.$row["Destination"].'</td>';
    echo '<td>'.$row["Fare Paid"].'</td>';
    echo '<td>'.$cardNum."</td>";
    echo '</tr>';
}
?>
        </tbody>
    </table>

    <br>
    <form action="passenger_home.php">
        <input type="submit" value="Back to Passenger Home">
    </form>
    </body>


<script>
/**
From W3Schools sorting algo
*/
function sortTable(n) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById("trips");
  switching = true;
  // Set the sorting direction to ascending:
  dir = "asc";
  /* Make a loop that will continue until
  no switching has been done: */
  while (switching) {
    // Start by saying: no switching is done:
    switching = false;
    rows = table.getElementsByTagName("TR");
    /* Loop through all table rows (except the
    first, which contains table headers): */
    for (i = 1; i < (rows.length - 1); i++) {
      // Start by saying there should be no switching:
      shouldSwitch = false;
      /* Get the two elements you want to compare,
      one from current row and one from the next: */
      x = rows[i].getElementsByTagName("TD")[n];
      y = rows[i + 1].getElementsByTagName("TD")[n];
      /* Check if the two rows should switch place,
      based on the direction, asc or desc: */
      if (dir == "asc") {
        if (isNumeric(x.innerHTML.toLowerCase()) && isNumeric(y.innerHTML.toLowerCase())) {
            if (parseFloat(x.innerHTML) > parseFloat(y.innerHTML)) {
                shouldSwitch = true;
                break;
            }
        } else if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
          // If so, mark as a switch and break the loop:
          shouldSwitch= true;
          break;
        }
      } else if (dir == "desc") {
        if (isNumeric(x.innerHTML.toLowerCase()) && isNumeric(y.innerHTML.toLowerCase())) {
            if (parseFloat(x.innerHTML) < parseFloat(y.innerHTML)) {
                shouldSwitch = true;
                break;
            }
        } else if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
          // If so, mark as a switch and break the loop:
          shouldSwitch= true;
          break;
        }
      }
    }
    if (shouldSwitch) {
      /* If a switch has been marked, make the switch
      and mark that a switch has been done: */
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      // Each time a switch is done, increase this count by 1:
      switchcount ++;
    } else {
      /* If no switching has been done AND the direction is "asc",
      set the direction to "desc" and run the while loop again. */
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
  }
}
function isNumeric(num){
  return !isNaN(num)
}
</script>
<style>
.flexy {
    display: block;
    width: 60%;
    border: 1px solid #eee;
    max-height: 320px;
    overflow: auto;
}

.flexy thead {
    display: -webkit-flex;
    -webkit-flex-flow: row wrap;
}

.flexy thead tr {
    display: -webkit-flex;
    width: 100%;
}

.flexy tbody {
    display: -webkit-flex;
    max-height: 320px;
    overflow: auto;
    -webkit-flex-flow: row wrap;
}
.flexy tbody tr{
    display: -webkit-flex;
    width: 100%;
}

.flexy tr td {
    width: 100%;
}
.flexy thead th {
    width: 100%;
}
</style>
</html>