-- La requête qui lie les 4 tables ensemble (triple JOIN)
SELECT
    ens.nom AS enseignant,
    m.nom   AS matiere,
    m.coefficient,
    c.nom   AS classe,
    c.niveau,
    a.annee_scolaire
FROM   affectations a
JOIN   enseignants ens ON a.enseignant_id = ens.id
JOIN   matieres    m   ON a.matiere_id    = m.id
JOIN   classes     c   ON a.classe_id     = c.id
WHERE  a.annee_scolaire = '2025-2026'
ORDER BY c.niveau, c.nom, m.nom;