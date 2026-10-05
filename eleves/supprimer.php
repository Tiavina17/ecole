<?php
require '../config.php';
require '../includes/auth.php';

  // Options selon la page :
  // requiereConnexion();                           // tous les connectés
  // requiereRole('directeur');                  // directeur seul
   requiereRole(['directeur','secretaire']);    // directeur OU secrétaire

// ─── SÉCURITÉ 1 : Vérifier l'ID ──────────────────────────────────
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    // header() envoie un en-tête HTTP au navigateur
    // Location: → redirection immédiate vers une autre page
    header('Location: liste.php');
    exit; // IMPORTANT : arrêter l'exécution après header()
}

// ─── SÉCURITÉ 2 : Vérifier que l'élève existe ────────────────────
$stmt = $pdo->prepare("SELECT * FROM eleves WHERE id = :id");
$stmt->execute([':id' => $id]);
$eleve = $stmt->fetch();

if (!$eleve) {
    header('Location: liste.php');
    exit;
}

// Mémoriser la classe AVANT de supprimer (pour recalculer les n°)
$classe_id = $eleve['classe_id'];
$nom_complet = $eleve['nom'] . ' ' . $eleve['prenom'];

// ─── SUPPRESSION ─────────────────────────────────────────────────
$del = $pdo->prepare("DELETE FROM eleves WHERE id = :id");
$del->execute([':id' => $id]);

// ─── Recalculer les numéros alphabétiques de la classe ───────────
$pdo->exec("SET @r = 0");
$pdo->prepare("UPDATE eleves SET numero_classe = (@r := @r + 1)
               WHERE classe_id = :cid ORDER BY nom, prenom")
    ->execute([':cid' => $classe_id]);

// ─── Rediriger vers liste.php avec le nom dans l'URL ─────────────
// urlencode() encode les espaces et accents pour les URL
// ex: "RAKOTO Jean" → "RAKOTO+Jean"
header('Location: liste.php?deleted=' . urlencode($nom_complet));
exit; // toujours exit après header()
?>