<?php
session_start();

$link = require '../MySQL/ConnectionDB.php';

/* Passa usuário e senha pelas sessions. */
$index_login = $_SESSION['username_simple'];
$index_password_temp = $_SESSION['password_simple'];

/* Password Encryption */
$index_password = md5($index_password_temp);

/* Verifica se usuário existe */
$check_simple_user = mysqli_query($link, "SELECT * FROM SignIn_User WHERE username = '$index_login' AND password = '$index_password'");

/* Se número de linhas retornadas em check_simple_user for maior que 0, usuário existe. */
if (mysqli_num_rows($check_simple_user) > 0)
{
    $_SESSION['user_mode'] = 'simple';
    ?>
    <script type="text/javascript">
        location.replace("../Home_Page.php");
    </script>
    <?php
}
else /* Senão, usuário ou senha incorretas. */
{
    $_SESSION['user_mode'] = "";
    $_SESSION['log_error'] = 'Wrong username or password.';
    ?>
    <script type="text/javascript">
        location.replace("../Index.php");
    </script>
    <?php
}