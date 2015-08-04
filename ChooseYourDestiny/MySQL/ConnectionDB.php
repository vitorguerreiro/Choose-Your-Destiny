<?php
/* 
 * Conectando com localhost na porta 3306 
 */

    $link = mysqli_connect('127.0.0.1:3306', 'root', 'root');
    if(!$link)
    {
        die('Não foi possível conectar: '.mysql_error($link));
    }

    $database = mysqli_select_db($link, 'ChooseYourDestiny');
    if(!$database)
    {
        die('Banco não encontrado.'.mysqli_error());
    }
    
    mysqli_query($link,"SET NAMES 'utf8'");
    mysqli_query($link,'SET character_set_connection=utf8');
    mysqli_query($link,'SET character_set_client=utf8');
    mysqli_query($link,'SET character_set_results=utf8');
    
    return $link;
    