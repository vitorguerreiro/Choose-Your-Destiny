<?php
session_start();

if($_SESSION['user_mode'] == 'facebook')
{
    $user = $_SESSION['user_id'];
}
else if($_SESSION['user_mode'] == 'simple')
{
    $user = $_SESSION['user_id'];
}

$location = $_SESSION['search_venue'];

$link = require 'MySQL/ConnectionDB.php';
$foursquare = require 'Foursquare_Connection.php';

mysqli_query($link, "DELETE FROM Venues_Info_Recommended WHERE user_id = '$user'");
mysqli_query($link, "DELETE FROM Venues_Info_Recommended_Food WHERE user_id = '$user'");
mysqli_query($link, "DELETE FROM Venues_Info_Recommended_Nightlife WHERE user_id = '$user'");

list($latitude,$longitude) = $foursquare->GeoLocate($location);

$categories = mysqli_query($link,"SELECT category_id, COUNT(ctg_id) AS num FROM Top_Categories "
            . "WHERE user_id = '$user' GROUP BY category_id ORDER BY num DESC");

$breakCondition = 0;
$breakConditionNightlife = 0;
$breakConditionFood = 0;

$_SESSION['latitude'] = $latitude;
$_SESSION['longitude'] = $longitude;

$count = 0;
$count_nightlife = 0;
$count_food = 0;
$array = array();
$array_nightlife = array();
$array_food = array();

if($_SESSION['user_mode'] == 'facebook')
{   
    /* Categorias novas */
    while($value = mysqli_fetch_array($categories)) 
    {                  
        $check_category = mysqli_query($link, "SELECT * FROM Foursquare_Categories WHERE sub_id = '$value[ctg_id]'");

        $check_ctg_result = mysqli_fetch_array($check_category);

        /* General Recommendation */
        if($check_ctg_result[main_ctg_name]!='Nightlife Spot' && $check_ctg_result[main_ctg_name]!='Food' && $check_ctg_result[main_ctg_name]!= null)
        {
            if($breakCondition < 30)
            {
                $categoryId = $value[ctg_id];
                $radius = mt_rand(1000, 5000);
                $limit = mt_rand(3, 7);

                /* Foursquare explore */
                $curlhandle = curl_init();
                curl_setopt($curlhandle, CURLOPT_URL, "https://api.foursquare.com/v2/venues/explore?ll=$latitude,$longitude&categoryId=$categoryId"
                        . "&radius=$radius&limit=$limit&client_id=$client_key&client_secret=$client_secret&v=20140501");
                curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, 1); /* Verdadeiro para retornar como string */
                $response = curl_exec($curlhandle);
                curl_close($curlhandle);

                $venues = json_decode($response);

                $teste = 0;
                do{
                    $resp = ($venues->response->groups[0]->items[$teste]);
                    $teste++;

                }while($resp);

                for($returnVenues = 0;$returnVenues<$teste-1;$returnVenues++)
                {
                    $venue_id = $venues->response->groups[0]->items[$returnVenues]->venue->id;
                    $venue_name = $venues->response->groups[0]->items[$returnVenues]->venue->name;
                    $venue_rating = $venues->response->groups[0]->items[$returnVenues]->venue->rating;
                    
                    $idExists = false;
                    for($i=0;$i<=$count;$i++)
                    {
                        if($array[$i] === $venue_id)
                        {
                            $idExists = true;
                            break;
                        }
                    }

                    if($idExists==false && $venue_rating>=7)
                    {
                        $array[$count] = $venue_id;
                        $count++;

                        $venues_values .= "('" . mysqli_escape_string($link,$user) . "', "
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->id) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->name) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->contact->phone) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->contact->twitter) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->url) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->address) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->lat) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->lng) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->city) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->state) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->country) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->likes->count) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->categories[0]->id) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->categories[0]->name) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->stats->usersCount) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->price->tier) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->price->message) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->price->currency) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->hours->status) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->hours->isOpen) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->rating) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->ratingSignals) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->stats->checkinsCount) . "'),";

                        $breakCondition++;
                    }
                } 
            }   
        }

        /* RECOMMENDED NIGHTLIFE PLACES */
        else if($d_ctg === false && $check_ctg_result[main_ctg_name] === 'Nightlife Spot')
        {
            if($breakConditionNightlife < 30)
            {
                $categoryId = $value[ctg_id];
                $radius = mt_rand(1000, 5000);
                $limit = mt_rand(3, 7);

                /* Foursquare explore */
                $curlhandle = curl_init();
                curl_setopt($curlhandle, CURLOPT_URL, "https://api.foursquare.com/v2/venues/explore?ll=$latitude,$longitude&categoryId=$categoryId"
                        . "&radius=$radius&limit=$limit&client_id=$client_key&client_secret=$client_secret&v=20140501");
                curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, 1); /* Verdadeiro para retornar como string */
                $response = curl_exec($curlhandle);
                curl_close($curlhandle);

                $venues = json_decode($response);

                $teste = 0;
                do{
                    $resp = ($venues->response->groups[0]->items[$teste]);
                    $teste++;

                }while($resp);

                for($returnVenues = 0;$returnVenues<$teste-1;$returnVenues++)
                {
                    $venue_id = $venues->response->groups[0]->items[$returnVenues]->venue->id;
                    $venue_name = $venues->response->groups[0]->items[$returnVenues]->venue->name;
                    $venue_rating = $venues->response->groups[0]->items[$returnVenues]->venue->rating;

                    $idExists = false;
                    for($i=0;$i<=$count_nightlife;$i++)
                    {
                        if($array_nightlife[$i] === $venue_id)
                        {
                            $idExists = true;
                            break;
                        }
                    }

                    if($idExists==false && $venue_rating>=7)
                    {
                        $array_nightlife[$count_nightlife] = $venue_id;
                        $count_nightlife++;

                        $venues_values_nightlife .= "('" . mysqli_escape_string($link,$user) . "', "
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->id) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->name) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->contact->phone) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->contact->twitter) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->url) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->address) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->lat) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->lng) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->city) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->state) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->country) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->likes->count) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->categories[0]->id) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->categories[0]->name) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->stats->usersCount) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->price->tier) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->price->message) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->price->currency) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->hours->status) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->hours->isOpen) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->rating) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->ratingSignals) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->stats->checkinsCount) . "'),";

                        $breakConditionNightlife++;
                    }                   
                } 
            }

        }

        /* recommended food places */
        else if($d_ctg === false && $check_ctg_result[main_ctg_name] === 'Food')
        {
            if($breakConditionFood < 30)
            {
                $categoryId = $value[ctg_id];
                $radius = mt_rand(1000, 5000);
                $limit = mt_rand(3, 7);

                /* Foursquare explore */
                $curlhandle = curl_init();
                curl_setopt($curlhandle, CURLOPT_URL, "https://api.foursquare.com/v2/venues/explore?ll=$latitude,$longitude&categoryId=$categoryId"
                        . "&radius=$radius&limit=$limit&client_id=$client_key&client_secret=$client_secret&v=20140501");
                curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, 1); /* Verdadeiro para retornar como string */
                $response = curl_exec($curlhandle);
                curl_close($curlhandle);

                $venues = json_decode($response);

                $teste = 0;
                do{
                    $resp = ($venues->response->groups[0]->items[$teste]);
                    $teste++;

                }while($resp);

                for($returnVenues = 0;$returnVenues<$teste-1;$returnVenues++)
                {
                    $venue_id = $venues->response->groups[0]->items[$returnVenues]->venue->id;
                    $venue_name = $venues->response->groups[0]->items[$returnVenues]->venue->name;
                    $venue_rating = $venues->response->groups[0]->items[$returnVenues]->venue->rating;
                    
                    $idExists = false;
                    for($i=0;$i<=$count_food;$i++)
                    {
                        if($array_food[$i] === $venue_id)
                        {
                            $idExists = true;
                            break;
                        }
                    }

                    if($idExists==false && $venue_rating>=7)
                    {
                        $array_food[$count_food] = $venue_id;
                        $count_food++;

                        $venues_values_food .= "('" . mysqli_escape_string($link,$user) . "', "
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->id) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->name) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->contact->phone) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->contact->twitter) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->url) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->address) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->lat) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->lng) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->city) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->state) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->location->country) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->likes->count) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->categories[0]->id) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->categories[0]->name) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->stats->usersCount) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->price->tier) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->price->message) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->price->currency) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->hours->status) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->hours->isOpen) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->rating) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->ratingSignals) . "',"
                        . "'" . mysqli_escape_string($link,$venues->response->groups[0]->items[$returnVenues]->venue->stats->checkinsCount) . "'),";

                        $breakConditionFood++;
                    }
                } 
            }    
        }

        if ($breakCondition >= 30 && $breakConditionFood >= 30 && $breakConditionNightlife >= 30)
        {
            break;
        }
    }

    $sql_venues = "INSERT INTO Venues_Info_Recommended"
                . "(user_id, venue_id, name, phone, twitter, url, address, "
                . "latitude, longitude, city, state, country, "
                . "likes, category_id, category_name, user_count, price_tier, price_message, "
                . "price_currency, hours_status, is_open, rating, rating_signals, checkin_count) VALUES ";

    $sql_venues_nightlife = "INSERT INTO Venues_Info_Recommended_Nightlife"
                . "(user_id, venue_id, name, phone, twitter, url, address, "
                . "latitude, longitude, city, state, country, "
                . "likes, category_id, category_name, user_count, price_tier, price_message, "
                . "price_currency, hours_status, is_open, rating, rating_signals, checkin_count) VALUES ";

    $sql_venues_food = "INSERT INTO Venues_Info_Recommended_Food"
                . "(user_id, venue_id, name, phone, twitter, url, address, "
                . "latitude, longitude, city, state, country, "
                . "likes, category_id, category_name, user_count, price_tier, price_message, "
                . "price_currency, hours_status, is_open, rating, rating_signals, checkin_count) VALUES ";


    if($venues_values != "")
    {
        $venues_values = substr($venues_values, 0, strlen($venues_values) -1);
        $venues_values .= ";";
    }

    $sql_venues .= $venues_values;

    if($venues_values_nightlife != "")
    {
        $venues_values_nightlife = substr($venues_values_nightlife, 0, strlen($venues_values_nightlife) -1);
        $venues_values_nightlife .= ";";
    }

    $sql_venues_nightlife .= $venues_values_nightlife;

    if($venues_values_food != "")
    {
        $venues_values_food = substr($venues_values_food, 0, strlen($venues_values_food) -1);
        $venues_values_food .= ";";
    }

    $sql_venues_food .= $venues_values_food;

    if($venues_values)
    {
        $venue_entry = mysqli_query($link,$sql_venues);
        if(!$venue_entry){
            die('Could not entered data general_2'.mysql_error());
        }
    }

    if($venues_values_nightlife)
    {
        $venue_entry_nightlife = mysqli_query($link,$sql_venues_nightlife);
        if(!$venue_entry_nightlife){
            die('Could not entered data nightlife_2'.mysql_error());
        }
    }

    if($venues_values_food)
    {
        $venue_entry_food = mysqli_query($link,$sql_venues_food);
        if(!$venue_entry_food){
            die('Could not entered data food_2'.mysql_error());
        }
    }
}