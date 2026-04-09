<?php
$pageTitle = 'Biens';
$pageDescription = 'Gérez votre portefeuille de biens immobiliers';

$allowedViews = ['index', 'photos'];
$view = $_GET['view'] ?? 'index';
if (!in_array($view, $allowedViews, true)) {
    $view = 'index';
}

function biensFetchForMedia(): array
{
    $stmt = db()->query('SELECT id, titre, ville, reference, photo_principale FROM biens ORDER BY created_at DESC LIMIT 200');
    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

function biensFetchPhotosByBien(array $biens): array
{
    if ($biens === []) {
        return [];
    }

    $ids = array_map(static fn(array $b): int => (int) $b['id'], $biens);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT id, bien_id, chemin, alt, position FROM bien_photos WHERE bien_id IN ($placeholders) ORDER BY position ASC, id ASC");
    $stmt->execute($ids);

    $map = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $photo) {
        $map[(int) $photo['bien_id']][] = $photo;
    }

    return $map;
}

function renderBiensHubCards(): void
{
    ?>
    <div class="page-header">
        <h1><i class="fas fa-house page-icon"></i> HUB <span class="page-title-accent">Biens</span></h1>
        <p>Gérez votre portefeuille de biens immobiliers</p>
    </div>

    <div class="cards-container">

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-list"></i></div>
                <h3 class="card-title">Catalogue des biens</h3>
            </div>
            <p class="card-description">Consultez et gérez tous vos biens actifs, en option et vendus.</p>
            <div class="card-tags"><span class="tag">Actifs</span><span class="tag">En option</span><span class="tag">Vendus</span></div>
            <a href="/admin/biens/catalogue" class="card-action"><i class="fas fa-arrow-right"></i> Consulter</a>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-plus-circle"></i></div>
                <h3 class="card-title">Ajouter un bien</h3>
            </div>
            <p class="card-description">Créez une nouvelle fiche bien avec photos, description et caractéristiques.</p>
            <div class="card-tags"><span class="tag">Nouveau mandat</span></div>
            <a href="/admin/biens/nouveau" class="card-action"><i class="fas fa-plus"></i> Créer</a>
        </div>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-images"></i></div>
                <h3 class="card-title">Médias & photos</h3>
            </div>
            <p class="card-description">Gérez les photos de vos biens : upload multiple, tri et suppression.</p>
            <div class="card-tags"><span class="tag">Photos</span><span class="tag">Tri</span><span class="tag">Suppression</span></div>
            <a href="/admin?module=biens&amp;view=photos" class="card-action"><i class="fas fa-image"></i> Gérer</a>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-tags"></i></div>
                <h3 class="card-title">Diffusion annonces</h3>
            </div>
            <p class="card-description">Publiez vos biens sur les portails immobiliers en un clic.</p>
            <div class="card-tags"><span class="tag">SeLoger</span><span class="tag">LeBonCoin</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

    </div>
    <?php
}

function renderBiensPhotosManager(): void
{
    $biens = biensFetchForMedia();
    $photosMap = biensFetchPhotosByBien($biens);
    $selectedId = isset($_GET['bien_id']) ? (int) $_GET['bien_id'] : ((int) ($biens[0]['id'] ?? 0));
    $selectedPhotos = $selectedId > 0 ? ($photosMap[$selectedId] ?? []) : [];

    ?>
    <style>
        .media-toolbar{display:flex;gap:12px;flex-wrap:wrap;align-items:end;margin-bottom:16px}
        .media-toolbar select,.media-toolbar input,.media-toolbar button{padding:10px;border-radius:8px;border:1px solid #d8dde6}
        .media-toolbar button{background:#f39c12;color:#fff;border:none;font-weight:600;cursor:pointer}
        .media-toolbar button.secondary{background:#2d70b3}
        .media-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-top:16px}
        .photo-card{background:#fff;border:1px solid #e8edf3;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.04)}
        .photo-card[draggable="true"]{cursor:move}
        .photo-card.dragging{opacity:.45}
        .photo-card img{width:100%;height:130px;object-fit:cover;display:block;background:#f6f7f9}
        .photo-meta{padding:10px;font-size:13px;display:flex;justify-content:space-between;align-items:center;gap:8px}
        .photo-meta button{background:#e74c3c;color:#fff;border:none;border-radius:6px;padding:6px 10px;cursor:pointer}
        .media-empty{padding:24px;background:#fff;border:1px dashed #cfd8e3;border-radius:10px;color:#728095;text-align:center}
        .media-status{font-size:13px;color:#4f5d73;margin-left:auto}
        .hub-back{display:inline-flex;gap:8px;align-items:center;margin-bottom:10px}
    </style>

    <a class="hub-back" href="/admin?module=biens"><i class="fas fa-arrow-left"></i> Retour au hub Biens</a>
    <div class="page-header">
        <h1><i class="fas fa-images page-icon"></i> Médias &amp; photos</h1>
        <p>Importez plusieurs photos, réorganisez l'ordre par glisser-déposer et supprimez les visuels inutiles.</p>
    </div>

    <div class="media-toolbar" id="mediaToolbar">
        <label>
            Bien
            <select id="bienSelect" name="bien_id">
                <?php foreach ($biens as $bien): ?>
                    <option value="<?= (int) $bien['id'] ?>" <?= (int) $bien['id'] === $selectedId ? 'selected' : '' ?>>
                        #<?= (int) $bien['id'] ?> — <?= e((string) $bien['titre']) ?><?= !empty($bien['ville']) ? ' (' . e((string) $bien['ville']) . ')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Ajouter des photos
            <input type="file" id="photosInput" multiple accept=".jpg,.jpeg,.png,.webp">
        </label>

        <button type="button" id="uploadBtn"><i class="fas fa-upload"></i> Uploader</button>
        <button type="button" class="secondary" id="saveOrderBtn"><i class="fas fa-sort"></i> Enregistrer l'ordre</button>

        <span class="media-status" id="mediaStatus"></span>
    </div>

    <div id="photoGrid" class="media-grid" data-photos='<?= e((string) json_encode($selectedPhotos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'></div>

    <script>
    (function () {
        const csrfToken = <?= json_encode(csrfToken(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const select = document.getElementById('bienSelect');
        const input = document.getElementById('photosInput');
        const uploadBtn = document.getElementById('uploadBtn');
        const saveOrderBtn = document.getElementById('saveOrderBtn');
        const grid = document.getElementById('photoGrid');
        const statusEl = document.getElementById('mediaStatus');

        let photos = JSON.parse(grid.dataset.photos || '[]');
        let dragId = null;

        function setStatus(text, isError) {
            statusEl.textContent = text || '';
            statusEl.style.color = isError ? '#b42318' : '#4f5d73';
        }

        function render() {
            grid.innerHTML = '';
            if (!photos.length) {
                const empty = document.createElement('div');
                empty.className = 'media-empty';
                empty.textContent = 'Aucune photo pour ce bien.';
                grid.appendChild(empty);
                return;
            }

            photos.forEach((photo) => {
                const card = document.createElement('article');
                card.className = 'photo-card';
                card.draggable = true;
                card.dataset.id = String(photo.id);
                card.innerHTML = `
                    <img src="${photo.chemin}" alt="${photo.alt || ''}">
                    <div class="photo-meta">
                        <span>#${photo.position + 1}</span>
                        <button type="button" data-delete="${photo.id}">Supprimer</button>
                    </div>
                `;

                card.addEventListener('dragstart', () => {
                    dragId = photo.id;
                    card.classList.add('dragging');
                });
                card.addEventListener('dragend', () => {
                    dragId = null;
                    card.classList.remove('dragging');
                });
                card.addEventListener('dragover', (event) => event.preventDefault());
                card.addEventListener('drop', (event) => {
                    event.preventDefault();
                    const targetId = Number(card.dataset.id);
                    if (!dragId || dragId === targetId) {
                        return;
                    }
                    const from = photos.findIndex(p => Number(p.id) === Number(dragId));
                    const to = photos.findIndex(p => Number(p.id) === targetId);
                    if (from < 0 || to < 0) {
                        return;
                    }
                    const moved = photos.splice(from, 1)[0];
                    photos.splice(to, 0, moved);
                    photos = photos.map((p, index) => ({ ...p, position: index }));
                    render();
                });

                grid.appendChild(card);
            });
        }

        async function postData(formData) {
            const response = await fetch('/admin/api/biens/upload', {
                method: 'POST',
                body: formData,
            });

            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Erreur inattendue.');
            }

            return payload;
        }

        async function uploadPhotos() {
            if (!input.files.length) {
                setStatus('Sélectionnez au moins une image.', true);
                return;
            }

            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('action', 'upload');
            formData.append('bien_id', select.value);
            Array.from(input.files).forEach((file) => formData.append('photos[]', file));

            setStatus('Upload en cours...');
            try {
                const payload = await postData(formData);
                photos = payload.photos || [];
                input.value = '';
                render();
                setStatus(payload.message || 'Upload terminé.');
            } catch (error) {
                setStatus(error.message, true);
            }
        }

        async function deletePhoto(photoId) {
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('action', 'delete');
            formData.append('bien_id', select.value);
            formData.append('photo_id', String(photoId));

            try {
                const payload = await postData(formData);
                photos = payload.photos || [];
                render();
                setStatus(payload.message || 'Photo supprimée.');
            } catch (error) {
                setStatus(error.message, true);
            }
        }

        async function saveOrder() {
            if (!photos.length) {
                return;
            }

            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('action', 'reorder');
            formData.append('bien_id', select.value);
            photos.forEach((photo) => formData.append('photo_ids[]', String(photo.id)));

            setStatus('Enregistrement du tri...');
            try {
                const payload = await postData(formData);
                photos = payload.photos || [];
                render();
                setStatus(payload.message || 'Ordre sauvegardé.');
            } catch (error) {
                setStatus(error.message, true);
            }
        }

        select.addEventListener('change', () => {
            const url = new URL(window.location.href);
            url.searchParams.set('module', 'biens');
            url.searchParams.set('view', 'photos');
            url.searchParams.set('bien_id', select.value);
            window.location.href = url.toString();
        });

        uploadBtn.addEventListener('click', uploadPhotos);
        saveOrderBtn.addEventListener('click', saveOrder);
        grid.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-delete]');
            if (!button) {
                return;
            }
            const photoId = Number(button.getAttribute('data-delete'));
            if (photoId && window.confirm('Supprimer cette photo ?')) {
                deletePhoto(photoId);
            }
        });

        render();
    })();
    </script>
    <?php
}

function renderContent() {
    global $view;

    if ($view === 'photos') {
        renderBiensPhotosManager();
        return;
    }

    renderBiensHubCards();
}
