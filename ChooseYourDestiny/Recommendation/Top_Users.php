<?php
session_start();

$link = require 'MySQL/ConnectionDB.php';

$insert = null;
$categories = null;

if($_SESSION['user_mode'] == 'facebook')
{
    $user = $_SESSION['user_id'];
}
else if($_SESSION['user_mode'] == 'simple')
{
    $user = $_SESSION['user_id'];
}

$users = mysqli_query($link, "SELECT user_id FROM Nearest_Users WHERE current_user_id = $user GROUP BY user_id");

$num = 0;
$qtd_appear = array();

/* Percorre tabela e retorna quantidade de vezes que um determinado usuário aparece nas centroides escolhidas */
while($value = mysqli_fetch_array($users)) 
{
    $user_info = mysqli_query($link, "SELECT user_id FROM Nearest_Users WHERE $value[0] = user_id AND current_user_id = $user");
    $count = mysqli_num_rows($user_info);
    
    $qtd_appear[$num][0] = $value[0];
    $qtd_appear[$num][1] = $count;
    
    $num++;
}

/* Função para ordenar os usuários que aparecem mais vezes nas centroides em que o usuário se encaixa mais */
function array_sorter (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array); /* Seta o ponteiro interno do array para sua primeira posição */
    foreach ($array as $i => $val) {
        $sorter[$i] = $val[$key];
    }
    arsort($sorter); /* Ordena um array e mantém a relação correspondente */
    foreach ($sorter as $i => $val) {
        $ret[$i]=$array[$i];
    }
    $array = $ret;  
}

array_sorter($qtd_appear,"1");

$selected_users = array_slice($qtd_appear, 0, 100); /* Seleciona as 20 primeiras posicoes do array */

$category_array = array();
$qtd = 0;

/* Procurar pelos nomes dos lugares que a pessoa fez check-in no facebook e encontrar categorias */
foreach ($selected_users as $key => $value) 
{
    $find_locations = mysqli_query($link, "SELECT place_name, place_latitude, place_longitude FROM FB_Friend_Places WHERE fb_friend_id = $value[0] GROUP BY place_name");
    
    while($value = mysqli_fetch_array($find_locations)) 
    {
        //$category_found = find_categories($value[0],$value[1], $value[2]); // find categories function is inside foursquare-search-kmeans
        $check = mysqli_query($link, "SELECT category FROM FB_Locations_Categories WHERE place_name = '$value[0]' AND "
                . "place_latitude = '$value[1]' AND place_longitude = '$value[2]'");
        if($check)
        {
            $check_num_rows = mysqli_num_rows($check);
        }
        
        if($check_num_rows>0)
        {
            $values = mysqli_fetch_array($check);
            if($values != NULL){
                $category_array[$qtd] = $values; /* Array é populado com categorias relacionadas com os gostos dos usuários */
                $qtd++;
            }
        }    
    }        
}

$size = sizeof($category_array, 0);

mysqli_query($link, "DELETE FROM Top_Categories WHERE user_id = '$user'");

$categories = "INSERT INTO Top_Categories (user_id, category_id) VALUES";

for($i = 0; $i < $size; $i++){
    
    $ctg_arr = $category_array[$i];
    
    $insert .= "('" . mysqli_escape_string($link,$user). "',"
            . "'" . mysqli_escape_string($link,$ctg_arr[0]). "'),";
}
 
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
        die('Could not entered data'.mysql_error());
    }
}
