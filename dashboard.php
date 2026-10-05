<?php
$page_title = "Tableau de bord";
require 'config.php'; require 'includes/auth.php';
requiereConnexion();

// ─── Stats communes à tous les rôles ─────────────────────────────
$nb_eleves     = $pdo->query("SELECT COUNT(*) FROM eleves")->fetchColumn();
$nb_classes    = $pdo->query("SELECT COUNT(*) FROM classes WHERE annee_scolaire='".ANNEE_SCOLAIRE."'")->fetchColumn();
$nb_enseignants= $pdo->query("SELECT COUNT(*) FROM enseignants WHERE statut='actif'")->fetchColumn();

// ─── Stats directeur seulement ────────────────────────────────────
if (estDirecteur()) {
    // Montant total payé ce mois / montant théorique dû
    $mois = date('Y-m'); // ex: "2025-10"
    $paye_mois = $pdo->prepare("
        SELECT COALESCE(SUM(montant), 0)
        FROM paiements WHERE mois_concerne = :m
    ");
    $paye_mois->execute([':m' => $mois]);
    $total_paye = $paye_mois->fetchColumn();
    // COALESCE(expr, 0) : retourne 0 si SUM est NULL (aucun paiement)

    // Élèves n'ayant PAS payé ce mois (sous-requête NOT IN)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM eleves
        WHERE id NOT IN (
            SELECT eleve_id FROM paiements WHERE mois_concerne = :m
        )
    ");
    $stmt->execute([':m' => $mois]);
    $nb_impayes = $stmt->fetchColumn();

    // 5 derniers paiements (pour le fil d'activité)
    $derniers_paiements = $pdo->query("
        SELECT p.*, e.nom, e.prenom, c.nom AS classe
        FROM   paiements p
        JOIN   eleves  e ON e.id = p.eleve_id
        JOIN   classes c ON c.id = e.classe_id
        ORDER BY p.created_at DESC LIMIT 5
    ")->fetchAll();
}

// ─── Stats enseignant : ses classes + ses élèves ──────────────────
if (estEnseignant()) {
    // Récupérer l'ID enseignant lié à cet utilisateur
    $stmt = $pdo->prepare("SELECT id FROM enseignants WHERE utilisateur_id=:uid");
    $stmt->execute([':uid' => $_SESSION['user_id']]);
    $ens_id = $stmt->fetchColumn();

    // Ses classes avec le nombre d'élèves
    $mes_classes = $pdo->prepare("
        SELECT c.*, m.nom AS matiere, COUNT(e.id) AS nb_eleves
        FROM   affectations a
        JOIN   classes  c ON c.id = a.classe_id
        JOIN   matieres m ON m.id = a.matiere_id
        LEFT JOIN eleves e ON e.classe_id = c.id
        WHERE  a.enseignant_id = :eid AND a.annee_scolaire = :a
        GROUP BY c.id, m.id
        ORDER BY c.niveau, c.nom
    ");
    $mes_classes->execute([':eid'=>$ens_id, ':a'=>ANNEE_SCOLAIRE]);
    $mes_classes = $mes_classes->fetchAll();
}

require 'includes/header.php';
?>

<!-- Cartes stats (tous les rôles) -->
<div class="stats-grid">
  <div class="stat-card"><div class="stat-num"><?=$nb_eleves?></div><div class="stat-label">Élèves</div></div>
  <div class="stat-card"><div class="stat-num"><?=$nb_classes?></div><div class="stat-label">Classes</div></div>
  <div class="stat-card"><div class="stat-num"><?=$nb_enseignants?></div><div class="stat-label">Enseignants</div></div>
</div>

<!-- Bloc directeur uniquement -->
<?php if (estDirecteur()): ?>
  <div class="card">
    <h3>💰 Écolage — <?= date('F Y') ?></h3>
    <p>Total perçu : <strong><?= number_format($total_paye, 0, ',', ' ') ?> Ar</strong></p>
    <!-- number_format(1234567, 0, ',', ' ') → "1 234 567" -->
    <p style="color:var(--danger)"><?=$nb_impayes?> élève(s) n'ont pas encore payé</p>
  </div>
<?php endif; ?>

<!-- Bloc enseignant : ses classes -->
<?php if (estEnseignant()): ?>
  <h2>📚 Mes classes</h2>
  <?php foreach ($mes_classes as $cl): ?>
    <div class="card">
      <strong><?=htmlspecialchars($cl['nom'])?></strong>
      — <?=htmlspecialchars($cl['matiere'])?>
      (<?=$cl['nb_eleves']?> élèves)
    </div>
  <?php endforeach; ?>
<?php endif; ?>