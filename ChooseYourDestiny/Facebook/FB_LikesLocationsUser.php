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
        $user_info = (new FacebookRequest(
          $session, 'GET', '/me?fields=likes,tagged_places'
        ))->execute()->getGraphObject();

        $user_id = $user_info->getProperty('id');
        
        /* ----------------- Likes ----------------- */
        $likes = $user_info->getProperty('likes');        
        $data_likes = $likes->getProperty('data');
        $data_array_likes = $data_likes->asArray();     

        $sql_likes = "INSERT INTO FB_User_Likes (fb_user_id, category, name, created_time, like_id) VALUES ";
        
        foreach ($data_array_likes as $fields)
        {
            $like_id = $fields->id;
            $check_likes = mysqli_query($link, "SELECT * FROM FB_User_Likes WHERE like_id = $like_id");

            /* Verifica se like_id já existe */
            if (!(mysqli_num_rows($check_likes) > 0))
            { /* Não existe */
                $values_likes .= "('" . mysqli_escape_string($link, $user_id) . "', "
                        ."'" . mysqli_escape_string($link, $fields->category) . "', "
                        ."'" . mysqli_escape_string($link, $fields->name) . "', "
                        ."'" . mysqli_escape_string($link, $fields->created_time) . "', "
                        ."'" . mysqli_escape_string($link, $fields->id) . "'),";
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

        /* ----------------- Places ----------------- */
        $places = $user_info->getProperty('tagged_places');
        $data_places = $places->getProperty('data');
        $data_array_places = $data_places->asArray();
        
        $sql_places = "INSERT INTO FB_User_Places (fb_user_id, place_id, place_name, place_latitude, place_longitude, tagged_place_id) VALUES ";
        
        foreach ($data_array_places as $fields)
        {
            $tagged_place = $fields->id;
            $check_places = mysqli_query($link, "SELECT * FROM FB_User_Places WHERE tagged_place = $tagged_place");

            /* Verifica se like_id já existe */
            if (!(mysqli_num_rows($check_places) > 0))
            { /* Não existe */
                $values_places .= "('" . mysqli_escape_string($link, $user_id) . "', "
                        ."'" . mysqli_escape_string($link, $fields->place->id) . "', "
                        ."'" . mysqli_escape_string($link, $fields->place->name) . "', "
                        ."'" . mysqli_escape_string($link, $fields->place->location->latitude) . "', "
                        ."'" . mysqli_escape_string($link, $fields->place->location->longitude) . "', "
                        ."'" . mysqli_escape_string($link, $fields->id) . "'),";
            }
        }
        
        if ($values_places != "")
        {
            $values_places = substr($values_places, 0, strlen($values_places)-1);
            $values_places .= ";";

            $sql_places .= $values_places;

            $retrival = mysqli_query($link, $sql_places);

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