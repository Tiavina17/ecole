<?php

    // session_start() DOIT être en tout premier, avant tout output HTML
    // C'est PHP qui crée/reprend la session de l'utilisateur
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $host = 'localhost';
    $db = 'ecole_db';
    $user = 'root';
    $pass = "";
    $charset = "utf8mb4"; // supporte les accents et les émojis

    // DNS = Data Source Name (adresse de la BD)
    $dns = "mysql:host=$host;dbname=$db;charset=$charset";

    // Options de sécurité recommandées
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dns, $user, $pass, $options);
    } catch (\PDOException $e) {
        // On n'affiche pas l'erreur à l'utilisateur 
        error_log($e->getMessage());
        die("Connexion impossible");
    }

    // Constante : URL de base du site (ne change jamais)
    // define('NOM', valeur) → accessible partout avec NOM
    define('SITE_URL', 'http://localhost/sekoly/novice');
    define('ANNEE_SCOLAIRE', '2026-2027'); // utilisée partout
    
?>