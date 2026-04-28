<?php
// modules/prospection/views/contacts/import.php
$pageTitle = 'Importer des contacts CSV';
$flash     = Session::getFlash();
?>
<div class="hub-page">
<header class="hub-hero">
    <div class="hub-hero-badge"><i class="fas fa-file-import"></i> Prospection</div>
    <h1>Import de contacts CSV</h1>
    <p>Chargez une liste de contacts depuis un fichier CSV et mappez les colonnes avec les champs du CRM.</p>
</header>
<div class="hub-narrative">
    <article class="hub-narrative-card hub-narrative-card--explanation">
        <h3><i class="fas fa-file-csv" style="color:#3b82f6"></i> Format CSV</h3>
        <p>Le fichier doit contenir au minimum une colonne email. Les colonnes nom, prénom et téléphone sont optionnelles.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--resultat">
        <h3><i class="fas fa-check-circle" style="color:#10b981"></i> Après import</h3>
        <p>Les contacts apparaissent dans votre liste de prospection et peuvent immédiatement rejoindre une campagne email.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--action">
        <h3><i class="fas fa-triangle-exclamation" style="color:#ef4444"></i> Avant d'importer</h3>
        <p>Vérifiez que vos contacts ont consenti à recevoir des emails — le RGPD s'applique à toute prospection commerciale.</p>
    </article>
</div>
</div><!-- /.hub-page -->

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible mb-3" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="?module=prospection&action=contacts" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
    <div>
        <h1 class="h3 mb-0 fw-bold"><i class="fas fa-file-import text-success me-2"></i>Import CSV</h1>
        <p class="text-muted mb-0 small">Chargez une liste de contacts depuis un fichier CSV.</p>
    </div>
</div>

<div class="row g-4 justify-content-center">

    <!-- Upload -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="fw-semibold"><i class="fas fa-upload me-2 text-success"></i>Charger le fichier</div>
            </div>
            <div class="card-body">

                <form method="POST" action="?module=prospection" enctype="multipart/form-data" novalidate id="import-form">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="contact_import_csv">

                    <!-- Drop zone -->
                    <div id="drop-zone" class="border border-2 border-dashed rounded-3 text-center p-5 mb-4 cursor-pointer"
                         style="border-color:#cbd5e1!important;transition:border-color .2s,background .2s;">
                        <i class="fas fa-cloud-arrow-up fa-2x text-muted mb-3" id="drop-icon"></i>
                        <div class="fw-semibold text-muted mb-1" id="drop-label">Glissez votre fichier CSV ici</div>
                        <div class="text-muted small mb-3">ou cliquez pour sélectionner</div>
                        <input type="file" name="csv_file" id="csv-file" accept=".csv,text/csv" class="d-none" required>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('csv-file').click()">
                            <i class="fas fa-folder-open me-2"></i>Parcourir…
                        </button>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg" id="import-btn" disabled>
                            <i class="fas fa-file-import me-2"></i>Lancer l'import
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Aide + Modèle -->
    <div class="col-12 col-lg-5">

        <!-- Format attendu -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="fw-semibold"><i class="fas fa-circle-info me-2 text-primary"></i>Format attendu</div>
            </div>
            <div class="card-body pb-2">
                <p class="text-muted small mb-3">Votre fichier CSV doit contenir une ligne d'en-tête avec les colonnes suivantes :</p>
                <div class="table-responsive">
                    <table class="table table-sm table-borderless" style="font-size:.8rem;">
                        <thead class="bg-light">
                            <tr>
                                <th>Colonne</th>
                                <th>Obligatoire</th>
                                <th>Exemple</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ([
                                ['email',      true,  'jean@ex.fr'],
                                ['first_name', false, 'Jean'],
                                ['last_name',  false, 'Dupont'],
                                ['phone',      false, '0600000000'],
                                ['company',    false, 'SARL Truc'],
                                ['city',       false, 'Aix-en-Provence'],
                                ['notes',      false, 'Vu au salon'],
                            ] as [$col, $req, $ex]): ?>
                            <tr>
                                <td><code><?= $col ?></code></td>
                                <td><?= $req ? '<span class="text-danger fw-bold">Oui</span>' : '<span class="text-muted">Non</span>' ?></td>
                                <td class="text-muted"><?= $ex ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info py-2 px-3 small mb-0">
                    <i class="fas fa-circle-info me-2"></i>
                    Séparateur <strong>virgule</strong> ou <strong>point-virgule</strong> accepté.
                    Les doublons email sont ignorés automatiquement.
                </div>
            </div>
        </div>

        <!-- Télécharger modèle -->
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="fw-semibold small mb-2"><i class="fas fa-download me-2 text-muted"></i>Modèle CSV</div>
                <p class="text-muted small mb-3">Téléchargez le modèle pour démarrer rapidement.</p>
                <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="downloadTemplate()">
                    <i class="fas fa-file-csv me-2"></i>Télécharger le modèle
                </button>
            </div>
        </div>

    </div>
</div>

<script>
(function() {
    var fileInput = document.getElementById('csv-file');
    var dropZone  = document.getElementById('drop-zone');
    var dropLabel = document.getElementById('drop-label');
    var dropIcon  = document.getElementById('drop-icon');
    var importBtn = document.getElementById('import-btn');

    function setFile(file) {
        if (!file) return;
        dropLabel.textContent = file.name;
        dropIcon.className = 'fas fa-file-csv fa-2x text-success mb-3';
        dropZone.style.borderColor = '#22c55e';
        dropZone.style.background = '#f0fdf4';
        importBtn.disabled = false;
    }

    fileInput.addEventListener('change', function() {
        setFile(this.files[0]);
    });

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.style.borderColor = '#3b82f6';
        dropZone.style.background = '#eff6ff';
    });

    dropZone.addEventListener('dragleave', function() {
        dropZone.style.borderColor = '#cbd5e1';
        dropZone.style.background = '';
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        var file = e.dataTransfer.files[0];
        if (file) {
            var dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            setFile(file);
        }
    });
})();

function downloadTemplate() {
    var content = 'email,first_name,last_name,phone,company,city,notes\njean.dupont@exemple.fr,Jean,Dupont,0600000000,SARL Exemple,Aix-en-Provence,Vu au salon\n';
    var blob = new Blob([content], {type: 'text/csv;charset=utf-8;'});
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href   = url;
    a.download = 'modele_contacts_prospection.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>
