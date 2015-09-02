<?php

/* 
 * Arquivo que inicializa as centroides verificando primeiramente o range maximo e minimo do eixo x e y 
 * para a funcao randomica que determinará a posicao das centroides
 */

function inicializa_centroids(array $data, $k) {
    
    $dimensions = count($data[0]);
    $centroids = array();
    $dimmax = array();
    $dimmin = array();

    foreach($data as $document) {
        /* Achar um máximo e um mínimo em que a suposição das centroides possam estar */
        foreach($document as $dimension => $val) {
                /* Se variável max não existe ou se valor 'x' do primeiro array dentro de data é maior que max */
                if(!isset($dimmax[$dimension]) || $val > $dimmax[$dimension]) {
                        $dimmax[$dimension] = $val;
                }
                /* Se variável min não existe ou se valor 'x' do primeiro array dentro de data é menor que max */
                if(!isset($dimmin[$dimension]) || $val < $dimmin[$dimension]) {
                        $dimmin[$dimension] = $val;
                }
        }
    }
    for($i = 0; $i < $k; $i++) {
            $centroids[$i] = initialiseCentroid($dimensions, $dimmax, $dimmin);
    }
    return $centroids;
}

function initialiseCentroid($dimensions, $dimmax, $dimmin) {
    $centroid = array();
    for($j = 0; $j < $dimensions; $j++) {
        if($j==0){
            $centroid[$j] = (rand($dimmin[$j], $dimmax[$j]));
        }  else {
            $centroid[$j] = (rand($dimmin[$j], $dimmax[$j]));
        }
    }
    return $centroid;
}