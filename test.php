<?php
    // 1. VARIABLE
    $nom = 'Jean';
    $prenom = 'Félix';
    $age = 15;

    // 2. CONDITION
    if ($age<=10) {
        echo "Trop jeune";
    } else {
        echo "Admis";
    }

    // 3. TABLEAU
    $eleves = ["Jean", "Marie", "Paul"];

    // 4. BOUCLE
    foreach ($eleves as $eleve) {
        echo "<p>" . $eleve . "</p>";
    }

    // 5. CALCULER UNE MOYENNE
    function calculerMoyenne($notes) {
        $total = 0;
        foreach ($notes as $note) {
            $total += $note;
        }
        return $total / count($notes);
    }
    $notes = [12,15,9,17];
    $moyenne = calculerMoyenne($notes);
    echo "Moyenne : " . $moyenne;
?>