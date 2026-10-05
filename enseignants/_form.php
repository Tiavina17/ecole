<!-- Champs spécifiques aux enseignants (en plus de nom, prenom, sexe, adresse) -->

<div class="form-group">
  <label>Matricule *</label>
  <input type="text" name="matricule" placeholder="ENS-2025-001"
         value="<?=htmlspecialchars($old['matricule']??'')?>">
</div>

<div class="form-group">
  <label>Téléphone</label>
  <input type="tel" name="telephone" placeholder="+261 34 00 000 00"
         value="<?=htmlspecialchars($old['telephone']??'')?>">
</div>

<div class="form-group">
  <label>Diplôme</label>
  <input type="text" name="diplome" placeholder="ex: Licence Mathématiques"
         value="<?=htmlspecialchars($old['diplome']??'')?>">
</div>

<div class="form-group">
  <label>Date d'embauche *</label>
  <input type="date" name="date_embauche"
         value="<?=$old['date_embauche']??''?>">
</div>

<!-- Salaire : visible seulement pour le directeur -->
<?php if (estDirecteur()): ?>
<div class="form-group">
  <label>Salaire de base (Ar)</label>
  <input type="number" name="salaire_base" step="0.01" placeholder="500000"
         value="<?=$old['salaire_base']??''?>">
</div>
<?php endif; ?>

<div class="form-group">
  <label>Statut</label>
  <select name="statut">
    <?php foreach(['actif'=>'✅ Actif','conge'=>'⏸️ En congé','retraite'=>'🔴 Retraité'] as $v=>$l): ?>
      <option value="<?=$v?>" <?=($old['statut']??'actif')==$v?'selected':''?>><?=$l?></option>
    <?php endforeach; ?>
  </select>
</div>