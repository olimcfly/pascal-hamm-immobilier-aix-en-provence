<?php

$rdvNotice = null;
$rdvNoticeType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leadId = (int)($_POST['lead_id'] ?? 0);
    $operation = (string)($_POST['operation'] ?? '');
    $scheduledDate = trim((string)($_POST['scheduled_date'] ?? ''));
    $scheduledTime = trim((string)($_POST['scheduled_time'] ?? ''));
    $comment = trim((string)($_POST['comment'] ?? ''));

    $scheduledAt = null;
    if ($operation === 'reschedule' && $scheduledDate !== '') {
        $scheduledAt = $scheduledDate . ' ' . ($scheduledTime !== '' ? $scheduledTime : '09:00');
    }

    if ($operation === 'confirm' && $scheduledDate !== '') {
        $scheduledAt = $scheduledDate . ' ' . ($scheduledTime !== '' ? $scheduledTime : '09:00');
    }

    $ok = LeadService::updateRdvStatus($leadId, $operation, $scheduledAt, $comment);
    $rdvNotice = $ok ? 'Mise à jour du rendez-vous enregistrée.' : 'Impossible de mettre à jour ce rendez-vous.';
    $rdvNoticeType = $ok ? 'success' : 'error';
}

$rdvLeads = LeadService::list(['stage_like' => 'rdv']);

$calendarBuckets = [];
foreach ($rdvLeads as $lead) {
    $metadata = is_array($lead['metadata'] ?? null) ? $lead['metadata'] : [];
    $appointmentAt = (string)($metadata['appointment_at'] ?? '');
    $fallbackDate = (string)($lead['updated_at'] ?? $lead['created_at'] ?? '');
    $dateSource = $appointmentAt !== '' ? $appointmentAt : $fallbackDate;
    $timestamp = strtotime($dateSource);
    if ($timestamp === false) {
        $timestamp = strtotime((string)($lead['created_at'] ?? 'now')) ?: time();
    }

    $dayKey = date('Y-m-d', $timestamp);
    if (!isset($calendarBuckets[$dayKey])) {
        $calendarBuckets[$dayKey] = [];
    }
    $calendarBuckets[$dayKey][] = $lead;
}
krsort($calendarBuckets);

?>
<style>
    .rdv-toolbar{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem}
    .rdv-back{display:inline-flex;align-items:center;gap:.45rem;text-decoration:none;color:#334155;font-weight:700}
    .rdv-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem}
    .rdv-day{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:1rem;box-shadow:0 10px 30px rgba(15,23,42,.04)}
    .rdv-day h3{margin:0 0 .7rem;font-size:1rem}
    .rdv-card{border:1px solid #e2e8f0;border-radius:12px;padding:.75rem;background:#f8fafc;margin-bottom:.7rem}
    .rdv-meta{font-size:.82rem;color:#64748b}
    .rdv-badge{display:inline-block;padding:.2rem .55rem;border-radius:999px;font-weight:700;background:#fee2e2;color:#991b1b;font-size:.72rem}
    .rdv-actions{display:grid;gap:.4rem;margin-top:.6rem}
    .rdv-actions form{display:grid;grid-template-columns:1fr 1fr;gap:.4rem}
    .rdv-actions input,.rdv-actions select,.rdv-actions textarea,.rdv-actions button{border:1px solid #cbd5e1;border-radius:8px;padding:.45rem .55rem;font-size:.86rem}
    .rdv-actions textarea{grid-column:1/-1;min-height:54px;resize:vertical}
    .rdv-actions button{cursor:pointer;font-weight:700;background:#0f172a;color:#fff;border-color:#0f172a}
    .rdv-notice{padding:.75rem .9rem;border-radius:10px;margin-bottom:1rem}
    .rdv-notice.success{background:#ecfdf5;border:1px solid #86efac;color:#166534}
    .rdv-notice.error{background:#fef2f2;border:1px solid #fca5a5;color:#991b1b}
</style>

<div class="page-header">
    <h1><i class="fas fa-calendar-days page-icon"></i> Convertir <span class="page-title-accent">Prise de RDV</span></h1>
    <p>Calendrier des leads au statut RDV, avec actions de confirmation, annulation et reprogrammation.</p>
</div>

<div class="rdv-toolbar">
    <a href="/admin?module=convertir" class="rdv-back"><i class="fas fa-arrow-left"></i> Retour aux sous-modules</a>
    <strong><?= count($rdvLeads) ?> lead(s) à traiter</strong>
</div>

<?php if ($rdvNotice): ?>
    <div class="rdv-notice <?= e($rdvNoticeType) ?>"><?= e($rdvNotice) ?></div>
<?php endif; ?>

<?php if (!$calendarBuckets): ?>
    <div class="rdv-day">Aucun lead avec un statut RDV pour le moment.</div>
<?php else: ?>
    <section class="rdv-grid">
        <?php foreach ($calendarBuckets as $day => $entries): ?>
            <article class="rdv-day">
                <h3><?= e(date('l d F Y', strtotime($day))) ?> <span class="rdv-meta">(<?= count($entries) ?>)</span></h3>
                <?php foreach ($entries as $lead): ?>
                    <?php
                    $metadata = is_array($lead['metadata'] ?? null) ? $lead['metadata'] : [];
                    $appointmentAt = (string)($metadata['appointment_at'] ?? '');
                    $status = (string)($metadata['appointment_status'] ?? 'demande');
                    ?>
                    <div class="rdv-card">
                        <div style="display:flex;justify-content:space-between;gap:.6rem;">
                            <strong><?= e(trim(($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? ''))) ?: 'Lead sans nom' ?></strong>
                            <span class="rdv-badge"><?= e(LeadService::stageLabel((string)($lead['stage'] ?? 'rdv'))) ?></span>
                        </div>
                        <div class="rdv-meta"><?= e((string)($lead['email'] ?? '')) ?> · <?= e((string)($lead['phone'] ?? '')) ?></div>
                        <div class="rdv-meta">Pipeline : <?= e(LeadService::sourceLabel((string)($lead['pipeline'] ?? 'autre'))) ?></div>
                        <div class="rdv-meta">Créé le <?= e(date('d/m/Y H:i', strtotime((string)$lead['created_at']))) ?></div>
                        <div class="rdv-meta">RDV : <?= e($appointmentAt !== '' ? date('d/m/Y H:i', strtotime($appointmentAt)) : 'à planifier') ?> · statut: <?= e($status) ?></div>

                        <div class="rdv-actions">
                            <form method="post" action="/admin?module=convertir&action=rdv">
                                <input type="hidden" name="lead_id" value="<?= (int)$lead['id'] ?>">
                                <select name="operation" required>
                                    <option value="confirm">Confirmer</option>
                                    <option value="cancel">Annuler</option>
                                    <option value="reschedule">Reprogrammer</option>
                                </select>
                                <input type="date" name="scheduled_date" value="<?= e(substr((string)$appointmentAt, 0, 10)) ?>">
                                <input type="time" name="scheduled_time" value="<?= e(strlen((string)$appointmentAt) >= 16 ? substr((string)$appointmentAt, 11, 5) : '') ?>">
                                <textarea name="comment" placeholder="Commentaire (optionnel)"></textarea>
                                <button type="submit"><i class="fas fa-check"></i> Mettre à jour</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
