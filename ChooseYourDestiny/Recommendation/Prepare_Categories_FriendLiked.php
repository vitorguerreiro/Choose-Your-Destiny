<?php

/* 
 *  Arquivo que popula tabela da quantidade de vezes que um usuário curtiu uma determinada categoria
 */

$link = require 'MySQL/ConnectionDB.php';

mysqli_query($link,"TRUNCATE TABLE Times_People_Like_Category");

$get_fb_people = mysqli_query($link,"SELECT fb_id FROM FB_Friend GROUP BY fb_id");

if ($get_fb_people) 
{
    while ($userid = mysqli_fetch_array($get_fb_people)) 
    {
        $times_people_like_category = "INSERT INTO Times_Friend_Like_Category (user_fb_id, like_category, like_category_id, number_likes) VALUES";
	$insert = null;

        $get_likes_categories = mysqli_query($link, "SELECT * FROM FB_Likes_Categories");
	if($get_likes_categories)
	{	
	    while ($cat = mysqli_fetch_array($get_likes_categories)) 
	    {
		$result = mysqli_query($link, "SELECT * FROM FB_Friend_Likes WHERE category_id = '$cat[1]' "
		              . "AND fb_friend_id = '$userid[0]'");
		
	        $num = mysqli_num_rows($result);
	        if($num != 0)
	        {
		
		      $insert .= "('" . mysqli_escape_string($link,$userid[0]). "', "
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
        if($insert != "")
        {
            $insert = substr($insert, 0, strlen($insert) -1);
            $insert .= ";";
        }

        $times_people_like_category .= $insert;

        if($insert)
        {
            $entry = mysqli_query($link,$times_people_like_category);
            if(!$entry){
                die('Could not entered data'.mysql_error());
            }
        }
    }
}

//mysqli_close($link);