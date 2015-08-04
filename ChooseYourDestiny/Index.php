<?php
session_start();
//$_SESSION = array();
?>
<!DOCTYPE html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Choose Your Destiny</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php
        $_SESSION['user_mode'] = "";
        
        /* Se usuário conectou com sucesso no Facebook, guarda todas informações obtidas do usuário 
         * e as categorias do Foursquare. Depois redireciona para a página Home_Page.php */
        if ($_SESSION['fb_status'] == 'connected')
        {
            $_SESSION['user_mode'] = 'facebook';

            require_once 'Facebook/FB_DataUser.php';
            require_once 'Facebook/FB_DataFriends.php';
            require_once 'Facebook/FB_LikesLocationsUser.php';
            require_once 'Facebook/FB_LikesLocationsFriend.php';
            require_once 'Foursquare/Foursquare_Categories.php';
            ?>
            <script type="text/javascript">
                location.replace("Home_Page.php");
            </script>
            <?php
        }
    ?>
    <section class="container">
      <div class="login">
        <h1>Choose Your Destiny</h1>       
        <form method="post" action="">
            <p><input type="text" name="index_username" value="" placeholder="Username"></p>
            <p><input type="password" name="index_password" value="" placeholder="Password"></p> 
            <p class="submit"><input type="submit" name="login_user" value="Login"></p>
        </form>  
        <form method="post" action="">         
            <p class="signin"><input type="submit" name="sign_in" value="Sign In"></p>
        </form>
        <?php
            /* Se o botão Sign In for pressionado, redireciona para página Sign_In.php */
            if(isset($_POST['sign_in']))
            {
                ?>
                <script type="text/javascript">
                    location.replace("SimpleUser/Sign_In.php");
                </script>
                <?php
            }
            else if(isset($_POST['login_user']))
            {   /* Se o botão Login for pressionado, coloca username e password nas sessions e redireciona para Login.php */
                $_SESSION['user_id'] = $_POST['index_username'];
                $_SESSION['username_simple'] = $_POST['index_username'];
                $_SESSION['password_simple'] = $_POST['index_password'];
                ?>
                <script type="text/javascript">
                    location.replace("SimpleUser/Login.php");
                </script>
                <?php
            }
        ?>
        <p class="facebook-login">
          <?php
            /* Botão de login no Facebook */
            require_once 'Facebook/FB_Login.php';
          ?>
        </p>
        <p id="log_error">
            <?php
                /* Mensagem de erro */
                echo $_SESSION['log_error'];
            ?>
        </p>
      </div>
    </section>
</body>
</html>
