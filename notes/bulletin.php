-- ALGORITHME : Moyenne pondérée par coefficient
-- Formule : SUM(note × coeff) / SUM(coeff)
-- Ex: Maths(14, coeff 3) + Français(12, coeff 3) + PC(10, coeff 2)
--   = (14×3 + 12×3 + 10×2) / (3+3+2) = (42+36+20)/8 = 12.25

SELECT
    e.nom, e.prenom, e.numero_classe,
    ROUND(SUM(n.note * m.coefficient) / SUM(m.coefficient), 2) AS moyenne_generale,
    GROUP_CONCAT(
        m.nom, ': ', ROUND(AVG(n.note),2)
        ORDER BY m.nom SEPARATOR ' | '
    ) AS detail_matieres
FROM   eleves e
JOIN   notes    n ON n.eleve_id   = e.id
JOIN   matieres m ON m.id         = n.matiere_id
WHERE  e.classe_id = 1 AND n.trimestre = 1
GROUP BY e.id
ORDER BY moyenne_generale DESC;  -- classement automatique : le premier = meilleur

<?php
// Après la requête, les élèves sont triés par moyenne DESC
// On numérote simplement avec le compteur de boucle
$rang = 1;
foreach ($resultats as $r) {
    echo $rang++  . "er — " . $r['nom'] . " : " . $r['moyenne_generale'] . "/20";
}
// Mention selon moyenne
function mention($moy): string {
    return match(true) {
        $moy >= 16 => 'Très Bien',
        $moy >= 14 => 'Bien',
        $moy >= 12 => 'Assez Bien',
        $moy >= 10 => 'Passable',
        default    => 'Insuffisant'
    };
    // match() = switch() moderne de PHP 8, plus propre
}
?>