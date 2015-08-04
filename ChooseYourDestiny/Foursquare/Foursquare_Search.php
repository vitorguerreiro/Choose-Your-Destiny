<?php
session_start();

$client_key = $_SESSION['client_key'];
$client_secret = $_SESSION['client_secret'];
$user_id = $_SESSION['user_id'];
$link = require 'MySQL/ConnectionDB.php';
$foursquare = require 'Foursquare_Connection.php';
$location = $_SESSION['search_venue'];
$radius = mt_rand(5000, 10000);

list($latitude,$longitude) = $foursquare->GeoLocate($location);

// Foursquare explore venues
$curlhandle = curl_init();
curl_setopt($curlhandle, CURLOPT_URL, "https://api.foursquare.com/v2/venues/explore?ll=$latitude,$longitude"
        . "&radius=$radius&query=$location&limit=50&client_id=$client_key&client_secret=$client_secret&v=20140814");
curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, 1); // Verdadeiro para retornar string
$response = curl_exec($curlhandle);
curl_close($curlhandle);

$venues = json_decode($response);

$venues2 = $venues;

mysqli_query($link, "DELETE FROM Foursquare_Venues_Info WHERE user_id = '$user_id'");

$sql = "INSERT INTO Foursquare_Venues_Info (user_id, venue_id, name, phone, twitter, url, address, latitude, longitude, city, state, country, likes, "
       . "category_id, category_name, user_count, price_tier, price_message, price_currency, hours_status, is_open, rating, rating_signals, checkin_count) "
       . "VALUES ";

foreach ($venues->response->groups[0]->items as $items)
{
    $venue_rating = $items->venue->rating;
    
    $venues_info .= "('" . mysqli_escape_string($link, $user_id) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->id) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->name) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->contact->phone) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->contact->twitter) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->url) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->location->address) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->location->lat) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->location->lng) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->location->city) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->location->state) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->location->country) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->likes->count) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->categories[0]->id) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->categories[0]->name) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->stats->usersCount) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->price->tier) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->price->message) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->price->currency) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->hours->status) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->hours->isOpen) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->rating) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->ratingSignals) . "', "
                . "'" . mysqli_escape_string($link, $items->venue->stats->checkinsCount) . "'),";
}

if ($venues_info != "")
{
    $venues_info = substr($venues_info, 0, strlen($venues_info)-1);
    $venues_info .= ";";
    
    $sql .= $venues_info;
    
    $retrival = mysqli_query($link, $sql);

    if(!$retrival){
        die('Could not entered data'.mysqli_error());
    }
}
