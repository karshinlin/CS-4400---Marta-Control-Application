<?php
include 'dbinfo.php' ;
session_start();
ini_set('display_errors', 1);

    if (isset($_POST["SetValueSelectedCard"])) {
        if ($_POST["SetValue"] < 0 || $_POST["SetValue"] > 1000) {
            echo "Breeze Cards can only hold values from $0 to $1000.<br>";
        } else {
            $cardNum = ltrim($_POST["SetValueSelectedCard"], '0');
            $yth = $pdo->query("UPDATE Breezecard SET Value='".$_POST["SetValue"]."' WHERE BreezecardNum = '".$cardNum."'");
            if (!$yth) {
                echo "Could not set the value of the selected card.<br>";
            }
        }

    } else if (isset($_POST["TransferSelectedCard"])) {
        $cardNum = ltrim($_POST["TransferSelectedCard"], '0');
        $yth = $pdo->query("UPDATE Breezecard SET BelongsTo ='".$_POST["TransferCard"]."' WHERE BreezecardNum = '".$cardNum."'");
        if (!$yth) {
            echo "Passenger not found. Please enter a valid passenger.";
        } else {
            $pth = $pdo->query("DELETE FROM Conflict WHERE BreezecardNum='".$cardNum."'");
        }
        //chedk if anyone left without Breezecard
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

?>

<!DOCTYPE html>
<head>
<title>Suspended Cards</title>
</head>
<body>

    <h2>Breeze Cards</h2>
    <p>Search/Filter</p>
    <form method="POST" action="admin_card_management.php">
        <table>
            <tr>
                <td>Owner</td>
                <td><input type="text" name="Owner"></td>
                <?php
                    if (isset($_POST["ShowSuspended"])) {
                        $checkOn = "checked";
                    } else {
                        $checkOn = "";
                    }
                ?>
                <td><input type="checkbox" name="ShowSuspended"<?php echo $checkOn; if (!isset($_POST["dummyCheck"])) echo "checked" ?>>Show Suspended Cards<input type="hidden" name="Suspended" value="Yes"><input type="hidden" name="dummyCheck"></td>
            </tr>
            <tr>
                <td>Card Number</td>
                <td><input type="text" name="CardNumber"></td>
                <td><button onclick="reset()">Reset</button></td>
            </tr>
            <tr>
                <td>Value Between</td>
                <td><input type="number" step="0.01" name="ValueLower">and<input type="number" step="0.01" name="ValueUpper"></td>
                <td><input type="submit" value="Update Filter"></td>
            </tr>
        </table>
    </form>
    <table id="breezeCards" border="1px:solid-black" class="flexy">
        <thead>
            <tr>
                <th style="cursor:pointer" onclick="sortTable(0)">Card #</th>
                <th style="cursor:pointer" onclick="sortTable(1)">Value</th>
                <th style="cursor:pointer" onclick="sortTable(2)">Owner</th>
            </tr>
        </thead>
        <tbody>
<?php
if (isset($_POST["ShowSuspended"]) || !isset($_POST["dummyCheck"])) {
    $queryString = "SELECT B.BreezecardNum, B.Value, B.BelongsTo, C.Username as NewOwner FROM Breezecard as B NATURAL LEFT JOIN Conflict as C";
} else {
    $queryString = "SELECT B.BreezecardNum, B.Value, B.BelongsTo, C.Username as NewOwner FROM Breezecard as B NATURAL LEFT JOIN Conflict as C WHERE C.Username IS NULL";
}
$additions = 0;
if (isset($_POST["Owner"]) && !empty($_POST["Owner"])) {
    if ($additions == 0) {
        $queryString .= " WHERE B.BelongsTo = '".$_POST["Owner"]."' AND C.Username IS NULL";
    } else {
        $queryString .= " AND B.BelongsTo = '".$_POST["Owner"]."' AND C.Username IS NULL";
    }
    $additions++;
}
if (isset($_POST["CardNumber"]) && !empty($_POST["CardNumber"])) {
    $cardNum = ltrim($_POST["CardNumber"], '0');
    if ($additions == 0) {
        $queryString .= " WHERE B.BreezecardNum = '".$cardNum."'";
    } else {
        $queryString .= " AND B.BreezecardNum = '".$cardNum."'";
    }
    $additions++;
}
if (isset($_POST["ValueLower"]) && !empty($_POST["ValueLower"])) {
    if ($additions == 0) {
        $queryString .= " WHERE B.Value >= '".$_POST["ValueLower"]."'";
    } else {
        $queryString .= " AND B.Value >= '".$_POST["ValueLower"]."'";
    }
    $additions++;
}
if (isset($_POST["ValueUpper"]) && !empty($_POST["ValueUpper"])) {
    if ($additions == 0) {
        $queryString .= " WHERE B.Value <= '".$_POST["ValueUpper"]."'";
    } else {
        $queryString .= " AND B.Value <= '".$_POST["ValueUpper"]."'";
    }
    $additions++;
}
$sth = $pdo->query($queryString);
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

    if (!is_null($row["NewOwner"])) {
        $status = "<b>Suspended</b>";
    } else {
        $status = $row["BelongsTo"];
    }
    echo '<td>'.$status."</td>";
    echo '</tr>';
}
?>
        </tbody>
    </table>

    <form method="POST" action="admin_card_management.php">
        <input type="number" step="0.01" name="SetValue" required>
        <input type="hidden" id="SetValueSelectedCard" name="SetValueSelectedCard" value="Yes">
        <input type="submit" value="Set Value of Selected Card">
    </form>
    <form method="POST" action="admin_card_management.php">
        <input type="text" step="0.01" name="TransferCard" required>
        <input type="hidden" id="TransferSelectedCard" name="TransferSelectedCard" value="Yes">
        <input type="submit" value="Transfer Selected Card">
    </form>
    <br>
    <form action="admin_home.php">
        <input type="submit" value="Back to Admin Home">
    </form>
</body>


<script>
function highlight_row() {
    var table = document.getElementById('breezeCards');
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
            document.getElementById("SetValueSelectedCard").value = rowSelected.cells[0].innerHTML;
            document.getElementById("TransferSelectedCard").value = rowSelected.cells[0].innerHTML;
        }
        //initially have first row selected
        if (i == 0) {
            var rowId = cell.parentNode.rowIndex;
            var rowsNotSelected = table.getElementsByTagName('tr');
            var rowSelected = table.getElementsByTagName('tr')[rowId];
            rowSelected.style.backgroundColor = "yellow";
            rowSelected.className += " selected";
            document.getElementById("SetValueSelectedCard").value = rowSelected.cells[0].innerHTML;
            document.getElementById("TransferSelectedCard").value = rowSelected.cells[0].innerHTML;
        }
    }

}
window.onload = highlight_row;
/**
From W3Schools sorting algo
*/
function sortTable(n) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById("breezeCards");
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