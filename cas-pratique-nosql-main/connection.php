<?php 
    require 'vendor/autoload.php';

    // connection a la base de donnee
    $client = new MongoDB\Client("mongodb://localhost:27017");

    // selection de la base de donnee
    $db=$client->restaurant;

    // selection de la collection
    $restaurants = $db->restaurants;
    
    // selection de la collection
    $favoris = $db->favoris;
?>