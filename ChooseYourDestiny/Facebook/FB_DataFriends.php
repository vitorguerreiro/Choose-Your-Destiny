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

      $friend_profile = (new FacebookRequest(
        $session, 'GET', '/me/friends?fields=id,name,first_name,last_name,birthday,gender'
      ))->execute()->getGraphObject();

      $user_id = $user_profile->getProperty('id');
      $data = $friend_profile->getProperty('data');
      $data_array = $data->asArray();

      $sql = "INSERT INTO FB_Friend (user_id, fb_id, name, first_name, last_name, birthday, gender) VALUES ";

      foreach ($data_array as $fields)
      {
          $user_exist = mysqli_query($link, "SELECT * FROM FB_Friend WHERE user_id = '$user_id' AND fb_id = '$fields->id'");

          /* Verifica se usuário e amigo já existe */
          if (!(mysqli_num_rows($user_exist) > 0))
          { /* Não existe */
              $values .= "('" . mysqli_escape_string($link, $user_id) . "', "
                      ."'" . mysqli_escape_string($link, $fields->id) . "', "
                      ."'" . mysqli_escape_string($link, $fields->name) . "', "
                      ."'" . mysqli_escape_string($link, $fields->first_name) . "', "
                      ."'" . mysqli_escape_string($link, $fields->last_name) . "', "
                      ."'" . mysqli_escape_string($link, $fields->birthday) . "', "
                      ."'" . mysqli_escape_string($link, $fields->gender) . "'),";
          }
      }

      if ($values != "")
      {
          $values = substr($values, 0, strlen($values)-1);
          $values .= ";";

          $sql .= $values;

          $retrival = mysqli_query($link, $sql);

          if(!$retrival){
              die('Could not entered data'.mysqli_error());
          }
      }   

      mysqli_close($link);

    } catch(FacebookRequestException $e) {
          //echo "Exception occured, code: " . $e->getCode();
          //echo " with message: " . $e->getMessage();

          mysqli_close($link);
    }   
}

