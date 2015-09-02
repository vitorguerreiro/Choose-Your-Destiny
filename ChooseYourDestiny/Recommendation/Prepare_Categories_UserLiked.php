<?php
session_start();

/* 
 *  Arquivo que popula tabela da quantidade de vezes que o usuário (cliente) curtiu uma determinada categoria
 */

$link = require 'MySQL/ConnectionDB.php';

if($_SESSION['user_mode'] == 'facebook')
{
    $user = $_SESSION['user_id'];
}
else if($_SESSION['user_mode'] == 'simple')
{
    $user = $_SESSION['user_id'];
}

mysqli_query($link,"DELETE FROM Times_User_Like_Category WHERE user_id = $user");

$get_likes_categories = mysqli_query($link, "SELECT * FROM FB_Likes_Categories");


$times_user_like_category = "INSERT INTO Times_User_Like_Category (user_id,like_category,like_category_id,number_likes) VALUES";
$insert = null;
$has_likes = mysqli_query($link, "SELECT * FROM FB_User_Likes");
$has_likes_num = mysqli_num_rows($has_likes);
if($has_likes_num)
{
    while ($cat = mysqli_fetch_array($get_likes_categories)) 
    {
        $result = mysqli_query($link, "SELECT * FROM FB_User_Likes WHERE category_id = '$cat[1]' AND fb_user_id = '$user'");

        $num = mysqli_num_rows($result);

        if($num)
        {
            $insert .= "('" . mysqli_escape_string($link,$user). "', "
                      . "'" . mysqli_escape_string($link,$cat[1]). "', "
                      . "'" . mysqli_escape_string($link,$cat[0]). "', "
                      . "'" . mysqli_escape_string($link, $num) . "'),";
        }

    }
}

if($insert != "")
{
    $insert = substr($insert, 0, strlen($insert) -1);
    $insert .= ";";
}

$times_user_like_category .= $insert;

if($insert)
{
    $entry = mysqli_query($link,$times_user_like_category);
    if(!$entry){
        die('Could not entered data'.mysql_error());
    }
}   

mysqli_close($link);