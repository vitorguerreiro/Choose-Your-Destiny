<?php

$link = require 'MySQL/ConnectionDB.php';
$foursquare = require 'Foursquare_Connection.php';
$foursquare_categories = $foursquare->GetPublic("venues/categories");
$categories = json_decode($foursquare_categories);

$sql = "INSERT INTO Foursquare_Categories (main_id, main_name, sub_id, sub_name) VALUES ";

foreach ($categories->response->categories as $item)
{
    foreach ($item->categories as $sub_item)
    {
        $check = mysqli_query($link, "SELECT * FROM Foursquare_Categories WHERE sub_id = '$sub_item->id'");
        
        if(mysqli_num_rows($check) == 0)
        {
            $sub_categories .= "('" . mysqli_escape_string($link, $item->id) . "', "
                            . "'" . mysqli_escape_string($link, $item->name) . "', "
                            . "'" . mysqli_escape_string($link, $sub_item->id) . "', "
                            . "'" . mysqli_escape_string($link, $sub_item->name) . "'),";
        }
    }
}

if($sub_categories != "")
{
    $sub_categories = substr($sub_categories, 0, strlen($sub_categories)-1);
    $sub_categories .= ";";
    
    $sql .= $sub_categories;
    
    $retrival = mysqli_query($link, $sql);

    if(!$retrival){
        die('Could not entered data'.mysqli_error());
    }
}


