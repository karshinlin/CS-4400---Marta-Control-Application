<?php
include 'dbinfo.php' ;
session_start();
$additions = 0;
if (isset($_POST["StartTime"]) && !empty($_POST["StartTime"])) {
    $filterStart = date("Y-m-d H:i:s", strtotime($_POST["StartTime"]));
    $addStart = " WHERE StartTime >= '".$filterStart."'";
    $additions++;
}
if (isset($_POST["EndTime"]) && !empty($_POST["EndTime"])) {
    $filterEnd = date("Y-m-d H:i:s", strtotime($_POST["EndTime"]));
    if ($additions == 0) {
        $addEnd = " WHERE StartTime <= '".$filterEnd."' ";
    } else {
        $addEnd = " AND StartTime <= '".$filterEnd."' ";
    }
}
$flowQuery = "SELECT Name, C.NumPassengerStart AS  '# Passengers In', C.NumPassengersEnd AS  '# Passengers Out', (C.NumPassengerStart - C.NumPassengersEnd) as Flow, C.Revenue, IsTrain
    FROM Station
        LEFT JOIN (
        (
            SELECT * FROM (

                SELECT StartsAt, SUM( Tripfare ) AS Revenue, COUNT( * ) AS NumPassengerStart
                FROM Trip".$addStart.$addEnd."
                GROUP BY StartsAt
                ) AS A

            LEFT JOIN (

                SELECT EndsAt, COUNT( * ) AS NumPassengersEnd
                FROM Trip
                WHERE EndsAt IS NOT NULL
                GROUP BY EndsAt) AS B
            ON A.StartsAt = B.EndsAt
            )

            UNION (

            SELECT * FROM (

                SELECT StartsAt, SUM( Tripfare ) AS Revenue, COUNT( * ) AS NumPassengerStart
                FROM Trip".$addStart.$addEnd."
                GROUP BY StartsAt
                ) AS A
            RIGHT JOIN (

                SELECT EndsAt, COUNT( * ) AS NumPassengersEnd
                FROM Trip
                WHERE EndsAt IS NOT NULL
                GROUP BY EndsAt) AS B
            ON A.StartsAt = B.EndsAt
            )
        ) AS C
        ON StopID = C.StartsAt WHERE C.Revenue IS NOT NULL";
?>

<!DOCTYPE html>
<head>
<title>Passenger Flow Report</title>
</head>
<body>
    <form method="POST" action="passenger_flow.php">
        <table>
            <tr>
                <td>Start Time</td>
                <td><input type="date" name="StartTime"></td>
                <td rowspan="2"><button onclick="reset()">Reset</button><input type="submit" value="Update"></td>
            </tr>
            <tr>
                <td>End Time</td>
                <td><input type="date" name="EndTime"></td>
            </tr>
        </table>
    </form>
    <table id="flow" border="1px:solid-black" class="flexy">
    <thead>
        <tr>
            <th style="cursor:pointer" onclick="sortTable(0)">Station Name</th>
            <th style="cursor:pointer" onclick="sortTable(1)"># Passgeners In</th>
            <th style="cursor:pointer" onclick="sortTable(2)"># Passengers Out</th>
            <th style="cursor:pointer" onclick="sortTable(3)">Flow</th>
            <th style="cursor:pointer" onclick="sortTable(4)">Revenue</th>
            <th style="cursor:pointer" onclick="sortTable(5)">Station Type</th>
        </tr>
    </thead>
    <tbody>
<?php
$sth = $pdo->query($flowQuery);
if (!$sth) {
    echo "Could not load flow report.<br>";
} else {
    for ($i = 0; $i < $sth->rowCount(); $i++) {
        $row = $sth->fetch();
        echo '<tr>';
        echo '<td>'.$row["Name"].'</td>';
        echo '<td>'.$row["# Passengers In"].'</td>';
        if (empty($row["# Passengers Out"])) {
            echo '<td>0</td>';
            echo '<td>'.$row["# Passengers In"].'</td>';
        } else {
            echo '<td>'.$row["# Passengers Out"].'</td>';
            echo '<td>'.$row["Flow"].'</td>';
        }
        echo '<td>'.$row["Revenue"]."</td>";
        if ($row["IsTrain"]) {
            echo '<td>Train</td>';
        } else {
            echo '<td>Bus</td>';
        }
        echo '</tr>';
    }
}

?>
        </tbody>
    </table>

<form action="admin_home.php">
    <input type="submit" value="Back to Admin Home">
</form>
    </body>


<script>
/**
From W3Schools sorting algo
*/
function sortTable(n) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById("flow");
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