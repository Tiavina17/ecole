<?php
    $page_title = "Liste des élèves";       // ① Définir le titre AVANT l'include
    require '../config.php';                // ② Connexion PDO
    require '../includes/header.php';       // ③ Ouvre : html, head, body, sidebar, main
    require '../includes/auth.php';
    requiereConnexion();

// JOIN + COUNT : pour chaque classe, compter ses élèves
$classes = $pdo->query("
    SELECT c.*,
           COUNT(e.id) AS nb_eleves,
           SUM(e.sexe='M') AS nb_garcons,
           SUM(e.sexe='F') AS nb_filles
    FROM   classes c
    LEFT JOIN eleves e ON e.classe_id = c.id
    GROUP BY c.id
    ORDER BY c.annee_scolaire DESC, c.niveau, c.nom
")->fetchAll();
// LEFT JOIN (au lieu de JOIN) : garde les classes MÊME sans élèves
// GROUP BY c.id : regroupe les résultats par classe pour que COUNT fonctionne
// SUM(e.sexe='M') : compte les 1 (vrai) où sexe='M' → nombre de garçons
?>

<!-- Dans le tableau -->
<?php foreach($classes as $c): ?>
<tr>
  <td><strong><?=htmlspecialchars($c['nom'])?></strong></td>
  <td><?=$c['annee_scolaire']?></td>
  <td><?=$c['nb_eleves']?> <small style="color:var(--muted)">
    (👦<?=$c['nb_garcons']?> 👧<?=$c['nb_filles']?>)
  </small></td>
  <td>
    <!-- Lien pour voir les élèves de cette classe -->
    <a href="eleves.php?id=<?=$c['id']?>" class="btn btn-primary btn-sm">👁️ Élèves</a>
    <a href="modifier.php?id=<?=$c['id']?>" class="btn btn-edit btn-sm">✏️</a>
  </td>
</tr>
<?php endforeach; ?>
<!-- Ici : le contenu spécifique à cette page -->

<?php require '../includes/footer.php'; ?>  