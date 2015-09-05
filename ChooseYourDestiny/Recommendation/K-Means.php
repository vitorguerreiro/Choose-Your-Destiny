<?php
session_start();

require 'Recommendation/Initialise_Centroids.php';
$link = require 'MySQL/ConnectionDB.php';

$ctg_likes = mysqli_query($link, "SELECT category FROM FB_User_Likes GROUP BY category"); /* Pega as categorias que o usuário atual tem likes */
$num_rows = mysqli_num_rows($ctg_likes);

if($_SESSION['user_mode'] == 'facebook')
{
    $user = $_SESSION['user_id'];
}
else if($_SESSION['user_mode'] == 'simple')
{
    $user = $_SESSION['user_id'];
}

mysqli_query($link,"DELETE FROM Kmeans_Centroid_Position WHERE user_id = $user");

if($num_rows)
{
    $i = 0;
    while ($item = mysqli_fetch_array($ctg_likes)) 
    {      
        require_once 'Recommendation/Prepare_Data.php';
        $data = search($item[0]); /* Procura categoria na tabela onde likes <> 0 */
        $mapeamento = array();

        /* Valor de k setado manualmente para 5 e enviado para a função de inicialização de centroides */
        $centroids = inicializa_centroids($data, 5); /* Posiciona randomicamente as centroides */

        while(1)
        {
            $likes_centroides = posicionar_centroides($data,$centroids); /* Função para posicionar as centroides */
            $changed = false; /* Variável para verificar mudança do posicionamento das centroides */
            $current_category = $data[0][0]; /* Qual a categoria corrente */

            foreach($likes_centroides as $posicao => $centroide) //$likes_centroides -> [n] -> centroide
            {
                if(!isset($mapeamento[$posicao]) || $centroide != $mapeamento[$posicao]) 
                {
                    $mapeamento = $likes_centroides;
                    $changed = true;
                    break;
                }
            }
            if(!$changed)
            {
                $result_kmeans[$i] = formatResults($mapeamento, $data);
                
                save_kmeans_centroids_positions($centroids,$current_category, $user);
                
                if($i == $num_rows-1) /* Verifica se já está na última iteração (última categoria a verificar) */
                {
                    save_kmeans_data($result_kmeans,$user);
                    return $result_kmeans;
                }
                break;
            }
            $centroids  = update_centroides($mapeamento, $data, $k);
        }
    $i++;
    }
}

function save_kmeans_centroids_positions($centroid_position, $current_category, $user)
{    
    $insert = null;
    $link = require 'MySQL/ConnectionDB.php';
    
    $query = "INSERT INTO Kmeans_Centroid_Position (user_id, centroid, category_id, pos_x, pos_y) VALUES";
    
    foreach ($centroid_position as $cnt => $value) 
    {
        $insert .= "('" . mysqli_escape_string($link,$user). "', "
                . "'" . mysqli_escape_string($link,$cnt). "', "
                . "'" . mysqli_escape_string($link,$current_category). "', "
                . "'" . mysqli_escape_string($link,$value[0]). "', "
                . "'" . mysqli_escape_string($link, $value[1]) . "'),";   
    }
    
    if($insert != "")
    {
        $insert = substr($insert, 0, strlen($insert) -1);
        $insert .= ";";
    }
    
    $query .= $insert;
    
    
    if($insert)
    {
        $entry = mysqli_query($link,$query);
        if(!$entry){
            die('Could not entered data'.mysql_error());
        }
    }
    mysqli_close($link);
}

function save_kmeans_data($result_kmeans, $user)
{    
    $insert = null;
    $link = require 'MySQL/ConnectionDB.php';
    
    mysqli_query($link,"DELETE FROM Kmeans_Data WHERE user_id = $user");
    
    $query = "INSERT INTO Kmeans_Data (user_id, centroid, ctg_id, number_likes) VALUES";
    
    foreach ($result_kmeans as $ctg => $category) 
    {
        foreach ($category as $cnt => $value)
        {
            foreach ($value as $key => $num) 
            {
                $insert .= "('" . mysqli_escape_string($link,$user). "', "
                            . "'" . mysqli_escape_string($link,$cnt). "', "
                            . "'" . mysqli_escape_string($link,$num[0]). "', "
                            . "'" . mysqli_escape_string($link, $num[1]) . "'),";
            }
        }
    }
    
    if($insert != "")
    {
        $insert = substr($insert, 0, strlen($insert) -1);
        $insert .= ";";
    }
    
    $query .= $insert;
    
    
    if($insert)
    {
        $entry = mysqli_query($link,$query);
        if(!$entry){
            die('Could not entered data k-means'.mysql_error());
        }
    }
    mysqli_close($link);
}

function posicionar_centroides($data, $centroids)
{
    /* Para cada (category x likes) verificar qual centroide é mais próximo */
    
    $like_in_centroid = array();

    foreach($data as $data_position => $data_position_array) /* $data -> [n] -> [0][1] */
    {
        $minDist = null;
        $right_centroid = null;
        foreach($centroids as $centroid_position => $centroid_position_array) /* $centroide -> [n] -> [0][1] */
        {
            $dist = 0;
            foreach($centroid_position_array as $pos => $value) /* $centroid_position_array -> [0] ou [1] -> [like_ctg] ou [num_likes] */
            {
                /* Distância absoluta é o módulo da distância entre o valor das centroides até o primeiro like de um usuário.
                 * Primeiro é verificação no eixo x e depois no eixo y... a verificação acontece com todas as centroides para cada like */
                $dist += abs($value - $data_position_array[$pos]);
            }
            if(!$minDist || $dist < $minDist) 
            {
                $minDist = $dist;
                /* Recebe o valor 'k' da centroide */
                $right_centroid = $centroid_position;
            }
        }
        $like_in_centroid[$data_position] = $right_centroid;
    }
    return $like_in_centroid;
}

function update_centroides($mapping, $data, $k) 
{
    $centroids = array();
    $counts = array_count_values($mapping);

    foreach($mapping as $posicao => $centroid) /* $mapeamento -> [n] -> centroide */
    {
        foreach($data[$posicao] as $pos => $value) /* $data[posicao] -> 0 ou 1 -> [ctg] ou [num_likes] */
        {
            $centroids[$centroid][$pos] += ($value/$counts[$centroid]); /* Para cada centroide, é feita uma divisão do número de y para os seus
                                                                         * dados correspondentes, assim achando novas posições para as centroides
                                                                         * $centoids[0 a 5][0 ou 1] += num_likes/qtd de itens na centroide n */
        }
    }
    
    return $centroids;
}

function formatResults($mapping, $data) /* Formata o resultado dentro do array */
{
    $result_kmeans  = array();
    $inside_data = array();
    foreach($mapping as $documentID => $centroidID) 
    {
        $inside_data = $data[$documentID];
        $result_kmeans[$centroidID][] = $inside_data;
    }
    return $result_kmeans;
}