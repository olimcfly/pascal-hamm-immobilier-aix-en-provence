<?php
declare(strict_types=1);
/** @var array<int, array<string, mixed>> $autoSequences */
/** @var array<string, mixed> $priorityHealth */
/** @var array<string, array<string, mixed>> $presets */
/** @var string $baseQ */
/** @var array<string, array{label:string,q:string}> $filterTabs */
/** @var string $seqFilter */
/** @var string $pageTitle */
/** @var string $pageDescription */
/** @var string $csrfSync */

$triggers = EmailSequencePriorityService::PRIORITY_FORM_TRIGGERS;
$phShow = (isset($priorityHealth['triggers']) && is_array($priorityHealth['triggers'])) ? $priorityHealth['triggers'] : [];

$triggerStyle = [
    'estimation-rapport' => ['icon' => 'fa-file-pdf', 'color' => '#3b82f6'],
    'avis-valeur' => ['icon' => 'fa-scale-balanced', 'color' => '#0e7490'],
    'estimation-resultat' => ['icon' => 'fa-chart-line', 'color' => '#8b5cf6'],
    'guide-offert' => ['icon' => 'fa-book', 'color' => '#10b981'],
    'prendre-rendez-vous' => ['icon' => 'fa-calendar-check', 'color' => '#f59e0b'],
    'contact' => ['icon' => 'fa-envelope', 'color' => '#2563eb'],
    'financement' => ['icon' => 'fa-coins', 'color' => '#059669'],
];

$byTrigger = [];
$otherSeqs = [];
foreach ($autoSequences as $row) {
    $ft = (string) ($row['form_trigger'] ?? '');
    if ($ft !== '' && in_array($ft, $triggers, true)) {
        $byTrigger[$ft][] = $row;
    } else {
        $otherSeqs[] = $row;
    }
}

$healthClass = static function (string $st): string {
    return match ($st) {
        'ok' => 'seq-auto-pill--ok',
        'manquante' => 'seq-auto-pill--miss',
        'doublon' => 'seq-auto-pill--dup',
        'incomplète' => 'seq-auto-pill--inc',
        default => 'seq-auto-pill--neu',
    };
};
?>
<style>
.seq-auto-page { min-width: 0; margin-bottom: 2rem; }
.seq-auto-hero {
    background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
    border-radius: 16px;
    padding: 28px 32px 24px;
    color: #fff;
    margin-bottom: 22px;
    box-shadow: 0 4px 20px rgba(15,34,55,.18);
}
.seq-auto-hero-badge {
    display: inline-block;
    background: rgba(201,168,76,.2);
    color: #c9a84c;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 20px;
    margin-bottom: 12px;
    border: 1px solid rgba(201,168,76,.35);
}
.seq-auto-hero h1 { font-size: 1.45rem; font-weight: 700; margin: 0 0 10px; line-height: 1.25; color: #fff; }
.seq-auto-hero > p { font-size: .92rem; color: rgba(255,255,255,.72); line-height: 1.6; max-width: 48rem; margin: 0 0 14px; }
.seq-auto-hero-note {
    font-size: .8rem;
    color: rgba(255,255,255,.6);
    line-height: 1.5;
    max-width: 48rem;
    margin: 0 0 14px;
}
.seq-filter-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
.seq-filter-tabs a {
    display: inline-flex; align-items: center; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600;
    text-decoration: none; border: 1px solid rgba(255,255,255,.25); color: rgba(255,255,255,.88);
    background: rgba(0,0,0,.12); transition: background .15s, border-color .15s;
}
.seq-filter-tabs a:hover { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.4); color: #fff; }
.seq-filter-tabs a.is-active { background: rgba(201,168,76,.25); border-color: #c9a84c; color: #fff; }
.seq-auto-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; align-items: center; }
.hub-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px; border: none; font-weight: 700; font-size: .83rem; cursor: pointer; transition: all .2s; text-decoration: none; }
.hub-btn-primary { background: #c9a84c; color: #10253c; }
.hub-btn-primary:hover { background: #b8962d; color: #10253c; }
.hub-btn-ghost { background: rgba(255,255,255,.14); color: #fff; border: 1px solid rgba(255,255,255,.35); }
.hub-btn-ghost:hover { background: rgba(255,255,255,.22); }

.seq-auto-layout { display: grid; grid-template-columns: minmax(248px, 280px) 1fr; gap: 22px; align-items: start; }
.seq-auto-aside { position: sticky; top: 12px; }
.seq-auto-aside-title {
    font-size: 12px; font-weight: 700; color: #8a95a3; text-transform: uppercase; letter-spacing: .07em;
    margin: 0 0 12px; padding-left: 4px;
}
.seq-auto-nav { display: flex; flex-direction: column; gap: 10px; }
.seq-auto-nav button {
    display: flex; align-items: flex-start; gap: 12px; width: 100%; text-align: left;
    padding: 14px 12px 14px 10px; border: 0; border-radius: 12px; background: #fff;
    box-shadow: 0 1px 6px rgba(0,0,0,.06); border-left: 4px solid #e8ecf0;
    cursor: pointer; font: inherit; color: inherit; transition: transform .15s, box-shadow .15s, border-color .15s;
}
.seq-auto-nav button:hover { transform: translateX(3px); box-shadow: 0 4px 14px rgba(0,0,0,.08); border-left-color: #c9a84c; }
.seq-auto-nav button.is-active {
    border-left-color: #c9a84c;
    background: linear-gradient(90deg, rgba(201,168,76,.1) 0%, #fff 45%);
    box-shadow: 0 2px 12px rgba(15,34,55,.08);
}
.seq-auto-ico {
    width: 36px; height: 36px; border-radius: 10px; background: #f1f5f9; display: flex; align-items: center; justify-content: center;
    font-size: .85rem; flex-shrink: 0; color: #64748b;
}
.seq-auto-nav button.is-active .seq-auto-ico { background: #fffbeb; color: #b45309; }
.seq-auto-nav-body { flex: 1; min-width: 0; }
.seq-auto-nav-label { font-size: .88rem; font-weight: 600; color: #1e293b; line-height: 1.25; }
.seq-auto-nav-desc { font-size: .72rem; color: #94a3b8; margin-top: 3px; line-height: 1.35; }
.seq-auto-pill {
    font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
    padding: 3px 7px; border-radius: 6px; flex-shrink: 0; align-self: flex-start; margin-top: 2px;
}
.seq-auto-pill--ok { background: #dcfce7; color: #14532d; }
.seq-auto-pill--miss { background: #fee2e2; color: #7f1d1d; }
.seq-auto-pill--dup { background: #ffedd5; color: #9a3412; }
.seq-auto-pill--inc { background: #fef9c3; color: #854d0e; }
.seq-auto-pill--neu { background: #e2e8f0; color: #475569; }

.seq-auto-main-head { margin-bottom: 16px; }
.seq-auto-main-head h2 { margin: 0 0 6px; font-size: 1.05rem; font-weight: 700; color: #0f172a; }
.seq-auto-main-head p { margin: 0; font-size: .82rem; color: #64748b; line-height: 1.5; }

.seq-auto-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(288px, 1fr)); gap: 14px; }
.seq-auto-card {
    background: #fff; border: 1px solid #e8eef7; border-radius: 14px; overflow: hidden;
    box-shadow: 0 2px 8px rgba(15,23,42,.04); transition: box-shadow .18s, transform .15s, border-color .15s;
}
.seq-auto-card:hover { box-shadow: 0 10px 28px rgba(15,34,55,.1); border-color: #dce5f0; transform: translateY(-2px); }
.seq-auto-card-bar { height: 4px; background: linear-gradient(90deg, #1a3a5c, #c9a84c); }
.seq-auto-card-in { padding: 16px 16px 14px; }
.seq-auto-card-badges { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; align-items: center; }
.seq-auto-badge-st { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; }
.seq-auto-card h3 { margin: 0 0 8px; font-size: .95rem; font-weight: 700; color: #0f172a; line-height: 1.3; }
.seq-auto-card .seq-ft { font-size: 12px; color: #64748b; font-family: ui-monospace, monospace; margin: 0 0 8px; word-break: break-all; }
.seq-auto-card .seq-desc { font-size: .8rem; color: #64748b; line-height: 1.45; margin: 0 0 12px; min-height: 2.8em; }
.seq-auto-meta { font-size: 11px; color: #94a3b8; margin-bottom: 12px; }
.seq-auto-card-foot { display: flex; gap: 8px; flex-wrap: wrap; padding-top: 12px; border-top: 1px solid #f1f5f9; }
.seq-auto-card-foot a {
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px;
    font-size: .78rem; font-weight: 700; text-decoration: none; background: #2563eb; color: #fff;
}
.seq-auto-card-foot a:hover { background: #1d4ed8; color: #fff; }

.sequences-flash { margin: 0 0 20px; padding: 12px 16px; border-radius: 10px; font-size: 14px; font-weight: 600; }
.sequences-flash--ok { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.sequences-flash--err { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

.seq-auto-empty {
    grid-column: 1 / -1; text-align: center; padding: 36px 20px; color: #94a3b8; background: #f8fafc;
    border: 1px dashed #e2e8f0; border-radius: 14px; font-size: .88rem;
}

@media (max-width: 900px) {
    .seq-auto-layout { grid-template-columns: 1fr; }
    .seq-auto-aside { position: static; }
    .seq-auto-nav { flex-direction: row; flex-wrap: wrap; }
    .seq-auto-nav button { flex: 1 1 calc(50% - 6px); min-width: 160px; }
}
</style>

<div class="seq-auto-page">
    <header class="seq-auto-hero">
        <div class="seq-auto-hero-badge">Automations formulaires</div>
        <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?></p>
        <p class="seq-auto-hero-note">
            Les <strong>7 scénarios prioritaires</strong> ci-dessous sont remplis avec des e-mails prêts à l’emploi (relances J+0, J+1…).
            Planifiez le cron <code style="background:rgba(0,0,0,.2);padding:2px 6px;border-radius:4px;">cron/email_sequences.php</code> pour l’envoi espacé.
        </p>
        <nav class="seq-filter-tabs" aria-label="Filtrer par type de séquence">
            <?php foreach ($filterTabs as $key => $ft): ?>
                <a href="<?= htmlspecialchars($baseQ . $ft['q'], ENT_QUOTES, 'UTF-8') ?>"
                   class="<?= $seqFilter === $key ? 'is-active' : '' ?>"><?= htmlspecialchars($ft['label'], ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="seq-auto-actions">
            <?php if ($csrfSync !== ''): ?>
            <form method="post" action="/admin?module=email-sequences&action=sync_priority" style="display:inline;margin:0;padding:0;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfSync, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="return_filter" value="automatic">
                <button type="submit" class="hub-btn hub-btn-ghost" title="Réaligner les 7 scénarios sur les modèles fournis">
                    <i class="fas fa-rotate"></i> Réappliquer les modèles
                </button>
            </form>
            <?php endif; ?>
            <a href="/admin?module=email-sequences&action=new" class="hub-btn hub-btn-primary">
                <i class="fas fa-plus"></i> Nouvelle séquence
            </a>
        </div>
    </header>

    <?php
    if (!empty($_SESSION['success'])) {
        $sf = (string) $_SESSION['success'];
        unset($_SESSION['success']);
        echo '<div class="sequences-flash sequences-flash--ok" role="status">' . htmlspecialchars($sf) . '</div>';
    }
    if (!empty($_SESSION['error'])) {
        $ef = (string) $_SESSION['error'];
        unset($_SESSION['error']);
        echo '<div class="sequences-flash sequences-flash--err" role="alert">' . htmlspecialchars($ef) . '</div>';
    }
    ?>

    <div class="seq-auto-layout">
        <aside class="seq-auto-aside" aria-label="Scénarios">
            <div class="seq-auto-aside-title">Choisir un déclencheur</div>
            <div class="seq-auto-nav" id="seqAutoNav">
                <button type="button" class="is-active" data-trigger="all" onclick="seqAutoFilter('all', this)">
                    <span class="seq-auto-ico"><i class="fas fa-layer-group" aria-hidden="true"></i></span>
                    <span class="seq-auto-nav-body">
                        <span class="seq-auto-nav-label">Toutes les séquences auto</span>
                        <span class="seq-auto-nav-desc"><?= count($autoSequences) ?> séquence(s) au total</span>
                    </span>
                </button>
                <?php foreach ($triggers as $ft):
                    $def = $presets[$ft] ?? [];
                    $label = (string) ($def['name'] ?? $ft);
                    $desc = (string) ($def['description'] ?? '');
                    if (mb_strlen($desc) > 72) {
                        $desc = mb_substr($desc, 0, 69) . '…';
                    }
                    $pt = $phShow[$ft] ?? null;
                    $st = (string) ($pt['status'] ?? '—');
                    $ico = $triggerStyle[$ft] ?? ['icon' => 'fa-bolt', 'color' => '#64748b'];
                    $nSeq = count($byTrigger[$ft] ?? []);
                ?>
                <button type="button" data-trigger="<?= htmlspecialchars($ft, ENT_QUOTES, 'UTF-8') ?>" onclick="seqAutoFilter('<?= htmlspecialchars($ft, ENT_QUOTES, 'UTF-8') ?>', this)">
                    <span class="seq-auto-ico" style="color:<?= htmlspecialchars($ico['color'], ENT_QUOTES, 'UTF-8') ?>;"><i class="fas <?= htmlspecialchars($ico['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i></span>
                    <span class="seq-auto-nav-body">
                        <span class="seq-auto-nav-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="seq-auto-nav-desc"><?= htmlspecialchars($desc !== '' ? $desc : ($nSeq . ' séquence(s)'), ENT_QUOTES, 'UTF-8') ?></span>
                    </span>
                    <span class="seq-auto-pill <?= htmlspecialchars($healthClass($st), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($st, ENT_QUOTES, 'UTF-8') ?></span>
                </button>
                <?php endforeach; ?>
                <?php if (count($otherSeqs) > 0): ?>
                <button type="button" data-trigger="__other__" onclick="seqAutoFilter('__other__', this)">
                    <span class="seq-auto-ico"><i class="fas fa-ellipsis-h" aria-hidden="true"></i></span>
                    <span class="seq-auto-nav-body">
                        <span class="seq-auto-nav-label">Autres déclencheurs</span>
                        <span class="seq-auto-nav-desc">Hors les 7 scénarios prioritaires</span>
                    </span>
                    <span class="seq-auto-pill seq-auto-pill--neu"><?= count($otherSeqs) ?></span>
                </button>
                <?php endif; ?>
            </div>
        </aside>

        <div>
            <div class="seq-auto-main-head">
                <h2 id="seqAutoMainTitle">Toutes les séquences automatiques</h2>
                <p id="seqAutoMainSub">Cartes cliquables vers l’éditeur — chaque scénario contient plusieurs e-mails espacés dans le temps.</p>
            </div>
            <div class="seq-auto-grid" id="seqAutoGrid">
                <?php if (count($autoSequences) === 0): ?>
                    <div class="seq-auto-empty">
                        Aucune séquence automatique. Les modèles par défaut sont créés au chargement de cette page ; sinon utilisez « Nouvelle séquence ».
                    </div>
                <?php else: ?>
                    <?php foreach ($autoSequences as $seq):
                        $ft = (string) ($seq['form_trigger'] ?? '');
                        $isOther = $ft === '' || !in_array($ft, $triggers, true);
                        $dataTrig = $isOther ? '__other__' : $ft;
                        $ico = $triggerStyle[$ft] ?? ['icon' => 'fa-envelope-open-text', 'color' => '#64748b'];
                        $hb = class_exists(EmailSequencePriorityService::class, false)
                            ? EmailSequencePriorityService::badgeForRow($seq, $priorityHealth)
                            : ['label' => '—', 'class' => 'ph-neutral'];
                        $hbPill = match ($hb['class']) {
                            'ph-ok' => 'seq-auto-pill--ok',
                            'ph-miss' => 'seq-auto-pill--miss',
                            'ph-dup' => 'seq-auto-pill--dup',
                            'ph-inc' => 'seq-auto-pill--inc',
                            'ph-dim', 'ph-neutral' => 'seq-auto-pill--neu',
                            default => 'seq-auto-pill--neu',
                        };
                        $stClass = $seq['status'] === 'active' ? ['bg' => '#dbeafe', 'fg' => '#1d4ed8', 'lb' => 'Actif'] : ['bg' => '#fee2e2', 'fg' => '#dc2626', 'lb' => 'Inactif'];
                        $steps = (int) ($seq['email_steps'] ?? 0);
                    ?>
                <div class="seq-auto-card" data-trigger="<?= htmlspecialchars($dataTrig, ENT_QUOTES, 'UTF-8') ?>" data-ft="<?= htmlspecialchars($ft, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="seq-auto-card-bar" style="background:linear-gradient(90deg, <?= htmlspecialchars($ico['color'], ENT_QUOTES, 'UTF-8') ?>, #1a3a5c);"></div>
                    <div class="seq-auto-card-in">
                        <div class="seq-auto-card-badges">
                            <span class="seq-auto-badge-st" style="background:<?= $stClass['bg'] ?>;color:<?= $stClass['fg'] ?>;"><?= htmlspecialchars($stClass['lb'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="seq-auto-pill <?= htmlspecialchars($hbPill, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hb['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <h3><?= htmlspecialchars((string) $seq['name']) ?></h3>
                        <?php if ($ft !== ''): ?>
                        <p class="seq-ft"><code><?= htmlspecialchars($ft) ?></code></p>
                        <?php endif; ?>
                        <p class="seq-desc"><?= htmlspecialchars(mb_substr((string)($seq['description'] ?? ''), 0, 160)) ?><?= mb_strlen((string)($seq['description'] ?? '')) > 160 ? '…' : '' ?></p>
                        <div class="seq-auto-meta"><?= (int) $steps ?> e-mail(s) · créée le <?= date('d/m/Y', strtotime((string)($seq['created_at'] ?? 'now'))) ?></div>
                        <div class="seq-auto-card-foot">
                            <a href="/admin?module=email-sequences&action=edit&id=<?= (int) $seq['id'] ?>"><i class="fas fa-pen"></i> Éditer la séquence</a>
                        </div>
                    </div>
                </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
function seqAutoFilter(trig, btn) {
    document.querySelectorAll('#seqAutoNav button').forEach(b => b.classList.remove('is-active'));
    btn.classList.add('is-active');
    const cards = document.querySelectorAll('.seq-auto-card');
    let n = 0;
    cards.forEach(c => {
        const d = c.getAttribute('data-trigger') || '';
        const show = trig === 'all' || d === trig;
        c.style.display = show ? '' : 'none';
        if (show) n++;
    });
    const t = document.getElementById('seqAutoMainTitle');
    const s = document.getElementById('seqAutoMainSub');
    if (trig === 'all') {
        t.textContent = 'Toutes les séquences automatiques';
        s.textContent = n + ' affichée(s). Cliquez sur Éditer pour modifier les e-mails et les délais.';
    } else if (trig === '__other__') {
        t.textContent = 'Autres déclencheurs';
        s.textContent = n + ' séquence(s) hors liste prioritaire.';
    } else {
        t.textContent = 'Déclencheur : ' + trig;
        s.textContent = n + ' séquence(s) — une seule devrait être active par déclencheur.';
    }
}
</script>
