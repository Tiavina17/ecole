<!-- Formulaire partagé pour ajouter et modifier -->
<div class="form-grid">

  <div class="form-group">
    <label>Nom de la classe *</label>
    <input type="text" name="nom" placeholder="ex: 6ème A"
           value="<?=htmlspecialchars($old['nom']??'')?>">
    <?php if(isset($errors['nom'])): ?><span class="error"><?=$errors['nom']?></span><?php endif; ?>
  </div>

  <div class="form-group">
    <label>Niveau *</label>
    <select name="niveau">
      <option value="">-- Choisir --</option>
      <?php foreach(['6ème','5ème','4ème','3ème','2nde','1ère','Terminale'] as $n): ?>
        <option value="<?=$n?>" <?=($old['niveau']??'')==$n?'selected':''?>><?=$n?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="form-group">
    <label>Année scolaire *</label>
    <input type="text" name="annee_scolaire" placeholder="2025-2026"
           value="<?=htmlspecialchars($old['annee_scolaire']??ANNEE_SCOLAIRE)?>">
  </div>
</div>