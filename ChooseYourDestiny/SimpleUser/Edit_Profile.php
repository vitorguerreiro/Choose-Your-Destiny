<?php
session_start();
?>
<!DOCTYPE html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Login Form</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <script type="text/javascript">
        function cancel()
        {
            location.replace("../Home_Page.php");
        }
    </script>
    <section class="container">
        <div class="login">
        <h1>Choose Your Destiny</h1>
        <?php
            $link = require '../MySQL/ConnectionDB.php';
            $username_query = $_SESSION['username_simple'];
            
            $sql_query = "SELECT * FROM SignIn_User WHERE username = '$username_query'";                 
            
            $retrival = mysqli_query($link, $sql_query);

            if(!$retrival){
                die('Could not entered data'.mysqli_error());
            }
            
            while ($row = mysqli_fetch_assoc($retrival))
            {
                $first_name_echo = $row["first_name"];
                $last_name_echo = $row["last_name"];
                $gender_echo = $row["gender"];
                $email_echo = $row["email"];               
            }
            
            mysqli_close($link);   
        ?>
        <form method="post" action="">
            <p><input type="text" name="first_name" value="<?php echo $first_name_echo ?>" placeholder="First Name"></p>
            <p><input type="text" name="last_name" value="<?php echo $last_name_echo ?>" placeholder="Last Name"></p> 
            <p><input type="text" name="gender" value="<?php echo $gender_echo ?>" placeholder="Gender"></p> 
            <p><input type="text" name="email" value="<?php echo $email_echo ?>" placeholder="E-mail"></p> 
            <p><input type="password" name="password" value="" placeholder="Update your password"></p> 
            <p class="signup"><input type="submit" value="Save" name="save_profile"></p>
        </form>
        <?php
            if(isset($_POST['save_profile']))
            {
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $gender = $_POST['gender'];
                $email = $_POST['email'];
                $username = $_SESSION['username_simple'];
                $password_tmp = $_POST['password'];
                
                /* Password Encryption */
                $password = md5($password_tmp);
                
                $link = require '../MySQL/ConnectionDB.php';
                
                $sql = "UPDATE SignIn_User SET first_name = '$first_name', last_name = '$last_name', gender = '$gender', email = '$email', password = '$password' "
                        . "WHERE username = '$username'";
                
                $retrival = mysqli_query($link, $sql);

                if(!$retrival){
                    die('Could not entered data'.mysqli_error());
                }

                mysqli_close($link);
                ?>
                <script type="text/javascript">
                    location.replace("../Home_Page.php");
                </script>
                <?php
            }
        ?>       
        <p class="cancel"><input type="submit" value="Cancel" onclick="cancel();"></p>    
        </div>
    </section>
</body>
</html>
