  <!-- ══ SECTION 1 : IDENTITÉ (obligatoire) ══ -->
  <fieldset><legend>👤 Identité — champs obligatoires</legend>

    <!-- Nom -->
    <div class="form-group">
      <label>Nom *</label>
      <input type="text" name="nom" placeholder="ex: RAKOTO"
             value="<?= htmlspecialchars($old['nom'] ?? '') ?>">
      <?php if (isset($errors['nom'])): ?>
        <span class="error"><?= $errors['nom'] ?></span>
      <?php endif; ?>
    </div>

    <!-- Prénom (même pattern que nom) -->
    <div class="form-group">
      <label>Prénom *</label>
      <input type="text" name="prenom"
             value="<?= htmlspecialchars($old['prenom'] ?? '') ?>">
    </div>

    <!-- Matricule -->
    <div class="form-group">
      <label>Matricule * <small>(format suggéré : 6A-001-2026)</small></label>
      <input type="text" name="matricule"
             value="<?= htmlspecialchars($old['matricule'] ?? '') ?>">
      <?php if (isset($errors['matricule'])): ?>
        <span class="error"><?= $errors['matricule'] ?></span>
      <?php endif; ?>
    </div>

    <!-- Sexe -->
    <div class="form-group">
      <label>Sexe *</label>
      <select name="sexe">
        <option value="">-- Choisir --</option>
        <!-- Ternaire : si $old['sexe'] === 'M' → ajouter 'selected' -->
        <option value="M" <?= ($old['sexe']??'')==='M' ? 'selected':'' ?>>Masculin</option>
        <option value="F" <?= ($old['sexe']??'')==='F' ? 'selected':'' ?>>Féminin</option>
      </select>
    </div>

    <!-- Date de naissance : choix complète OU année seule -->
    <div class="form-group">
      <label>Date de naissance *</label>
      <label><input type="radio" name="date_type" value="complete"
        <?= ($old['date_type']??'complete')==='complete' ? 'checked':'' ?>
        onchange="toggleDate(this.value)"> Date complète</label>
      <label><input type="radio" name="date_type" value="annee"
        <?= ($old['date_type']??'')==='annee' ? 'checked':'' ?>
        onchange="toggleDate(this.value)"> Année seulement</label>

      <div id="div_complete">
        <input type="date" name="date_naissance"
               value="<?= $old['date_naissance'] ?? '' ?>">
      </div>
      <div id="div_annee" style="display:none">
        <input type="number" name="annee_naissance"
               min="1990" max="<?= date('Y') ?>" placeholder="ex: 2010"
               value="<?= $old['annee_naissance'] ?? '' ?>">
      </div>
      <?php if (isset($errors['date'])): ?>
        <span class="error"><?= $errors['date'] ?></span>
      <?php endif; ?>
    </div>

    <!-- Adresse -->
    <div class="form-group">
      <label>Adresse *</label>
      <textarea name="adresse" rows="2"><?= htmlspecialchars($old['adresse']??'') ?></textarea>
    </div>
  </fieldset>

  <!-- ══ SECTION 2 : SCOLARITÉ ══ -->
  <fieldset><legend>🏫 Scolarité</legend>
    <div class="form-group">
      <label>Classe *</label>
      <select name="classe_id">
        <option value="">-- Choisir --</option>
        <?php foreach ($classes as $c): ?>
          <option value="<?=$c['id']?>"
            <?= ($old['classe_id']??0)==$c['id'] ? 'selected':'' ?>>
            <?= htmlspecialchars($c['nom']) ?> (<?=$c['annee_scolaire']?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <!-- N° classe : calculé auto, affiché seulement -->
    <p><small>Le numéro alphabétique est calculé automatiquement après l'ajout.</small></p>
  </fieldset>

  <!-- ══ SECTION 3 : FAMILLE (facultatif) ══ -->
  <fieldset><legend>👨‍👩‍👧 Famille <small>(tous facultatifs)</small></legend>
    <!-- Même pattern pour chaque champ facultatif -->
    <?php
    $fac = [
        'nom_pere'       => 'Nom du père',
        'nom_mere'       => 'Nom de la mère',
        'contact_pere'   => 'Contact père',
        'contact_mere'   => 'Contact mère',
        'contact_tuteur' => 'Contact tuteur',
        'email_famille'  => 'Email famille',
    ];
    foreach ($fac as $name => $label): ?>
      <div class="form-group">
        <label><?= $label ?></label>
        <input type="text" name="<?=$name?>"
               value="<?= htmlspecialchars($old[$name]??'') ?>">
      </div>
    <?php endforeach; ?>
  </fieldset>