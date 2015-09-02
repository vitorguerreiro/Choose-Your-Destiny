<?php
session_start();

/* 
 * Procura em qual centroide de determinada categoria o usuário corrente se encaixa melhor.
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

$user_info = mysqli_query($link, "SELECT * FROM Times_User_Like_Category WHERE user_id = '$user'");

$group = array();
$count = 0;

while($info = mysqli_fetch_array($user_info)) 
{    
    $kmeans_info = mysqli_query($link, "SELECT * FROM Kmeans_Centroid_Position WHERE user_id = '$user' AND category_id = $info[3]");
    $min_y = 1000;
    $found = FALSE;
    while($centroid = mysqli_fetch_array($kmeans_info)) 
    {   
        $found = TRUE;
        $dif = $centroid[5]-$info[4]; // $centroid[4] = pos_y e $info[3] = number_likes
        if(abs($dif) < $min_y) // compara menor distancia entre o numero de likes do usuário e a centroide da categoria atual
        {
            $min_y = abs($dif);
            $cent = $centroid[2]; // $centroid[1] = centroid
        }   
    }
    
    if(found == TRUE) // adiciona no array categoria e centroide que deverá ser comparada
    {
        $group[$count][0] = $info[3]; // $info[2] = like_category_id
        $group[$count][1] = $cent;
    }
    $count++;
}

//mysqli_close($link);

return $group;