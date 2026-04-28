<?php

declare(strict_types=1);

$pageTitle = 'Listings';
$pageDescription = 'Valider la publication des annonces';

/**
 * @param array<string, scalar> $flt
 * @return array{where: non-empty-array<string>, params: array<string, mixed>}
 */
function listings_biens_filters_to_sql(array $flt, bool $hasPublier): array
{
    $where = ['1=1'];
    $params = [];

    $q = isset($flt['flt_q']) ? trim((string) $flt['flt_q']) : '';
    if ($q !== '') {
        // PDO n’accepte pas le même jeton nommé deux fois dans une requête (HY093).
        $where[] = '(titre LIKE :lq1 OR ville LIKE :lq2 OR adresse LIKE :lq3)';
        $like = '%' . $q . '%';
        $params[':lq1'] = $like;
        $params[':lq2'] = $like;
        $params[':lq3'] = $like;
    }

    $src = isset($flt['flt_source']) ? (string) $flt['flt_source'] : '';
    if ($src === 'own') {
        $where[] = "(COALESCE(source, '') <> 'partage')";
    } elseif ($src === 'partage') {
        $where[] = "source = 'partage'";
    }

    $st = isset($flt['flt_statut']) ? (string) $flt['flt_statut'] : '';
    $allowedStat = ['actif', 'pending', 'vendu', 'archive'];
    if ($st !== '' && $st !== 'all' && in_array($st, $allowedStat, true)) {
        $where[] = 'statut = :lfst';
        $params[':lfst'] = $st;
    }

    $pub = isset($flt['flt_pub']) ? (string) $flt['flt_pub'] : '';
    if ($hasPublier) {
        if ($pub === 'yes') {
            $where[] = '(publier_vitrine IS NULL OR publier_vitrine = 1)';
        } elseif ($pub === 'no') {
            $where[] = '(publier_vitrine = 0)';
        }
    }

    $villeF = isset($flt['flt_ville']) ? trim((string) $flt['flt_ville']) : '';
    if ($villeF !== '') {
        $where[] = '(ville LIKE :lville)';
        $params[':lville'] = '%' . $villeF . '%';
    }

    return ['where' => $where, 'params' => $params];
}

/**
 * @return array{rows: list<array<string,mixed>>, error: ?string, total_biens: int, has_publier: bool, has_source: bool}
 */
function listings_biens_fetch(array $flt): array
{
    try {
        $pdo = db();
        $cols = scraping_import_biens_column_set_exists($pdo);
        $total = (int) $pdo->query('SELECT COUNT(*) FROM biens')->fetchColumn();

        ['where' => $where, 'params' => $qparams] = listings_biens_filters_to_sql($flt, isset($cols['publier_vitrine']));
        $whereSql = implode(' AND ', $where);

        $selectCols = 'id, slug, titre, ville, adresse, type_bien, prix, statut, created_at';
        if (isset($cols['source'])) {
            $selectCols .= ', source';
        }
        if (isset($cols['publier_vitrine'])) {
            $selectCols .= ', publier_vitrine';
        }
        if (isset($cols['reference'])) {
            $selectCols .= ', reference';
        }

        $sql = "SELECT {$selectCols} FROM biens WHERE {$whereSql} ORDER BY created_at DESC LIMIT 300";

        $st = $pdo->prepare($sql);
        foreach ($qparams as $k => $v) {
            $st->bindValue($k, $v);
        }
        $st->execute();

        return [
            'rows' => $st->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'error' => null,
            'total_biens' => $total,
            'has_publier' => isset($cols['publier_vitrine']),
            'has_source' => isset($cols['source']),
        ];
    } catch (Throwable $e) {
        error_log('listings_biens_fetch: ' . $e->getMessage());

        return [
            'rows' => [],
            'error' => $e->getMessage(),
            'total_biens' => 0,
            'has_publier' => false,
            'has_source' => false,
        ];
    }
}

function scraping_import_biens_column_set_exists(PDO $pdo): array
{
    require_once dirname(__DIR__, 2) . '/core/helpers/scraping_import_biens.php';

    return scraping_import_biens_column_set($pdo);
}

function listings_is_mes_biens(?string $source): bool
{
    return ($source ?? '') !== 'partage';
}

/**
 * Villes présentes sur les fiches (import / saisie) pour auto-complétion du filtre.
 *
 * @return list<string>
 */
function listings_distinct_villes_admin(): array
{
    try {
        $pdo = db();
        $st = $pdo->query(
            "SELECT DISTINCT TRIM(ville) AS v FROM biens
             WHERE ville IS NOT NULL AND TRIM(ville) <> ''
             ORDER BY v ASC
             LIMIT 300"
        );
        if (!$st) {
            return [];
        }
        $rows = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $v = trim((string) ($row['v'] ?? ''));
            if ($v !== '') {
                $rows[] = $v;
            }
        }

        return $rows;
    } catch (Throwable $e) {
        error_log('listings_distinct_villes_admin: ' . $e->getMessage());

        return [];
    }
}

function renderContent(): void
{
    $flt = [
        'flt_q'       => isset($_GET['flt_q']) ? (string) $_GET['flt_q'] : '',
        'flt_ville'   => isset($_GET['flt_ville']) ? (string) $_GET['flt_ville'] : '',
        'flt_source'  => isset($_GET['flt_source']) ? (string) $_GET['flt_source'] : '',
        'flt_statut'  => isset($_GET['flt_statut']) ? (string) $_GET['flt_statut'] : '',
        'flt_pub'     => isset($_GET['flt_pub']) ? (string) $_GET['flt_pub'] : '',
    ];

    $fetch = listings_biens_fetch($flt);
    $villesSuggest = listings_distinct_villes_admin();

    ?>
    <style>
        .listings-notice { background:#eff6ff;border:1px solid #bfdbfe;color:#1e3a5f;border-radius:12px;padding:14px 18px;margin-bottom:20px;font-size:14px;line-height:1.5}
        .listings-toolbar{display:flex;flex-wrap:wrap;gap:12px;align-items:end;margin-bottom:18px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px}
        .listings-toolbar label{font-size:12px;color:#475569;display:block;margin-bottom:4px;font-weight:600}
        .listings-toolbar input,.listings-toolbar select{padding:9px 10px;border-radius:8px;border:1px solid #cbd5e1;min-width:140px;background:#fff}
        .listings-toolbar button{padding:10px 16px;border-radius:8px;border:none;background:#0f2237;color:#fff;font-weight:600;cursor:pointer}
        .listings-toolbar a.btn-reset{padding:10px 14px;border-radius:8px;border:1px solid #cbd5e1;color:#334155;text-decoration:none;font-size:13px;background:#fff}
        .hub-hero{background:linear-gradient(135deg,#0f2237,#1a3a5c);border-radius:16px;padding:24px 20px;color:#fff;margin-bottom:22px}
        .hub-hero h1{margin:0 0 8px;font-size:28px;font-weight:700}
        .hub-actions{display:flex;gap:12px;margin-top:14px;flex-wrap:wrap}
        .hub-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:8px;font-weight:600;text-decoration:none}
        .hub-btn--gold{background:#c9a84c;color:#10253c}
        .hub-btn--muted{background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.35)}
        .listings-section{background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:auto}
        .section-header{padding:14px 18px;border-bottom:1px solid #eef2f7;background:#f8fafc}
        .section-header h2{margin:0;font-size:16px;font-weight:600}
        .listings-table{width:100%;border-collapse:collapse;font-size:13px;min-width:1080px}
        .listings-table th{padding:11px 10px;text-align:left;font-weight:600;color:#475569;background:#f8fafc;border-bottom:1px solid #e5e7eb;white-space:nowrap}
        .listings-table td{padding:10px;border-bottom:1px solid #f1f5f9;vertical-align:top}
        .property-title{font-weight:600;color:#0f172a}
        .property-addr{font-size:12px;color:#64748b}
        .empty-state{text-align:center;padding:28px;color:#94a3b8}
        .listings-mini{font-size:12px;color:#64748b}
        select.ls-inline{width:100%;min-width:120px;font-size:12px;padding:6px;border-radius:6px;border:1px solid #cbd5e1}
        .lnk-edit{color:#2563eb;text-decoration:none;font-weight:600}
        .lnk-edit--off{color:#94a3b8;cursor:not-allowed}
        .lnk-delete{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;color:#dc2626;background:#fef2f2;border:none;border-radius:8px;cursor:pointer;padding:0;font-size:14px;transition:background .15s}
        .lnk-delete:hover{background:#fecaca;color:#991b1b}
        .listings-act-icons{display:flex;flex-wrap:wrap;align-items:center;gap:6px;justify-content:flex-end}
        .btn-icon-act{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;text-decoration:none;border:1px solid #e2e8f0;background:#fff;color:#475569;font-size:13px}
        .btn-icon-act:hover{background:#f1f5f9;color:#2563eb;border-color:#cbd5e1}
        .btn-icon-act.btn-icon-act--muted{color:#94a3b8;cursor:default;background:#f8fafc;border-color:#e5e7eb}
        #listingsStatus{font-size:13px;color:#059669;margin-top:8px;min-height:1.2em}
        .mono{font-family:ui-monospace,Menlo,monospace;font-size:12px;color:#475569}
        .listing-row-cb{width:42px;text-align:center}
        .listing-row-checkbox,.listing-header-cb,.listing-data-cb{width:18px;height:18px;accent-color:#64748b;cursor:pointer}
        .listings-selection-bar{display:none;align-items:center;justify-content:flex-end;gap:10px;padding:10px 16px;background:#fafafa;border-bottom:1px solid #e5e7eb;font-size:13px;color:#475569}
        .listings-selection-bar.is-visible{display:flex}
        .listings-selection-bar span{font-weight:600;color:#64748b}
        .btn-bulk-del{display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border-radius:8px;border:none;background:#dc2626;color:#fff;font-weight:600;cursor:pointer;font-size:13px;font-family:inherit}
        .btn-bulk-del:not(:disabled):hover{background:#b91c1c}
    </style>

    <?php
    $csrf = htmlspecialchars(function_exists('csrfToken') ? csrfToken() : ((string) ($_SESSION['csrf_token'] ?? '')), ENT_QUOTES, 'UTF-8');
    $apiBase = '/admin/';
    ?>

    <header class="hub-hero">
        <h1>Listings & publication</h1>
        <p class="mono">Total&nbsp;: <?= (int) $fetch['total_biens']; ?> bien(s) en base. Validez l’affichage sur pages publiques (liste /biens).</p>
        <div class="hub-actions">
            <a href="/admin/?module=scraping" class="hub-btn hub-btn--gold"><i class="fas fa-download"></i> Import eXp</a>
            <a href="/admin/?module=biens&amp;view=catalogue" class="hub-btn hub-btn--muted"><i class="fas fa-layer-group"></i> Hub Biens</a>
        </div>
    </header>

    <p class="listings-notice">
        Après <strong>scraping</strong>, les fiches ont <strong>Publication = Non</strong> jusqu’à approbation ici (<code>publier_vitrine</code>). Les annonces anciennes restent comme avant si la valeur est vide (migration 039).
    </p>

    <?php if (!$fetch['has_publier'] && !$fetch['error']): ?>
        <p style="background:#fef3c7;border:1px solid #fcd34d;padding:14px;border-radius:12px;margin-bottom:16px;font-size:14px;">
            Migration <strong>039_biens_publication_listing.sql</strong> à exécuter pour activer les colonnes Publication et filtres correspondants.
        </p>
    <?php endif; ?>

    <?php if ($fetch['error'] !== null): ?>
        <p style="background:#fef2f2;border:1px solid #fecaca;padding:14px;border-radius:12px;margin-bottom:16px;color:#991b1b;font-size:14px;">
            <?= htmlspecialchars($fetch['error'], ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php
    $q = htmlspecialchars($flt['flt_q'], ENT_QUOTES, 'UTF-8');
    $fville = htmlspecialchars($flt['flt_ville'], ENT_QUOTES, 'UTF-8');

    ?>
    <form class="listings-toolbar" method="get" action="">
        <input type="hidden" name="module" value="listings">
        <div>
            <label for="flt_ville">Ville (données import)</label>
            <input type="text" id="flt_ville" name="flt_ville" list="listings-villes-list" value="<?= $fville ?>"
                   placeholder="ex. Aix-les-Bains, Chambéry…"
                   autocomplete="off" style="min-width:200px">
            <datalist id="listings-villes-list">
                <?php foreach ($villesSuggest as $vx) {
                    $vxh = htmlspecialchars($vx, ENT_QUOTES, 'UTF-8');
                    ?>
                <option value="<?= $vxh ?>">
                <?php } ?>
            </datalist>
        </div>
        <div>
            <label for="flt_q">Recherche libre</label>
            <input type="search" id="flt_q" name="flt_q" value="<?= $q ?>" placeholder="Titre, ville, texte libre…">
        </div>
        <div>
            <label for="flt_source">Origine</label>
            <select id="flt_source" name="flt_source">
                <option value="" <?= ($flt['flt_source'] ?? '') === '' ? ' selected' : '' ?>>Tous</option>
                <option value="own" <?= ($flt['flt_source'] ?? '') === 'own' ? ' selected' : '' ?>>Mes biens / manuel</option>
                <option value="partage" <?= ($flt['flt_source'] ?? '') === 'partage' ? ' selected' : '' ?>>Partagés (eXp)</option>
            </select>
        </div>
        <div>
            <label for="flt_statut">Statut DB</label>
            <select id="flt_statut" name="flt_statut">
                <option value="" <?= ($flt['flt_statut'] ?? '') === '' ? ' selected' : '' ?>>Tous</option>
                <option value="actif">actif</option>
                <option value="pending">pending</option>
                <option value="vendu">vendu</option>
                <option value="archive">archive</option>
            </select>
        </div>
        <div>
            <label for="flt_pub">Publication vitrine</label>
            <select id="flt_pub" name="flt_pub">
                <option value="" <?= ($flt['flt_pub'] ?? '') === '' ? ' selected' : '' ?>>Tous</option>
                <option value="yes">Oui</option>
                <option value="no">Non</option>
            </select>
        </div>
        <div>
            <button type="submit"><i class="fas fa-filter"></i> Filtrer</button>
            <a class="btn-reset" href="/admin/?module=listings">Réinitialiser</a>
        </div>
    </form>

    <div class="listings-section">
        <div class="section-header">
            <h2>Annonces (table biens)</h2>
            <span class="listings-mini"><?= count($fetch['rows']) ?> ligne(s) affichée(s)</span>
        </div>
        <div id="listings-selection-bar" class="listings-selection-bar" aria-live="polite">
            <span><span id="listing-bulk-count">0</span> sélectionné(s)</span>
            <button type="button" class="btn-bulk-del" id="listing-bulk-delete-btn" disabled title="Supprimer les annonces cochées (max 100)">
                <i class="fas fa-trash-alt"></i> Supprimer
            </button>
        </div>
        <table class="listings-table">
            <thead>
            <tr>
                <th class="listing-row-cb" title="Tout sélectionner / tout désélectionner">
                    <input type="checkbox" id="listing-select-all-page" class="listing-header-cb" autocomplete="off" aria-label="Tout sélectionner dans cette liste">
                </th>
                <th>Bien</th>
                <th>Origine</th>
                <th>Statut</th>
                <th>Publication /biens</th>
                <th>Ville</th>
                <th>Prix</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($fetch['rows'] === []) { ?>
                <tr><td colspan="8" class="empty-state">Aucune ligne avec ces filtres.</td></tr>
            <?php } else {
                foreach ($fetch['rows'] as $prop) {
                    $id = (int) ($prop['id'] ?? 0);
                    $slug = isset($prop['slug']) ? (string) $prop['slug'] : '';
                    $source = isset($prop['source']) ? (string) $prop['source'] : '';
                    $isOwn = listings_is_mes_biens(($source ?: null));
                    ?>
                    <tr data-bien-id="<?= $id ?>">
                        <td class="listing-row-cb">
                            <input type="checkbox" class="listing-data-cb" value="<?= $id ?>" autocomplete="off" aria-label="Sélectionner <?= $id ?>">
                        </td>
                        <td>
                            <div class="property-title">#<?= $id ?> — <?= htmlspecialchars((string) ($prop['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="property-addr mono"><?= htmlspecialchars((string) ($prop['reference'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        </td>
                        <td>
                            <?php if (!empty($fetch['has_source'])) { ?>
                                <select class="ls-inline" data-field="source" title="Origine annonce">
                                    <option value="own" <?= $source !== 'partage' ? ' selected' : '' ?>>Mes biens</option>
                                    <option value="partage" <?= $source === 'partage' ? ' selected' : '' ?>>Partagé</option>
                                </select>
                            <?php } else { ?>
                                <span class="listings-mini">—</span>
                            <?php } ?>
                        </td>
                        <td>
                            <select class="ls-inline" data-field="statut">
                                <?php foreach (['actif', 'pending', 'vendu', 'archive'] as $stv) {
                                    ?>
                                    <option value="<?= htmlspecialchars($stv, ENT_QUOTES, 'UTF-8') ?>" <?= ($prop['statut'] ?? '') === $stv ? ' selected' : '' ?>><?= $stv ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td>
                            <?php
                            $pv = $prop['publier_vitrine'] ?? null;
                            $selNull = $pv === null || $pv === '';
                            $sel0 = !$selNull && (int) $pv === 0;
                            $sel1 = !$selNull && (int) $pv === 1;
                            ?>
                            <?php if ($fetch['has_publier']) { ?>
                                <select class="ls-inline" data-field="publier_vitrine">
                                    <option value="null" <?= $selNull ? ' selected' : '' ?>>Auto (ancien)</option>
                                    <option value="1" <?= $sel1 ? ' selected' : '' ?>>Oui</option>
                                    <option value="0" <?= $sel0 ? ' selected' : '' ?>>Non</option>
                                </select>
                            <?php } else { ?>—<?php } ?>
                        </td>
                        <td><?= htmlspecialchars((string) ($prop['ville'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= isset($prop['prix']) ? number_format((float) $prop['prix'], 0, ',', ' ') . ' €' : '—' ?></td>
                        <td>
                            <?php $pubUrl = $slug !== '' ? '/biens/' . rawurlencode($slug) : '/bien.php?id=' . $id; ?>
                            <div class="listings-act-icons">
                                <a class="btn-icon-act" href="<?= htmlspecialchars($pubUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" title="Voir sur le site public"><i class="fas fa-external-link-alt"></i></a>
                                <?php if ($isOwn) { ?>
                                    <a class="btn-icon-act" href="/admin/?module=biens&amp;view=photos&amp;bien_id=<?= $id ?>" title="Photos / médias"><i class="fas fa-images"></i></a>
                                <?php } else { ?>
                                    <span class="btn-icon-act btn-icon-act--muted" title="Partagé : pas d’édition ici"><i class="fas fa-lock"></i></span>
                                <?php } ?>
                                <button type="button" class="lnk-delete listing-delete-btn" data-id="<?= $id ?>" title="Supprimer cette annonce" aria-label="Supprimer <?= (int) $id ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php
                }
            } ?>
            </tbody>
        </table>
    </div>
    <div id="listingsStatus"></div>

    <script>
    (function () {
        const CSRF = '<?= $csrf ?>';
        const BASE = <?= json_encode($apiBase ?? '/admin/', JSON_UNESCAPED_SLASHES) ?>;
        const url = BASE.replace(/\/?$/, '/') + 'api/listings/quick-update.php';
        const urlDelete = BASE.replace(/\/?$/, '/') + 'api/listings/delete.php';
        const urlBulkDelete = BASE.replace(/\/?$/, '/') + 'api/listings/bulk-delete.php';
        function status(txt, ok) {
            var el = document.getElementById('listingsStatus');
            el.textContent = txt || '';
            el.style.color = ok ? '#059669' : '#b91c1c';
        }
        document.querySelectorAll('.listings-table select[data-field]').forEach(function (sel) {
            sel.dataset.originalValue = sel.value;
        });
        document.querySelectorAll('.listings-table select[data-field]').forEach(function (sel) {
            sel.addEventListener('change', function () {
                var tr = sel.closest('tr');
                var id = tr && tr.getAttribute('data-bien-id');
                if (!id) return;
                var field = sel.getAttribute('data-field');
                var val = sel.value;
                var payload = {csrf_token: CSRF, id: Number(id)};
                if (field === 'publier_vitrine') {
                    payload.publier_vitrine = (val === 'null') ? null : Number(val);
                } else {
                    payload[field] = val;
                }
                status('…', true);
                fetch(url, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload),
                    credentials: 'same-origin'
                }).then(function (r) { return r.json().then(function (d) { return { r: r, d: d }; }); })
                  .then(function (x) {
                    if (!x.r.ok || !x.d.success) throw new Error(x.d.message || ('HTTP ' + x.r.status));
                    status(x.d.message || 'OK', true);
                    sel.dataset.originalValue = sel.value;
                }).catch(function (e) {
                    status(e.message || 'Erreur', false);
                    var orig = sel.dataset.originalValue;
                    if (orig !== undefined) sel.value = orig;
                });
            });
        });
        function listingSelectedIds() {
            var ids = [];
            document.querySelectorAll('.listing-data-cb:checked').forEach(function (cb) {
                var v = Number(cb.value);
                if (v > 0) ids.push(v);
            });
            return ids;
        }
        function listingUpdateBulkBar() {
            var ids = listingSelectedIds();
            var n = ids.length;
            var el = document.getElementById('listing-bulk-count');
            var btn = document.getElementById('listing-bulk-delete-btn');
            var bar = document.getElementById('listings-selection-bar');
            var all = document.getElementById('listing-select-all-page');
            if (!el || !btn) return;
            el.textContent = String(n);
            btn.disabled = n === 0;
            if (bar) bar.classList.toggle('is-visible', n > 0);
            if (all) {
                var boxes = document.querySelectorAll('.listing-data-cb');
                var total = boxes.length;
                var checked = document.querySelectorAll('.listing-data-cb:checked').length;
                all.checked = total > 0 && checked === total;
                all.indeterminate = checked > 0 && checked < total;
            }
        }
        var allCb = document.getElementById('listing-select-all-page');
        if (allCb) {
            allCb.addEventListener('change', function () {
                document.querySelectorAll('.listing-data-cb').forEach(function (cb) {
                    cb.checked = !!allCb.checked;
                });
                listingUpdateBulkBar();
            });
        }
        document.querySelectorAll('.listing-data-cb').forEach(function (cb) {
            cb.addEventListener('change', listingUpdateBulkBar);
        });
        var bulkBtn = document.getElementById('listing-bulk-delete-btn');
        if (bulkBtn) {
            bulkBtn.addEventListener('click', function () {
                var ids = listingSelectedIds();
                if (ids.length === 0) return;
                if (!window.confirm('Supprimer définitivement ' + ids.length + ' annonce(s) sélectionnée(s) ? Les photos jointes suivent si CASCADE.')) return;
                if (ids.length > 100) ids = ids.slice(0, 100);
                status('Suppression en masse…', true);
                fetch(urlBulkDelete, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({csrf_token: CSRF, ids: ids}),
                    credentials: 'same-origin'
                }).then(function (r) { return r.json().then(function (d) { return { r: r, d: d }; }); })
                  .then(function (x) {
                    if (!x.r.ok || !x.d.success) throw new Error(x.d.message || ('HTTP ' + x.r.status));
                    status(x.d.message || 'Supprimé.', true);
                    window.setTimeout(function () { window.location.reload(); }, 500);
                }).catch(function (e) {
                    status(e.message || 'Erreur', false);
                });
            });
        }

        listingUpdateBulkBar();

        document.querySelectorAll('.listing-delete-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-id');
                if (!id || !window.confirm('Supprimer définitivement l’annonce #' + id + ' ? Les photos jointes seront aussi supprimées.')) {
                    return;
                }
                status('Suppression…', true);
                fetch(urlDelete, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({csrf_token: CSRF, id: Number(id)}),
                    credentials: 'same-origin'
                }).then(function (r) { return r.json().then(function (d) { return { r: r, d: d }; }); })
                  .then(function (x) {
                    if (!x.r.ok || !x.d.success) throw new Error(x.d.message || ('HTTP ' + x.r.status));
                    status(x.d.message || 'Supprimé.', true);
                    window.setTimeout(function () { window.location.reload(); }, 400);
                }).catch(function (e) {
                    status(e.message || 'Erreur', false);
                });
            });
        });
    })();
    </script>

    <?php
}
