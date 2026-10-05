<?php
requiereRole('directeur'); // seul le directeur voit les salaires

// Calcul du salaire net (exemple simplifié)
$ens_id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM enseignants WHERE id=:id");
$stmt->execute([':id'=>$ens_id]);
$ens = $stmt->fetch();

// Algorithme de calcul de salaire
$salaire_base   = $ens['salaire_base'];
$nb_classes     = 4; // compté depuis affectations
$prime_classes  = $nb_classes * 10000;   // 10 000 Ar par classe
$retenue_cnaps  = $salaire_base * 0.01;  // 1% CNAPS
$retenue_irsa   = $salaire_base * 0.03;  // 3% IRSA simplifié
$salaire_brut   = $salaire_base + $prime_classes;
$total_retenues = $retenue_cnaps + $retenue_irsa;
$salaire_net    = $salaire_brut - $total_retenues;

// Enregistrer le paiement du salaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payer'])) {
    // INSERT dans une table salaires_payes (à créer)
}
?>