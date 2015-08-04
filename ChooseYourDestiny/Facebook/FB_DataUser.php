<?php
session_start();

$link = require 'MySQL/ConnectionDB.php';
require 'facebook-php-sdk/autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;

FacebookSession::setDefaultApplication('1391059227889416', '95c8327c743782c13cf03f7a14986197');

$session = new FacebookSession($_SESSION['access_token']);
 
if($session) {
    try {
      $user_profile = (new FacebookRequest(
        $session, 'GET', '/me'
      ))->execute()->getGraphObject();

      $_SESSION['user_id'] = $user_profile->getProperty('id');
      
      $fb_id = $user_profile->getProperty('id');
      $name = $user_profile->getProperty('name');
      $first_name = $user_profile->getProperty('first_name');
      $last_name = $user_profile->getProperty('last_name');
      $birthday = $user_profile->getProperty('birthday');
      $gender = $user_profile->getProperty('gender');

      $sql = "SELECT * FROM FB_User WHERE fb_id = '$fb_id' ";
      $user_exist = mysqli_query($link, $sql);

      /* Verifica se usuário já existe */
      if (!(mysqli_num_rows($user_exist) > 0))
      { /* Usuário não existe */
          $sql = "INSERT INTO FB_User (fb_id, name, first_name, last_name, birthday, gender)".
                  " VALUES ('$fb_id','$name','$first_name','$last_name','$birthday','$gender');";

          $retrival = mysqli_query($link, $sql);

          if(!$retrival){
              die('Could not entered data'.mysqli_error());
          }

          mysqli_close($link);
      }
      else
      { /* Usuário existe */
          $sql = "UPDATE FB_User SET name = '$name', first_name = '$first_name', last_name = '$last_name', birthday = '$birthday', gender = '$gender' WHERE fb_id = '$fb_id'";

          $retrival = mysqli_query($link, $sql);

          if(!$retrival){
              die('Could not entered data'.mysqli_error());
          }

          mysqli_close($link);
      }
    } catch(FacebookRequestException $e) {
          //echo "Exception occured, code: " . $e->getCode();
          //echo " with message: " . $e->getMessage();

          mysqli_close($link);
    }   
}

