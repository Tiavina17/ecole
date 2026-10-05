-- Taux de présence par élève sur un mois
SELECT
    e.nom, e.prenom,
    COUNT(*) AS jours_total,
    SUM(p.present) AS jours_present,
    SUM(1 - p.present) AS nb_absences,
    SUM(p.present = 0 AND p.justifie = 0) AS absences_injustifiees,
    ROUND(SUM(p.present) / COUNT(*) * 100, 1) AS taux_presence
FROM   presences p
JOIN   eleves e ON e.id = p.eleve_id
WHERE  p.classe_id = 1
  AND  p.date_cours BETWEEN '2025-10-01' AND '2025-10-31'
GROUP BY e.id
ORDER BY nb_absences DESC; -- les plus absents en premier