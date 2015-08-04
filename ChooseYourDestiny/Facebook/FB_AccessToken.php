<?php
session_start();

/* Coloca nas sessions, o access_token retornado depois do login no Facebook
 * e fb_status para informar se está conectado no Facebook ou não. */
$_SESSION['access_token'] = $_GET['access_token'];
$_SESSION['fb_status'] = $_GET['fb_status'];

$teste = $_SESSION['fb_status'];
