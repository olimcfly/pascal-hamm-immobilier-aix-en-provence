<?php
declare(strict_types=1);

$pageTitle = 'Mon profil';
$pageDescription = 'Infos personnelles et photo de profil';

$profilFlashOk = '';
$profilFlashErr = '';

$uid = (int) ($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profil_save'])) {
    if ($uid <= 0) {
        $profilFlashErr = 'Session invalide. Reconnectez-vous.';
    } elseif (!function_exists('settings_save')) {
        $profilFlashErr = 'Sauvegarde indisponible (configuration).';
    } else {
        $prenom = trim((string) ($_POST['profil_prenom'] ?? ''));
        $nom = trim((string) ($_POST['profil_nom'] ?? ''));
        $email = trim((string) ($_POST['profil_email'] ?? ''));
        $tel = trim((string) ($_POST['profil_telephone'] ?? ''));
        $ville = trim((string) ($_POST['profil_ville'] ?? ''));
        $address = trim((string) ($_POST['profil_address'] ?? ''));
        $photo = trim((string) ($_POST['profil_photo'] ?? ''));

        $data = [
            'profil_prenom' => $prenom,
            'profil_nom' => $nom,
            'profil_email' => $email,
            'profil_telephone' => $tel,
            'profil_ville' => $ville,
            'profil_address' => $address,
            'profil_photo' => $photo,
        ];

        $ok = settings_save($data, 'profil', $uid);
        if ($ok) {
            $syncData = [
                'advisor_firstname' => $prenom,
                'advisor_lastname' => $nom,
                'advisor_phone' => $tel,
                'advisor_email' => $email,
                'advisor_photo' => $photo,
                'contact_phone' => $tel,
                'contact_email' => $email,
                'contact_address' => $address,
            ];
            if ($ville !== '') {
                $syncData['zone_city'] = $ville;
            }
            settings_save($syncData, 'profil', $uid);
            if (function_exists('setting_flush')) {
                setting_flush($uid);
            }
            $profilFlashOk = 'Vos informations ont été enregistrées.';
        } else {
            $profilFlashErr = 'Impossible d’enregistrer. Vérifiez que votre compte est bien lié à la base (user_id).';
        }
    }
}

/** @return array<string, string> */
function profil_form_values(int $userId): array
{
    $def = static function (string $key, string $advisorKey, string $fallback) use ($userId): string {
        $v = trim((string) setting($key, '', $userId));
        if ($v === '' && $advisorKey !== '') {
            $v = trim((string) setting($advisorKey, '', $userId));
        }
        if ($v === '') {
            $v = $fallback;
        }

        return $v;
    };

    return [
        'profil_prenom' => $def('profil_prenom', 'advisor_firstname', 'Pascal'),
        'profil_nom' => $def('profil_nom', 'advisor_lastname', 'Hamm'),
        'profil_email' => $def('profil_email', 'advisor_email', defined('APP_EMAIL') ? APP_EMAIL : ''),
        'profil_telephone' => $def('profil_telephone', 'advisor_phone', defined('APP_PHONE') ? APP_PHONE : ''),
        'profil_ville' => $def('profil_ville', 'zone_city', defined('APP_CITY') ? APP_CITY : ''),
        'profil_address' => $def('profil_address', 'contact_address', defined('APP_ADDRESS') ? APP_ADDRESS : ''),
        'profil_photo' => $def('profil_photo', 'advisor_photo', DEFAULT_ADVISOR_PHOTO_URL),
    ];
}

$pv = profil_form_values($uid);
$initials = strtoupper(mb_substr($pv['profil_prenom'], 0, 1) . mb_substr($pv['profil_nom'], 0, 1));
if (trim($initials) === '') {
    $initials = 'PH';
}

function renderContent(): void
{
    global $profilFlashOk, $profilFlashErr, $pv, $initials;
    ?>
    <style>
    .profil-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .profil-hero {
        background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
        border-radius: 16px;
        padding: 36px 40px;
        color: #fff;
        margin-bottom: 32px;
        box-shadow: 0 4px 20px rgba(15,34,55,.18);
    }
    .profil-hero h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 12px;
    }
    .profil-hero p {
        font-size: 15px;
        color: rgba(255,255,255,.7);
        margin: 0;
    }

    .profil-flash {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 600;
    }
    .profil-flash--ok {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .profil-flash--err {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .profil-section {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
    }
    .profil-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f1f5f9;
    }

    .profil-form-group {
        margin-bottom: 20px;
    }
    .profil-form-group:last-child {
        margin-bottom: 0;
    }
    .profil-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    @media (max-width: 600px) {
        .profil-form-row {
            grid-template-columns: 1fr;
        }
    }

    .profil-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }
    .profil-input,
    .profil-textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color .2s;
    }
    .profil-input:focus,
    .profil-textarea:focus {
        outline: none;
        border-color: #c9a84c;
        box-shadow: 0 0 0 3px rgba(201,168,76,.1);
    }

    .profil-avatar-section {
        display: flex;
        align-items: flex-start;
        gap: 24px;
        margin-bottom: 24px;
    }
    .profil-avatar-box {
        flex-shrink: 0;
    }
    .profil-avatar {
        width: 120px;
        height: 120px;
        border-radius: 12px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: 700;
        color: #c9a84c;
        border: 2px solid #e5e7eb;
        overflow: hidden;
    }
    .profil-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .profil-avatar-info {
        flex: 1;
    }
    .profil-avatar-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }
    .profil-avatar-input {
        display: block;
        margin-bottom: 12px;
    }
    .profil-helper {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }

    .profil-button-group {
        display: flex;
        gap: 12px;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }
    .profil-btn {
        padding: 11px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s;
    }
    .profil-btn-primary {
        background: #c9a84c;
        color: #0f2237;
    }
    .profil-btn-primary:hover {
        background: #b8943d;
        transform: translateY(-1px);
    }
    .profil-btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }
    .profil-btn-secondary:hover {
        background: #e5e7eb;
    }

    @media (max-width: 600px) {
        .profil-hero { padding: 24px 20px; }
        .profil-section { padding: 16px; }
        .profil-avatar-section { flex-direction: column; }
    }
    </style>

    <div class="profil-container">
        <div class="profil-hero">
            <h1>Mon profil</h1>
            <p>Mettez à jour vos informations personnelles et votre photo. Elles sont utilisées sur le site et dans les emails.</p>
        </div>

        <?php if ($profilFlashOk !== ''): ?>
            <div class="profil-flash profil-flash--ok" role="status"><?= htmlspecialchars($profilFlashOk) ?></div>
        <?php endif; ?>
        <?php if ($profilFlashErr !== ''): ?>
            <div class="profil-flash profil-flash--err" role="alert"><?= htmlspecialchars($profilFlashErr) ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin?module=profil" class="profil-form">
            <input type="hidden" name="profil_save" value="1">

            <div class="profil-section">
                <h2 class="profil-section-title"><i class="fas fa-camera" style="margin-right:8px;color:#3b82f6;"></i>Photo de profil</h2>

                <div class="profil-avatar-section">
                    <div class="profil-avatar-box">
                        <div class="profil-avatar" id="profil-avatar-preview">
                            <?php
                            $photoSrc = $pv['profil_photo'];
                            $photoOk = $photoSrc !== '' && (filter_var($photoSrc, FILTER_VALIDATE_URL) || preg_match('#^/[\w./?#-]+$#', $photoSrc));
                            ?>
                            <?php if ($photoOk): ?>
                                <img src="<?= htmlspecialchars($photoSrc) ?>" alt="">
                            <?php else: ?>
                                <?= htmlspecialchars($initials) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="profil-avatar-info">
                        <label class="profil-avatar-label">URL de la photo</label>
                        <input type="url" class="profil-input profil-avatar-input" name="profil_photo" id="profil_photo"
                               placeholder="https://..." value="<?= htmlspecialchars($pv['profil_photo']) ?>">
                        <div class="profil-helper">Lien direct vers une image (JPG, PNG). Taille recommandée : 400×400 px.</div>
                    </div>
                </div>
            </div>

            <div class="profil-section">
                <h2 class="profil-section-title"><i class="fas fa-user" style="margin-right:8px;color:#10b981;"></i>Informations personnelles</h2>

                <div class="profil-form-row">
                    <div class="profil-form-group">
                        <label class="profil-label">Prénom</label>
                        <input type="text" class="profil-input" name="profil_prenom" required
                               value="<?= htmlspecialchars($pv['profil_prenom']) ?>">
                    </div>
                    <div class="profil-form-group">
                        <label class="profil-label">Nom</label>
                        <input type="text" class="profil-input" name="profil_nom" required
                               value="<?= htmlspecialchars($pv['profil_nom']) ?>">
                    </div>
                </div>

                <div class="profil-form-group">
                    <label class="profil-label">Email</label>
                    <input type="email" class="profil-input" name="profil_email" required
                           value="<?= htmlspecialchars($pv['profil_email']) ?>">
                    <div class="profil-helper">Email de contact principal (affiché sur le site selon les pages).</div>
                </div>
            </div>

            <div class="profil-section">
                <h2 class="profil-section-title"><i class="fas fa-map-location-dot" style="margin-right:8px;color:#f59e0b;"></i>Localisation</h2>

                <div class="profil-form-group">
                    <label class="profil-label">Adresse</label>
                    <input type="text" class="profil-input" name="profil_address"
                           value="<?= htmlspecialchars($pv['profil_address']) ?>">
                    <div class="profil-helper">Adresse du bureau ou mention géographique (ex. secteur d’activité).</div>
                </div>

                <div class="profil-form-row">
                    <div class="profil-form-group">
                        <label class="profil-label">Ville</label>
                        <input type="text" class="profil-input" name="profil_ville"
                               value="<?= htmlspecialchars($pv['profil_ville']) ?>">
                    </div>
                    <div class="profil-form-group">
                        <label class="profil-label">Téléphone</label>
                        <input type="tel" class="profil-input" name="profil_telephone"
                               value="<?= htmlspecialchars($pv['profil_telephone']) ?>">
                    </div>
                </div>
            </div>

            <div class="profil-button-group">
                <button type="submit" class="profil-btn profil-btn-primary">
                    <i class="fas fa-save" style="margin-right:6px;"></i> Enregistrer les modifications
                </button>
                <button type="reset" class="profil-btn profil-btn-secondary">
                    <i class="fas fa-redo" style="margin-right:6px;"></i> Réinitialiser
                </button>
            </div>
        </form>
    </div>

    <script>
    (function() {
        var input = document.getElementById('profil_photo');
        var preview = document.getElementById('profil-avatar-preview');
        var initials = <?= json_encode($initials, JSON_UNESCAPED_UNICODE) ?>;
        if (!input || !preview) return;
        function refresh() {
            var url = (input.value || '').trim();
            if (url && (/^https?:\/\//i.test(url) || url.charAt(0) === '/')) {
                preview.innerHTML = '<img src="" alt="">';
                var img = preview.querySelector('img');
                img.onload = function() { img.style.display = 'block'; };
                img.onerror = function() { preview.textContent = initials; };
                img.src = url;
            } else {
                preview.textContent = initials;
            }
        }
        input.addEventListener('input', refresh);
    })();
    </script>
    <?php
}
