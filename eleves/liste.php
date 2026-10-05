<?php

    $page_title = "Liste des élèves";       //  Définir le titre AVANT l'include
    require '../config.php';                //  Connexion PDO
    require '../includes/auth.php';

  // Options selon la page :
  // requiereConnexion();                           // tous les connectés
  // requiereRole('directeur');                  // directeur seul
   requiereRole(['directeur','secretaire']);    // directeur OU secrétaire
   
    require '../includes/header.php';       //  Ouvre : html, head, body, sidebar, main

    // Message de suppression (vient de supprimer.php via l'URL)
// ex: liste.php?deleted=RAKOTO+Jean
$deleted = $_GET['deleted'] ?? '';

// Filtre classe : liste.php?classe_id=2
$filtre_classe = (int)($_GET['classe_id'] ?? 0);
// Recherche texte : liste.php?q=RAKOTO
$search = trim($_GET['q'] ?? '');

// Charger les classes pour le menu déroulant du filtre
$classes = $pdo->query("SELECT * FROM classes ORDER BY niveau, nom")->fetchAll();

// ─── Construction dynamique du WHERE ─────────────────────────────
// Algorithme : on construit la requête en fonction des filtres actifs
// On évite le SQL à la main (vulnérable) → on utilise un tableau
$where  = [];   // conditions WHERE (chaînes SQL)
$params = [];   // valeurs sécurisées pour PDO

if ($filtre_classe > 0) {
    $where[]  = "e.classe_id = :classe_id";
    $params[':classe_id'] = $filtre_classe;
}
if (!empty($search)) {
    // LIKE '%RAKOTO%' → cherche RAKOTO n'importe où dans la chaîne
    $where[]  = "(e.nom LIKE :q OR e.prenom LIKE :q OR e.matricule LIKE :q)";
    $params[':q'] = "%$search%";
}

// implode() : colle les éléments d'un tableau avec " AND "
// ex: ["a=:a","b=:b"] → "a=:a AND b=:b"
$sql_where = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// JOIN : lier eleves (alias "e") et classes (alias "c")
// ON e.classe_id = c.id → la condition de jointure
// c.nom AS classe_nom → renommer la colonne pour éviter conflit avec e.nom
$stmt = $pdo->prepare("
    SELECT e.*, c.nom AS classe_nom
    FROM   eleves  e
    JOIN   classes c ON e.classe_id = c.id
    $sql_where
    ORDER BY e.nom, e.prenom
");
$stmt->execute($params);
$eleves = $stmt->fetchAll();

// Statistiques globales (pour les cartes en haut)
$total   = $pdo->query("SELECT COUNT(*) FROM eleves")->fetchColumn();
$garcons = $pdo->query("SELECT COUNT(*) FROM eleves WHERE sexe='M'")->fetchColumn();
$filles  = $pdo->query("SELECT COUNT(*) FROM eleves WHERE sexe='F'")->fetchColumn();
// fetchColumn() retourne directement la première colonne (le COUNT) → plus court que fetch() ?>


<!-- Message si élève vient d'être supprimé -->
<?php if ($deleted): ?>
  <div class="alert alert-danger">
    🗑️ <?= htmlspecialchars($deleted) ?> a été supprimé.
  </div>
<?php endif; ?>

<!-- En-tête -->
<div class="page-header">
  <div>
    <h1 class="page-title">👨‍🎓 Élèves</h1>
    <p class="page-subtitle"><?= count($eleves) ?> résultat(s)</p>
  </div>
  <a href="ajouter.php" class="btn btn-primary">➕ Ajouter</a>
</div>

<!-- Cartes statistiques -->
<div class="stats-grid">
  <div class="stat-card"><div class="stat-num"><?=$total?></div><div class="stat-label">Total</div></div>
  <div class="stat-card"><div class="stat-num"><?=$garcons?></div><div class="stat-label">Garçons</div></div>
  <div class="stat-card"><div class="stat-num"><?=$filles?></div><div class="stat-label">Filles</div></div>
</div>

<!-- Barre de filtre + recherche -->
<form method="GET" class="filter-bar">
  <input type="text" name="q" placeholder="🔍 Nom, prénom, matricule..."
         value="<?= htmlspecialchars($search) ?>">

  <select name="classe_id" onchange="this.form.submit()">
    <!-- onchange="this.form.submit()" : soumet le formulaire automatiquement -->
    <!-- dès que l'utilisateur choisit une classe -->
    <option value="0">Toutes les classes</option>
    <?php foreach ($classes as $c): ?>
      <option value="<?=$c['id']?>"
        <?= $filtre_classe==$c['id'] ? 'selected':'' ?>>
        <?= htmlspecialchars($c['nom']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>

  <!-- Bouton réinitialiser : visible seulement si filtre actif -->
  <?php if ($filtre_classe || !empty($search)): ?>
    <a href="liste.php" class="btn btn-ghost btn-sm">✕ Effacer</a>
  <?php endif; ?>
</form>

<!-- Tableau des élèves -->
<div class="card">
  <div class="table-wrapper">
  <table>
    <thead><tr>
      <th>N°</th><th>Matricule</th><th>Nom & Prénom</th>
      <th>Naissance</th><th>Classe</th><th>Contact</th><th>Actions</th>
    </tr></thead>
    <tbody>

    <?php if (empty($eleves)): ?>
      <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">
        Aucun élève trouvé.
      </td></tr>
    <?php else: ?>
      <?php foreach ($eleves as $e): ?>
        <tr>
          <td><span class="badge-num"><?= $e['numero_classe'] ?? '—' ?></span></td>

          <td class="muted"><?= htmlspecialchars($e['matricule']) ?></td>

          <td>
            <strong><?= htmlspecialchars($e['nom']) ?></strong>
            <?= htmlspecialchars($e['prenom']) ?>
            <small style="color:var(--muted)">
              <?= $e['sexe']==='M' ? '👦':'👧' ?>
            </small>
          </td>

          <td class="muted">
            <?php
            // Afficher date complète OU année OU tiret
            if ($e['date_naissance']) {
              // strtotime() convertit "2010-03-15" en timestamp Unix
              // date('d/m/Y', ...) formate en "15/03/2010"
              echo date('d/m/Y', strtotime($e['date_naissance']));
            } elseif ($e['annee_naissance']) {
              echo "vers " . $e['annee_naissance'];
            } else {
              echo '—';
            }
            ?>
          </td>

          <td><span class="badge-classe"><?= htmlspecialchars($e['classe_nom']) ?></span></td>

          <td class="muted">
            <?php
            // Afficher le premier contact disponible
            // L'opérateur ?? enchaîné : essaie l'un après l'autre ?>
            <?= $e['contact_pere'] ?? $e['contact_mere'] ?? $e['contact_tuteur'] ?? '—' ?>
          </td>

          <td>
            <a href="modifier.php?id=<?=$e['id']?>" class="btn btn-edit btn-sm">✏️</a>

            <!-- confirm() : boîte JS qui demande confirmation avant de suivre le lien -->
            <a href="supprimer.php?id=<?=$e['id']?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('Supprimer <?= htmlspecialchars($e['nom']) ?> ?')">
              🗑️
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>

    </tbody>
  </table>
  </div>
</div>

<?php require '../includes/footer.php'; ?>