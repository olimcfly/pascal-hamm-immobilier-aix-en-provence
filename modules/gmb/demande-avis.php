<?php
require_once __DIR__ . '/includes/GmbService.php';

$user           = Auth::user();
$service        = new GmbService((int) ($user['id'] ?? 0));
$emailTemplates = $service->templates('email');
?>
<style>
.da-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.2rem}
.da-card{background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius,16px);padding:1.25rem 1.4rem;box-shadow:var(--hub-shadow-sm)}
.da-card h3{margin:0 0 1rem;font-size:1rem;font-weight:700;color:#0f172a}
.da-field{display:grid;gap:.3rem;margin-bottom:.75rem}
.da-field label{font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em}
.da-field input,.da-field select,.da-field textarea{border:1px solid #cbd5e1;border-radius:10px;padding:.55rem .75rem;font-size:.88rem;width:100%;box-sizing:border-box}
.da-field textarea{min-height:100px;resize:vertical}
@media(max-width:700px){.da-grid{grid-template-columns:1fr}}
</style>

<div class="da-grid">
    <div class="da-card">
        <h3><i class="fas fa-paper-plane" style="color:#f59e0b;margin-right:.4rem"></i>Envoyer une demande</h3>
        <form id="gmb-demande-form">
            <div class="da-field">
                <label>Nom client</label>
                <input name="client_nom" required placeholder="ex : Jean Martin">
            </div>
            <div class="da-field">
                <label>Email client</label>
                <input type="email" name="client_email" placeholder="jean@exemple.fr">
            </div>
            <div class="da-field">
                <label>Téléphone</label>
                <input name="client_tel" placeholder="06 xx xx xx xx">
            </div>
            <div class="da-field">
                <label>Adresse du bien</label>
                <input name="bien_adresse" placeholder="ex : 12 rue des Lilas, Aix">
            </div>
            <div class="da-field">
                <label>Canal d'envoi</label>
                <select name="canal">
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                    <option value="both">Email + SMS</option>
                </select>
            </div>
            <div class="da-field">
                <label>Template</label>
                <select name="template_id">
                    <option value="">Template par défaut</option>
                    <?php foreach ($emailTemplates as $tpl): ?>
                        <option value="<?= (int) $tpl['id'] ?>"><?= htmlspecialchars($tpl['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="hub-btn hub-btn--gold"><i class="fas fa-paper-plane"></i> Envoyer la demande</button>
        </form>
    </div>

    <div class="da-card">
        <h3><i class="fas fa-file-lines" style="color:#3b82f6;margin-right:.4rem"></i>Nouveau template</h3>
        <form id="gmb-template-form">
            <div class="da-field">
                <label>Nom du template</label>
                <input name="nom" required placeholder="ex : Après signature">
            </div>
            <div class="da-field">
                <label>Canal</label>
                <select name="canal">
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                </select>
            </div>
            <div class="da-field">
                <label>Sujet (email)</label>
                <input name="sujet" placeholder="ex : Votre avis nous intéresse !">
            </div>
            <div class="da-field">
                <label>Contenu</label>
                <textarea name="contenu" required placeholder="Bonjour {prenom}, …"></textarea>
            </div>
            <button type="submit" class="hub-btn"><i class="fas fa-save"></i> Sauvegarder le template</button>
        </form>
    </div>
</div>
