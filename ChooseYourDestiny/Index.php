<?php
session_start();
//$_SESSION = array();
?>
<!DOCTYPE html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Choose Your Destiny</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="//code.jquery.com/jquery-1.11.3.min.js"></script>
</head>
<body>
    <!-- Facebook Javascript SDK (FB Login Button) -->
    <script type="text/javascript">
        // Transfere accessToken do Javascript para usar na SDK PHP
        function transferToken(token, status) {
            $.ajax({
                url: "Facebook/FB_AccessToken.php",
                data: { 'access_token': token, 'fb_status': status },
                success: function(data) {
                    $access_token = data;
                    if (status === 'connected')
                    {
                        location.replace("Home_Page.php");
                    }
                }
            });
        }
        
        // This is called with the results from from FB.getLoginStatus().
        function statusChangeCallback(response) {
          console.log('statusChangeCallback');
          console.log(response);
          // The response object is returned with a status field that lets the
          // app know the current login status of the person.
          // Full docs on the response object can be found in the documentation
          // for FB.getLoginStatus().
          if (response.status === 'connected') {
            // Logged into your app and Facebook. 
            transferToken(response.authResponse.accessToken, response.status);
          } else if (response.status === 'not_authorized') {
            // The person is logged into Facebook, but not your app.
            transferToken("", response.status);
          } else {
            // The person is not logged into Facebook, so we're not sure if
            // they are logged into this app or not. (unknown)
            transferToken("", response.status);
          }
        }

        // This function is called when someone finishes with the Login
        // Button.  See the onlogin handler attached to it in the sample
        // code below.
        function checkLoginState() {
          FB.getLoginStatus(function(response) {
            statusChangeCallback(response);
          });
        }

        window.fbAsyncInit = function() {
        FB.init({
          appId      : '1391059227889416',
          secret     : '95c8327c743782c13cf03f7a14986197',
          cookie     : true,  // enable cookies to allow the server to access 
                              // the session
          xfbml      : true,  // parse social plugins on this page
          version    : 'v2.3' // use version 2.3
        });

        // Now that we've initialized the JavaScript SDK, we call 
        // FB.getLoginStatus().  This function gets the state of the
        // person visiting this page and can return one of three states to
        // the callback you provide.  They can be:
        //
        // 1. Logged into your app ('connected')
        // 2. Logged into Facebook, but not your app ('not_authorized')
        // 3. Not logged into Facebook and can't tell if they are logged into
        //    your app or not.
        //
        // These three cases are handled in the callback function.

        FB.getLoginStatus(function(response) {
          statusChangeCallback(response);
        });    
        };

        // Load the SDK asynchronously
        (function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = "//connect.facebook.net/en_US/sdk.js";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'faceobok-jssdk'));      
    </script>
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
    <div class="container">  
        <div class="omb_login">
            <h3 class="omb_authTitle">Login or <a href="SimpleUser/Sign_In.php">Sign up</a></h3>
                <div class="omb_authTitle">
                    <div class="omb_authTitle">
                        <fb:login-button scope="public_profile,email,user_friends,user_birthday,user_likes,user_tagged_places" onlogin="checkLoginState();" 
                                         data-max-rows="1" data-size="xlarge">
                        </fb:login-button>
                    </div>                   	
                </div>

                <div class="row omb_row-sm-offset-3 omb_loginOr">
                    <div class="col-xs-12 col-sm-6">
                        <hr class="omb_hrOr">
                        <span class="omb_spanOr">or</span>
                    </div>
                </div>

                <div class="row omb_row-sm-offset-3">
                    <div class="col-xs-12 col-sm-6">	
                        <form class="omb_loginForm" action="" autocomplete="off" method="POST">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control" name="index_username" placeholder="Username">
                            </div>
                            <span class="help-block"></span>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input type="password" class="form-control" name="index_password" placeholder="Password">
                            </div>
                            <span class="help-block">
                                <?php
                                    /* Mensagem de erro, username ou password errado */
                                    echo $_SESSION['log_error'];
                                ?>
                            </span>
                            <input class="btn btn-lg btn-primary btn-block" type="submit" name="login_user" value="Login">
                        </form>
                        <?php
                        /* Se o botão Login for pressionado, coloca username e password nas sessions e redireciona para Login.php */
                        if(isset($_POST['login_user']))
                        {   
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
                    </div>
                </div>
            
                <div class="row omb_row-sm-offset-3">
                    <div class="col-xs-12 col-sm-6">
                        <p class="omb_forgotPwd">
                            <a href="#">Forgot password?</a>
                        </p>
                    </div>
                </div>	    	
            </div>
        </div>
    </section>
</body>
</html>


