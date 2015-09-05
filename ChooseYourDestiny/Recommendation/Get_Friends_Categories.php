<?php
session_start();

$client_key = $_SESSION['foursquare_client_key'];
$client_secret = $_SESSION['foursquare_client_secret'];
$user_id = $_SESSION['user_id'];
$location = $_SESSION['search_venue'];

$link = require 'MySQL/ConnectionDB.php';
$foursquare = require 'Foursquare_Connection.php';

$radius = mt_rand(5000, 10000);

$search_location = mysqli_query($link, "SELECT place_name, place_latitude, place_longitude FROM FB_Friend_Places WHERE place_name NOT IN"
                . "(SELECT place_name FROM FB_Locations_Categories) GROUP BY place_name");

if($search_location){
    //require '/Applications/MAMP/htdocs/WhatNextInc1/Foursquare/foursquare-search-kmeans.php';

    $category_array = array();
    $qtd = 0;

    /* Procurar pelos nomes dos lugares que a pessoa fez check-in no facebook e encontrar categorias */
    while ($value = mysqli_fetch_array($search_location)) 
    {
        $insert = null;
        $category_found = null;
        $venue_found = FALSE;
            
        // $category_found = find_categories($value[0],$value[1],$value[2]); // find categories function is inside foursquare-search-kmeans     
        /* Foursquare search venues */
        $curlhandle = curl_init();
        curl_setopt($curlhandle, CURLOPT_URL, "https://api.foursquare.com/v2/venues/search?ll=$value[1],$value[2]"
                . "&radius=$radius&query=$location&limit=10&client_id=$client_key&client_secret=$client_secret&v=20140814");
        curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, 1); // Verdadeiro para retornar string
        $response = curl_exec($curlhandle);
        curl_close($curlhandle);

        $venues = json_decode($response);

        $teste = 0;
        do{
            $resp = ($venues->response->venues[$teste]->id);
            $teste++;

        }while($resp);
        
        for($i=0;$i<$teste-1;$i++)
        {
            if($venues->response->venues[$i]->name == $value[0])
            {
                $category_found = $venues->response->venues[$i]->categories[0]->id;
                $venue_found = TRUE;
                break;
            }
        }
        
        if($venue_found == FALSE)
        {
            $delete_locations = mysqli_query($link, "DELETE FROM FB_Friend_Places WHERE place_name = '$value[0]'");
        }
        
        if($category_found)
        {
            $insert .= "('" . mysqli_escape_string($link,$value[0]). "',"
                    . "'" . mysqli_escape_string($link,$value[1]). "', "
                    . "'" . mysqli_escape_string($link,$value[2]). "', "
                    . "'" . mysqli_escape_string($link, $category_found) . "'),";

            $categories = "INSERT INTO FB_Locations_Categories (place_name, place_latitude, place_longitude, category) VALUES";

            if($insert != "")
            {
                $insert = substr($insert, 0, strlen($insert) -1);
                $insert .= ";";
            }

            $categories .= $insert;

            if($insert)
            {
                $entry = mysqli_query($link,$categories);
                if(!$entry){
                    die('Could not entered data'.mysqli_error());
                }
            }
        }
    }
}