<?php
include 'dbinfo.php' ;
session_start();
date_default_timezone_set('America/New_York');

if (isset($_POST["SelectedRemoveCard"])) {
    $cardNum = ltrim($_POST["SelectedRemoveCard"], '0');
    $queryString = 'SELECT BreezecardNum, Tripfare, StartsName, EndsName, Value, BelongsTo, B.IsTrain FROM (SELECT A.*, Station.Name AS EndsName FROM (SELECT Trip.*, Station.Name as StartsName, Station.IsTrain FROM Trip LEFT JOIN Station ON Trip.StartsAt=Station.StopID) AS A LEFT JOIN Station ON A.EndsAt = Station.StopID) AS B NATURAL LEFT JOIN Breezecard';
    $lth = $pdo->query($queryString." WHERE BelongsTo='".$_SESSION["CurrentUser"]."' AND EndsName IS NULL");
    if ($lth->rowCount() > 0) {
        $row = $lth->fetch();
        if ($row["BreezecardNum"] == $cardNum) {
            echo "Cannot remove a card that is tied to an active trip.<br>";
        } else {
            $sth = $pdo->query("UPDATE Breezecard SET BelongsTo = NULL WHERE BreezecardNum='".$cardNum."'");
        }
    } else {
        $sth = $pdo->query("UPDATE Breezecard SET BelongsTo = NULL WHERE BreezecardNum='".$cardNum."'");
    }
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
}
if (isset($_POST["CardToAdd"])) {
    $cardNum = ltrim($_POST["CardToAdd"], '0');
    $sth = $pdo->query("SELECT * FROM Breezecard WHERE BreezecardNum='".$cardNum."'");
    $row = $sth->fetch();
    //card already in Breezecard table
    if ($sth->rowCount() > 0) {
        //no conflict
        if (empty($row["BelongsTo"])) {
            //set BelongTo register user
            $pdo->query("UPDATE Breezecard SET BelongsTo='".$_SESSION["CurrentUser"]."' WHERE BreezecardNum='".$row["BreezecardNum"]."'");
        } else {
        //conflict exists. Do nothing if adding one's own card.
            if ($row["BelongsTo"] != $_SESSION["CurrentUser"]) {
                $date = date("Y-m-d H:i:s", time());
                //insert into conflict table
                $pdo->query("INSERT INTO Conflict(Username, BreezecardNum, DateTime) VALUES ('".$_SESSION["CurrentUser"]."','".$cardNum."','".$date."')");
                echo "Card already has user and has been suspended.<br>";
            }

        }
    } else {
        //make new card with given number and add to Breezecard table
        $pth = $pdo->query("INSERT INTO Breezecard(BreezecardNum, Value, BelongsTo) VALUES ('".$cardNum."','0','".$_SESSION["CurrentUser"]."')");
        echo "A new Breeze Card with balance $0.00 has been added.<br>";
    }
}
if (isset($_POST["SelectedValueCard"])) {
    $breezecardNum = explode(':', $_POST["SelectedValueCard"])[0];
    $oldValue = explode(':', $_POST["SelectedValueCard"])[1];
    $creditCard = $_POST["creditCard"];
    if (strlen($creditCard) != 16) {
        echo "Please enter a valid credit card.<br>";
    } else {
        if ($_POST["ValueToAdd"] < 0 || ($oldValue + $_POST["ValueToAdd"]) > 1000) {
            echo "Breeze Cards can only hold values from $0 to $1000.<br>";
        } else {
            $cardNum = ltrim($breezecardNum, '0');
            $sth = $pdo->query("UPDATE Breezecard SET Value='".($_POST["ValueToAdd"] + $oldValue)."' WHERE BreezecardNum='".$cardNum."'");
            if (!$sth) {
                echo "Unable to add value to selected card.<br>";
            }
        }

    }
}
?>

<!DOCTYPE html>
<head>
    <title>Manage Cards</title>
</head>
<body>
    <h2>Breeze Cards</h2>
    <table id="passengerCards" border="1px:solid-black" class="flexy">
        <thead>
            <tr>
                <th style="cursor:pointer" onclick="sortTable(0)">Card Number</th>
                <th style="cursor:pointer" onclick="sortTable(1)">Value</th>
            </tr>
        </thead>
        <tbody>
<?php

$sth = $pdo->query("SELECT BreezecardNum, Value FROM Breezecard WHERE BelongsTo='".$_SESSION["CurrentUser"]."' AND BreezecardNum NOT IN (SELECT BreezecardNum FROM Conflict)");
for ($i = 0; $i < $sth->rowCount(); $i++) {
    $row = $sth->fetch();
    echo '<tr>';
    //add leading 0s to make card num 16 digits long
    $cardNum = $row["BreezecardNum"];
    while (strlen($cardNum) < 16) {
        $cardNum = "0".$cardNum;
    }
    echo '<td>'.$cardNum."</td>";
    echo '<td>'.$row["Value"]."</td>";
    echo '</tr>';
}
?>

        </tbody>
    </table>
    <form method="POST" action="passenger_card_management.php">
        <input id="SelectedRemoveCard" name="SelectedRemoveCard" type="hidden" value="BreezecardNum">
        <input type="submit" value="Remove Selected Card">
    </form>
    <br>
    <form method="POST" action="passenger_card_management.php">
        <input id="CardToAdd" type="text" name="CardToAdd" required>
        <input type="submit" value="Add Card">
    </form>
    <form method="POST" action="passenger_card_management.php">
        <h3>Add Value to Selected Card:</h3>
        <table>
            <tr>
                <td>Credit Card #</td>
                <td><input type="number" step="1" name="creditCard" required>*16 digit number</td>
            </tr>
            <tr>
                <td>Value</td>
                <td><input type="number" step="0.01" name="ValueToAdd" required></td>
            </tr>
        </table>
        <input type="hidden" name="SelectedValueCard" id="SelectedValueCard" value="BreezecardNum">
        <input type="submit" value="Add Value">
    </form>
    <br>
    <form action="passenger_home.php">
        <input type="submit" value="Back to Passenger Home">
    </form>
</body>

<script>
function highlight_row() {
    var table = document.getElementById('passengerCards');
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
            document.getElementById("SelectedValueCard").value = rowSelected.cells[0].innerHTML + ":" + rowSelected.cells[1].innerHTML;
            document.getElementById("SelectedRemoveCard").value = rowSelected.cells[0].innerHTML;
        }
        //initially have first row selected
        if (i == 0) {
            var rowId = cell.parentNode.rowIndex;
            var rowsNotSelected = table.getElementsByTagName('tr');
            var rowSelected = table.getElementsByTagName('tr')[rowId];
            rowSelected.style.backgroundColor = "yellow";
            rowSelected.className += " selected";
            document.getElementById("SelectedValueCard").value = rowSelected.cells[0].innerHTML + ":" + rowSelected.cells[1].innerHTML;
            document.getElementById("SelectedRemoveCard").value = rowSelected.cells[0].innerHTML;
        }
    }

}
window.onload = highlight_row;
/**
From W3Schools sorting algo
*/
function sortTable(n) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById("passengerCards");
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
    width: 40%;
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
