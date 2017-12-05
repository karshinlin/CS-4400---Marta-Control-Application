<?php
include 'dbinfo.php' ;
session_start();
date_default_timezone_set('America/New_York');

    //echo "Username: ".$_POST['RegisterUsername']."<br>";
    if (isset($_POST["RegisterUsername"])) {
        $sth = $pdo->query("SELECT Username FROM User WHERE Username = '".$_POST['RegisterUsername']."'");
        $lth = $pdo->query("SELECT Email FROM Passenger WHERE Email = '".$_POST["RegistrationEmail"]."'");
        //echo "SELECT Email FROM User WHERE Email = '".$_POST["RegistrationEmail"]."'<br>";
        if ($lth->rowCount() != 0) {
            echo "Email already exists. Please login in or enter another email";
            exit;
        }
        //echo "SELECT Username FROM User WHERE Username = '".$_POST["RegisterUsername"]."'"."<br>";
        if ($sth->rowCount() != 0) {
            echo "Username already exists. Please login in or enter another username";
            exit;
        } else {
            $hashedPassword = password_hash($_POST['RegistrationPassword'], PASSWORD_BCRYPT);
            //crypt($_POST['RegistrationPass'], $salt);
            //echo $_POST['RegistrationPassword']."<br>";
            //echo $hashedPassword."<br>";
            $cardOption = $_POST["breezecardOption"];
            //echo $cardOption."<br>";
            if ($cardOption == 'ExistingCard') {
                //check if card already tied to owner, then there is conflict
                $sth = $pdo->query("SELECT * FROM Breezecard WHERE BreezecardNum='".$_POST["ExistingCard"]."'");
                //echo "SELECT * FROM Breezecard WHERE BreezecardNum='".$_POST["ExistingCard"]."'"."<br>";
                $row = $sth->fetch();
                //card already in Breezecard table
                if ($sth->rowCount() > 0) {
                    //create new Passenger
                    $sth = $pdo->query("INSERT INTO User(Username, Password, isAdmin) VALUES ('".$_POST["RegisterUsername"]."','".$hashedPassword."','FALSE')");
                    //echo "INSERT INTO User(Username, Password, isAdmin) VALUES ('".$_POST["RegisterUsername"]."','".$hashedPassword."','FALSE')"."<br>";
                    $pth = $pdo->query("INSERT INTO Passenger(Username, Email) VALUES ('".$_POST["RegisterUsername"]."','".$_POST["RegistrationEmail"]."')");
                    //echo "INSERT INTO Passenger(Username, Email) VALUES ('".$_POST["RegisterUsername"]."','".$_POST["RegistrationEmail"]."')"."<br>";
                    if (!$sth || !$pth) {
                        echo "Unable to create new account. Please try again";
                    }
                    //no conflict
                    if (empty($row["BelongsTo"])) {
                        //set BelongTo register user
                        $pdo->query("UPDATE Breezecard SET BelongsTo='".$_POST["RegisterUsername"]."' WHERE BreezecardNum='".$row["BreezecardNum"]."'");
                       // echo "UPDATE Breezecard SET BelongsTo='".$_POST["RegisterUsername"]."' WHERE BreezecardNum='".$row["BreezecardNum"]."'"."<br>";
                    } else {
                    //conflict exists
                        $conflictCard = $row["BreezecardNum"];
                        $date = date("Y-m-d H:i:s", time());
                        //insert into conflict table
                        $pdo->query("INSERT INTO Conflict(Username, BreezecardNum, DateTime) VALUES ('".$_POST["RegisterUsername"]."','".$conflictCard."','".$date."')");
                        //echo "INSERT INTO Conflict(Username, BreezecardNum, DateTime) VALUES ('".$_POST["RegisterUsername"]."','".$conflictCard."','".$date."')"."<br>";
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
                    header('Location: login.php');
                } else {
                    //Breeze that was entered does not exist
                    echo "Breezecard does not exist. Please try again";
                    exit;
                }

            } else if ($cardOption == 'NewCard') {
                //add card to breezecards with owner
                $sth = $pdo->query("INSERT INTO User(Username, Password, isAdmin) VALUES ('".$_POST["RegisterUsername"]."','".$hashedPassword."','FALSE')");
                // echo "INSERT INTO User(Username, Password, isAdmin) VALUES ('".$_POST["RegisterUsername"]."','".$hashedPassword."','FALSE')"."<br>";
                $pth = $pdo->query("INSERT INTO Passenger(Username, Email) VALUES ('".$_POST["RegisterUsername"]."','".$_POST["RegistrationEmail"]."')");
                // echo "INSERT INTO Passenger(Username, Email) VALUES ('".$_POST["RegisterUsername"]."','".$_POST["RegistrationEmail"]."')"."<br>";
                if (!$sth || !$pth) {
                    echo "Unable to create new account. Please try again";
                }
                $random = 0;
                while (true) {
                    $random = rand(0, 9999999999999999);
                    $sth = $pdo->query("SELECT * FROM Breezecard WHERE BreezecardNum='".$random."'");
                    // echo "SELECT * FROM Breezecard WHERE BreezecardNum='".$random."'"."<br>";
                    if ($sth->rowCount() == 0) {
                        break;
                    }
                }
                $pth = $pdo->query("INSERT INTO Breezecard(BreezecardNum, Value, BelongsTo) VALUES ('".$random."','0','".$_POST["RegisterUsername"]."')");
                // echo "INSERT INTO Breezecard(BreezecardNum, Value, BelongsTo) VALUES ('".$random."','0','".$_POST["RegisterUsername"]."')"."<br>";
                header('Location: login.php');
            }
        }
    }
?>
<!DOCTYPE html>
<title>Create a MARTA Account</title>
<form action="register.php" method="post" onsubmit=" return validateForm()">
    Username:
    <input type="text" id="RegisterUsername" name="RegisterUsername" required><br>
    Email Address:
    <input type="email" id="RegisterEmail" name="RegistrationEmail" required><br>
    Password:
    <input type="password" id="RegistrationPass" name="RegistrationPassword" minlength="8" required><br>
    Confirm Password:
    <input type="password" id="ConfirmPass" name="ConfirmPassword" required><br>
    <br>
    <input type="radio" id="existingCardRB" name="breezecardOption" value="ExistingCard" checked>Use my existing Breeze Card<br>
    Card Number <input type="text" id="existingCardNum" name="ExistingCard"><br>
    <input type="radio" id="newCardRB" name="breezecardOption" value="NewCard">Get a new Breeze Card<br>
    <br>
    <input type="submit" value="Create Account">
</form>

</html>

<script>
    function validateForm() {
        if (document.getElementById('RegisterUsername') == "" ||
            document.getElementById('RegisterEmail') == "" ||
            document.getElementById('RegistrationPass') == "") {
            return false;
        }
        if (!document.getElementById("ConfirmPass").value === document.getElementById("RegistrationPass").value) {
            return false;
        }
        if (!document.getElementById("existingCardRB").checked && !document.getElementById("newCardRB").checked) {
            return false;
        }
        if (document.getElementById("existingCardRB").checked) {
            if (document.getElementById("existingCardNum").value == "") {
                return false;
            }
        }
        return true;
    }
</script>

