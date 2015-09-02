<?php
session_start();
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$group = require_once 'Recommendation/kNN.php'; // retorna qual a centroide para cada categoria que o usuário se encaixa

$link = require 'MySQL/ConnectionDB.php';

$insert = null;

if($_SESSION['user_mode'] == 'facebook')
{
    $user = $_SESSION['user_id'];
}
else if($_SESSION['user_mode'] == 'simple')
{
    $user = $_SESSION['user_id'];
}

mysqli_query($link,"DELETE FROM Nearest_Users WHERE current_user_id = '$user'");

$insert_query = "INSERT INTO Nearest_Users (current_user_id, user_id) VALUES";

foreach ($group as $ctg => $value) 
{  
    $query = mysqli_query($link, "SELECT centroid, ctg_id, number_likes FROM Kmeans_Data WHERE "
            . "ctg_id = $value[0] AND centroid = $value[1] GROUP BY number_likes");
    
    while($row = mysqli_fetch_array($query)) // $row = [centroid], [ctg_id], [number_likes]
    {
        $users = mysqli_query($link, "SELECT user_fb_id FROM Times_People_Like_Category "
                . "WHERE like_category_id = '$row[1]' AND number_likes = '$row[2]'"
                . "GROUP BY user_fb_id ");
        
        while($id = mysqli_fetch_array($users)) 
        {
            $insert .= "('" . mysqli_escape_string($link,$user). "',"
                    . "'" . mysqli_escape_string($link, $id[0]) . "'),";
        } 
    }
  
}

if($insert != "")
{
    $insert = substr($insert, 0, strlen($insert) -1);
    $insert .= ";";
}

$insert_query .= $insert;

if($insert)
{
    $entry = mysqli_query($link,$insert_query);
    if(!$entry){
        die('Could not entered data'.mysql_error());
    }
}   
