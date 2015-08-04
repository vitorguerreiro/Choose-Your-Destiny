<?php
$link = require '../MySQL/ConnectionDB.php';

if (!empty($_POST['username']))
{
    $check_username = $_POST['username'];
    $check = mysqli_query($link, "SELECT * FROM SignIn_User WHERE username = '$check_username'");

    if (mysqli_num_rows($check) > 0)
    {
        echo 'User already exists';
    }
    else
    {
        new_user();
    }
}

mysqli_close($link);

function new_user()
{
    $link = require '../MySQL/ConnectionDB.php';
    
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password_temp = $_POST['password'];

    /* Password Encryption */
    $password = md5($password_temp);

    $sql = "INSERT INTO SignIn_User (first_name, last_name, gender, email, username, password) VALUES ".
            "('$first_name', '$last_name', '$gender', '$email', '$username', '$password')";

    $retrival = mysqli_query($link, $sql);

    if(!$retrival){
        die('Could not entered data'.mysqli_error());
    }

    mysqli_close($link);
    ?>
    <script type="text/javascript">
        location.replace("../Index.php");
    </script>
    <?php
}       

