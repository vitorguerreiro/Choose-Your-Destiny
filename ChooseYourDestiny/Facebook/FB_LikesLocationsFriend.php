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
        /* Requisita user_id */
        $user_info = (new FacebookRequest(
          $session, 'GET', '/me?fields=id'
        ))->execute()->getGraphObject();       
        $user_id = $user_info->getProperty('id');
        
        /* Requisita friends_id, friends_likes */
        $friend_info = (new FacebookRequest(
          $session, 'GET', '/me/friends?fields=id,likes'
        ))->execute()->getGraphObject();

        /* ----------------- Friends likes ----------------- */
        $data_friends = $friend_info->getProperty('data');
        $data_array_friends = $data_friends->asArray();
        
        $sql_likes = "INSERT INTO FB_Friend_Likes (fb_user_id, fb_friend_id, category, name, created_time, like_id) VALUES ";
        
        foreach ($data_array_friends as $fields_friends)
        {
            $friend_id = $fields_friends->id;
            $likes = $fields_friends->likes->data;

            foreach ($likes as $fields)
            {
                $like_id = $fields->id;
                $check_likes = mysqli_query($link, "SELECT * FROM FB_Friend_Likes WHERE like_id = $like_id");

                /* Verifica se like_id já existe */
                if (!(mysqli_num_rows($check_likes) > 0))
                { /* Não existe */
                    $values_likes .= "('" . mysqli_escape_string($link, $user_id) . "', "
                            ."'" . mysqli_escape_string($link, $friend_id) . "', "
                            ."'" . mysqli_escape_string($link, $fields->category) . "', "
                            ."'" . mysqli_escape_string($link, $fields->name) . "', "
                            ."'" . mysqli_escape_string($link, $fields->created_time) . "', "
                            ."'" . mysqli_escape_string($link, $fields->id) . "'),";
                }
            }
        }
        
        if ($values_likes != "")
        {
            $values_likes = substr($values_likes, 0, strlen($values_likes)-1);
            $values_likes .= ";";

            $sql_likes .= $values_likes;

            $retrival = mysqli_query($link, $sql_likes);

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