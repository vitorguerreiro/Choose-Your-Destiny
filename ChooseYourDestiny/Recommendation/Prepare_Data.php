<?php

/* 
 * Arquivo que separa os likes de determinada categoria e os adiciona do array "data"
 * para ser analisado pelo algoritmo k-means
 */

function search($ctg)
{
    $link = require 'MySQL/ConnectionDB.php';
    $name = $ctg;
    $filter = mysqli_query($link,"SELECT like_category_id, number_likes FROM Times_Friends_Like_Category WHERE number_likes <> '0' "
            . "AND like_category = '$name'");

    $data = array();
    $num = 0;

    while ($points = mysqli_fetch_array($filter)) 
    {
        $data[$num] = array($points[0],$points[1]);   
        $num++;
    }   

    return $data;
}