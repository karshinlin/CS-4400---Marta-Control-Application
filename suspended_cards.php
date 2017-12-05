<?php
include 'dbinfo.php' ;
session_start();
ini_set('display_errors', 1);

if (isset($_POST["assignToNew"])) {
    $cardNum = ltrim($_POST["assignToNew"], '0');
    $sth = $pdo->query("UPDATE Breezecard SET BelongsTo='".$_POST["NewUser"]."' WHERE BreezecardNum='".$cardNum."'");
    $pth = $pdo->query("DELETE FROM Conflict WHERE BreezecardNum='".$cardNum."'");
    if (!$sth || !$pth) {
        echo "Unable to assign to user.<br>";
    }
} else if (isset($_POST["assignToPrev"])) {
    $cardNum = ltrim($_POST["assignToPrev"], '0');
    $pth = $pdo->query("DELETE FROM Conflict WHERE BreezecardNum='".$cardNum."'");
    if (!$pth) {
        echo "Unable to assign to user.<br>";
    }
}

//need to check if Passengers without Breezecards
$sth = $pdo->query('SELECT Username FROM Passenger WHERE Username NOT IN (SELECT BelongsTo FROM Breezecard WHERE BelongsTo IS NOT NULL AND BreezecardNum NOT IN (SELECT BreezecardNum FROM Conflict))');
$rows = $sth->fetchAll();
foreach($rows as $row) {
    $random = 0;
    $found = FALSE;
    while (true) {
        $random = rand(0, 9999999999999999);
        $sth = $pdo->query("SELECT * FROM Breezecard WHERE BreezecardNum='".$random."'");
        // echo "SELECT * FROM Breezecard WHERE BreezecardNum='".$random."'"."<br>";
        if ($sth->rowCount() == 0) {
            break;
        }
    }
    $pth = $pdo->query("INSERT INTO Breezecard(BreezecardNum, Value, BelongsTo) VALUES ('".$random."','0','".$row["Username"]."')");
    if (!$pth) {
        echo "Unable to assign to create new card.<br>";
    }
}
?>

<!DOCTYPE html>
<head>
<title>Suspended Cards</title>
</head>
<body>
    <table id="suspendedCards" border="1px:solid-black" class="flexy">
        <thead>
            <tr>
                <th style="cursor:pointer" onclick="sortTable(0)">Card #</th>
                <th style="cursor:pointer" onclick="sortTable(1)">New Owner</th>
                <th style="cursor:pointer" onclick="sortTable(2)">Date Suspended</th>
                <th style="cursor:pointer" onclick="sortTable(3)">Previous Owner</th>
            </tr>
        </thead>
        <tbody>
<?php

$sth = $pdo->query('SELECT C.BreezecardNum, C.Username as NewOwner, C.DateTime, B.BelongsTo as PreviousOwner FROM Conflict as C NATURAL LEFT JOIN Breezecard as B');
for ($i = 0; $i < $sth->rowCount(); $i++) {
    $row = $sth->fetch();
    echo '<tr>';
    //add leading 0s to make card num 16 digits long
    $cardNum = $row["BreezecardNum"];
    while (strlen($cardNum) < 16) {
        $cardNum = "0".$cardNum;
    }
    echo '<td>'.$cardNum."</td>";
    echo '<td>'.$row["NewOwner"]."</td>";
    echo '<td>'.$row["DateTime"]."</td>";
    echo '<td>'.$row["PreviousOwner"]."</td>";
    echo '</tr>';
}
?>
        </tbody>
    </table>
    <form method="POST" action="suspended_cards.php">
        <input id="toNew" type="hidden" name="assignToNew" value="Yes">
        <input id="NewUser" type="hidden" name="NewUser" value="Yes">
        <input type="submit" value="Assign Selected Card to New Owner">
    </form>
    <form method="POST" action="suspended_cards.php">
        <input id="toPrev" type="hidden" name="assignToPrev" value="Yes">
        <input type="submit" value="Assign Selected Card to Previous Owner">
    </form>
    <br>
    <form action="admin_home.php">
        <input type="submit" value="Back to Admin Home">
    </form>
</body>
<script>
function highlight_row() {
    var table = document.getElementById('suspendedCards');
    var cells = table.getElementsByTagName('td');

    for (var i = 0; i < cells.length; i++) {
        // Take each cell
        var cell = cells[i];
        // do something on onclick event for cell
        cell.onclick = function () {
            // Get the row id where the cell exists
            var rowId = this.parentNode.rowIndex;
            var rowsNotSelected = table.getElementsByTagName('tr');
            for (var row = 0; row < rowsNotSelected.length; row++) {
                rowsNotSelected[row].style.backgroundColor = "";
                rowsNotSelected[row].classList.remove('selected');
            }
            var rowSelected = table.getElementsByTagName('tr')[rowId];
            rowSelected.style.backgroundColor = "yellow";
            rowSelected.className += " selected";
            //store value of selected row for form action
            document.getElementById("toNew").value = rowSelected.cells[0].innerHTML;
            document.getElementById("toPrev").value = rowSelected.cells[0].innerHTML;
            document.getElementById("NewUser").value = rowSelected.cells[1].innerHTML;
        }
        //initially have first row selected
        if (i == 0) {
            var rowId = cell.parentNode.rowIndex;
            var rowsNotSelected = table.getElementsByTagName('tr');
            var rowSelected = table.getElementsByTagName('tr')[rowId];
            rowSelected.style.backgroundColor = "yellow";
            rowSelected.className += " selected";
            document.getElementById("toNew").value = rowSelected.cells[0].innerHTML;
            document.getElementById("toPrev").value = rowSelected.cells[0].innerHTML;
            document.getElementById("NewUser").value = rowSelected.cells[1].innerHTML;
        }
    }

}
window.onload = highlight_row;
/**
From W3Schools sorting algo
*/
function sortTable(n) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById("suspendedCards");
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