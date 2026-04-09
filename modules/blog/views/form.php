<?php
require_once __DIR__ . '/../../../config/database.php';
$website_id = 1;
$id = (int)($_GET['id'] ?? 0);
$a  = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM blog_articles WHERE id=? AND website_id=?");
    $stmt->execute([$id, $website_id]);
    $a = $stmt->fetch();
    if (!$a) { header('Location: ../accueil.php'); exit; }
}

$v = static fn(string $k, string $d = ''): string => htmlspecialchars((string)($a[$k] ?? $d), ENT_QUOTES, 'UTF-8');
$datePublication = '';
if (!empty($a['date_publication'])) {
    $ts = strtotime((string)$a['date_publication']);
    if ($ts !== false) {
        $datePublication = date('Y-m-d\\TH:i', $ts);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $id ? 'Éditer' : 'Créer' ?> un article</title>
<link rel="stylesheet" href="/modules/blog/assets/blog.css">
</head>
<body>
<div class="cms-wrap">
  <header class="cms-header">
    <a href="accueil.php" class="back">← Retour</a>
    <h1><?= $id ? 'Éditer l\'article' : 'Créer un article' ?></h1>
  </header>

  <form method="post" action="controllers/save.php" class="article-form" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="form-grid">
      <div class="col-main">
        <div class="form-group">
          <label for="titre-input">Titre *</label>
          <input type="text" id="titre-input" name="titre" value="<?= $v('titre') ?>" required>
        </div>

        <div class="form-group">
          <label for="slug-input">Slug</label>
          <input type="text" id="slug-input" name="slug" value="<?= $v('slug') ?>" placeholder="auto-genere-depuis-le-titre">
        </div>

        <div class="form-group">
          <label for="contenu-input">Contenu</label>
          <textarea id="contenu-input" name="contenu" rows="18" placeholder="Rédigez votre article ici..."><?= $v('contenu') ?></textarea>
          <small>Éditeur simple (textarea). Peut être remplacé plus tard par un éditeur riche.</small>
        </div>

        <div class="form-group">
          <label for="meta-desc-input">Meta description</label>
          <textarea id="meta-desc-input" name="meta_desc" maxlength="160" rows="3" placeholder="Description SEO de la page (160 caractères max)"><?= $v('meta_desc') ?></textarea>
          <div class="char-count" id="meta-count">0/160</div>
        </div>
      </div>

      <div class="col-side">
        <div class="side-box">
          <h3>Publication</h3>

          <div class="form-group">
            <label for="image-input">Image à la une</label>
            <input type="file" id="image-input" name="featured_image" accept="image/*">
            <small>Champ prêt pour l'upload. Le contrôleur actuel sauvegarde uniquement les données textuelles.</small>
          </div>

          <div class="form-group">
            <label for="statut-input">Statut</label>
            <select id="statut-input" name="statut">
              <?php $statut = $a['statut'] ?? 'brouillon'; ?>
              <option value="brouillon" <?= $statut === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
              <option value="publié" <?= $statut === 'publié' ? 'selected' : '' ?>>Publié</option>
            </select>
          </div>

          <div class="form-group">
            <label for="date-publication-input">Date de publication</label>
            <input type="datetime-local" id="date-publication-input" name="date_publication" value="<?= htmlspecialchars($datePublication, ENT_QUOTES, 'UTF-8') ?>">
          </div>
        </div>

        <button type="submit" class="btn-primary btn-full">💾 Enregistrer</button>
      </div>
    </div>
  </form>
</div>

<script>
(function () {
  const titleInput = document.getElementById('titre-input');
  const slugInput = document.getElementById('slug-input');
  const metaInput = document.getElementById('meta-desc-input');
  const metaCount = document.getElementById('meta-count');

  function slugify(value) {
    return value
      .toLowerCase()
      .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  if (titleInput && slugInput) {
    titleInput.addEventListener('input', function () {
      if (!slugInput.dataset.manual) {
        slugInput.value = slugify(titleInput.value);
      }
    });

    slugInput.addEventListener('input', function () {
      slugInput.dataset.manual = '1';
      slugInput.value = slugify(slugInput.value);
    });
  }

  if (metaInput && metaCount) {
    const updateCount = function () {
      metaCount.textContent = metaInput.value.length + '/160';
    };
    metaInput.addEventListener('input', updateCount);
    updateCount();
  }
})();
</script>
</body>
</html>
