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

mysqli_query($link, "DELETE FROM Venues_Info WHERE user_id = '$user'");

list($latitude,$longitude) = $foursquare->GeoLocate($location);

$categories = mysqli_query($link,"SELECT * FROM Foursquare_Categories");
$qtd_categories = mysqli_num_rows($categories);
$venues_values = null;

$radius = mt_rand(5000, 10000);

// PERMITE CONEXOES COM SERVIDORES EXTERNOS

$curlhandle = curl_init();
curl_setopt($curlhandle, CURLOPT_URL, "https://api.foursquare.com/v2/venues/explore?ll=$latitude,$longitude"
        . "&radius=$radius&client_id=$client_key&client_secret=$client_secret&v=20140814");
curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, 1); // verdadeiro para retornar como string
$response = curl_exec($curlhandle);
curl_close($curlhandle);

$venues = json_decode($response);

$teste = 0;
do{
    $resp = ($venues->response->groups[0]->items[$teste]);
    $teste++;

}while($resp);

for($returnVenues=0;$returnVenues<$teste-1;$returnVenues++)
{

    $venue_id = $venues->response->groups[0]->items[$returnVenues]->venue->id;
    $venue_rating = $venues->response->groups[0]->items[$returnVenues]->venue->rating;
    $venue_already_added = mysqli_query($link,"SELECT * FROM Venues_Info WHERE"
        . " venue_id = '$venue_id' AND user_id = '$user'");

    if((!$venue_already_added || mysqli_num_rows($venue_already_added)== 0) && $venue_rating>7)
    {

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

    } 
}


$sql_venues = "INSERT INTO Venues_Info "
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

if($venues_values)
{
    $venue_entry = mysqli_query($link,$sql_venues);
    if(!$venue_entry){
        die('Could not entered data trending'.mysql_error());
    }
}
