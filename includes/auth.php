<?php
// Ce fichier contient des fonctions de protection
// On l'inclut en haut de chaque page protégée

// Vérifie que l'utilisateur est connecté
function requiereConnexion() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

// Vérifie qu'il a le bon rôle
// $roles peut être une string ou un tableau
// Ex : requiereRole('directeur') ou requiereRole(['directeur','secretaire'])
function requiereRole($roles) {
    requiereConnexion();
    $roles = (array)$roles; // convertit string en tableau si besoin
    if (!in_array($_SESSION['user_role'], $roles)) {
        http_response_code(403); // code HTTP "Accès interdit"
        die('<h2>❌ Accès refusé</h2><p><a href="'.SITE_URL.'">Retour</a></p>');
    }
}

// Fonctions pratiques pour les conditions dans le HTML
function estDirecteur(): bool {
    return ($_SESSION['user_role'] ?? '') === 'directeur';
}
function estEnseignant(): bool {
    return ($_SESSION['user_role'] ?? '') === 'enseignant';
}