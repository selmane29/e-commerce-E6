<?php 
session_start();

//inclure le fichier de connexion à la base de données
include 'bdd.php';

try{
    //connexion à la base de données
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf-8, $dbpassword");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_exception);
}