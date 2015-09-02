<?php

/* 
 * Arquivo que filtra as categorias existentes em todos os likes dos usuários do fb contidos na tabela fb-people
 */

$link = require 'MySQL/ConnectionDB.php';

    $query = mysqli_query($link, "SELECT category FROM FB_Friend_Likes GROUP BY category");

    $insert_likeCategories = "INSERT INTO FB_Likes_Categories (category_name) VALUES";

    if ($query) 
    {
	while ($row = mysqli_fetch_row($query)) 
	{
	        $has_category = mysqli_query($link, "SELECT * FROM FB_Likes_Categories WHERE category_name = '$row[0]'");
	
	        if(!$has_category || mysqli_num_rows($has_category)== 0) 
	        {
	            $insert .= "('" . mysqli_escape_string($link,$row[0]). "'),";
	        }
	}
    }

    if($insert != "")
    {
        $insert = substr($insert, 0, strlen($insert) -1);
        $insert .= ";";
    }

    $insert_likeCategories .= $insert;

    if($insert)
    {
        $likescategory_entry = mysqli_query($link,$insert_likeCategories);
        if(!$likescategory_entry){
            die('Could not entered data'.mysql_error());
        }
    }

mysqli_close($link);
