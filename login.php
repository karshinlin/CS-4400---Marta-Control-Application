<?php
include 'dbinfo.php' ;
session_start();
//echo "URI: ".$_SERVER['HTTP_HOST'];
if (isset($_POST["username"])) {

    $loginUsername = $_POST["username"];
    $loginPassword = $_POST["password"];
    $hashedPassword = password_hash($loginPassword, PASSWORD_BCRYPT);
    //echo $hashedPassword."<br>";
    //echo "SELECT Username,IsAdmin FROM User WHERE Username = '".$loginUsername."' AND Password = '".$loginPassword."'\n";

    $sth = $pdo->query("SELECT Username, Password, IsAdmin FROM User WHERE Username = '".$loginUsername."'");
    //echo "SELECT Username, Password, IsAdmin FROM User WHERE Username = '".$loginUsername."'"."<br>";

    if (!$sth) {
        echo "DB Error, could not query the database\n";
        exit;
    } else if ($sth->rowCount() == 0) {
        echo "Invalid login. Please try again";
    } else if ($sth->rowCount() == 1) {
        $row = $sth->fetch();
        $isAdmin = $row["IsAdmin"];
        $dbPassword = $row["Password"];
        //echo $dbPassword;
        if (password_verify($loginPassword, $dbPassword)) {
            $_SESSION["CurrentUser"] = $loginUsername;
            $_SESSION["IsAdmin"] = $isAdmin;
            if (!$isAdmin) {
                header('Location: passenger_home.php');
            } else {
                header('Location: admin_home.php');
            }
        } else {
            echo "Wrong credentials. Please try again";
        }

    } else {
        echo "Error. Please try again";
    }
}
?>
<!DOCTYPE html>
<title>Log In</title>
<form action="login.php" method="post">
    Username:
    <input type ="text" name="username"><br>
    Password:
    <input type="password" name="password"><br>
    <br>
    <input type="submit" value="Submit">
</form>
<form action="register.php">
    <input type="submit" id="register" value="Register">
</form>
</html>

