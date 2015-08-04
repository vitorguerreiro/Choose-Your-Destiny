<?php
session_start();
?>
<!DOCTYPE html>

<html>
    <head>
        <script type="text/javascript" src="//code.jquery.com/jquery-1.11.3.min.js"></script>
    </head>
    <body>      
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
        <fb:login-button scope="public_profile,email,user_friends,user_birthday,user_likes,user_tagged_places" onlogin="checkLoginState();" 
                         data-max-rows="1" data-size="large">
        </fb:login-button>
    </body>
</html>