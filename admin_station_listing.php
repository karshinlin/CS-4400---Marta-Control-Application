<?php
include 'dbinfo.php' ;
session_start();
?>
<!DOCTYPE html>
<head>
    <title>Station Listing</title>
    <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.2.1.min.js"></script>
</head>
<body>
<!-- construct table-->
<table id="myTable2" border="1px:solid-black" class="flexy">
    <thead>
        <tr>
            <th style="cursor:pointer" onclick="sortTable(0)">Station Name</th>
            <th style="cursor:pointer" onclick="sortTable(1)">Stop ID</th>
            <th style="cursor:pointer" onclick="sortTable(2)">Fare</th>
            <th style="cursor:pointer" onclick="sortTable(3)">Status</th>
        </tr>
    </thead>
    <tbody>
<?php
$sth = $pdo->query("SELECT StopID, Name, EnterFare, ClosedStatus FROM Station");
for ($i = 0; $i < $sth->rowCount(); $i++) {
    $row = $sth->fetch();
?>
    <tr onclick="ajaxCall('<?php echo $row["StopID"]?>')">
        <td><?php echo $row["Name"]?></td>
        <td><?php echo $row["StopID"]?></td>
        <td><?php echo $row["EnterFare"]?></td>
        <td><?php echo ($row["ClosedStatus"] ? "Closed" : "Open"); ?></td>
    </tr>
<?php } ?>
</tbody>
</table>
<br>
<button onclick="ajaxQuery()">Create New Station</button>
<br>
<form action="admin_home.php">
    <input type="submit" value="Back to Admin Home">
</form>
<body>
<script>
function ajaxCall(stopID) {
    $.ajax({
        method: "GET",
        url: "station_detail.php",
        data: {StopID: stopID},
        success: function(data) {
            window.location = 'station_detail.php?StopID=' + stopID;
        }
    });
}
function ajaxQuery() {
    $.ajax({
        method: "GET",
        url: "create_station.php",
        success: function(data) {
            window.location = 'create_station.php';
        }
    });
}
/**
From W3Schools sorting algo
*/
function sortTable(n) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById("myTable2");
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
    width: 25%;
}
.flexy thead th {
    width: 25%;
}
</style>
</html>