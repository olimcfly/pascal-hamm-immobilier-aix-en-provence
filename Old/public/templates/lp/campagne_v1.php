<?php
// public/templates/lp/campagne_v1.php
// Template : Campagne Premium — Bleu dominant, blanc, conversion
// Usage    : Google Ads, Facebook Ads, Social (estimation, contact, guide)
// Form     : prénom, nom, email, téléphone, message (optionnel), consentement

$h1         = htmlspecialchars($funnel['h1'] ?? 'Vendez votre bien au meilleur prix à Aix-en-Provence');
$promise    = htmlspecialchars($funnel['promise'] ?? 'Obtenez une estimation gratuite et sans engagement en 48h');
$ctaLabel   = htmlspecialchars($funnel['cta_label'] ?? 'Recevoir mon estimation gratuite');
$ville      = htmlspecialchars($funnel['ville'] ?? '');
$slug       = htmlspecialchars($funnel['slug'] ?? '');
$formAction = rtrim(APP_URL, '/') . '/lp/' . $slug . '/submit';
$formType   = $funnel['form_type'] ?? 'guide';
$advisorName = htmlspecialchars(setting('advisor_name', defined('ADVISOR_NAME') ? ADVISOR_NAME : ''));
$advisorPhone = htmlspecialchars(setting('advisor_phone', ''));

// Bénéfices configurables
$benefits = [
    ['icon' => 'fa-chart-line',       'title' => 'Prix du marché en temps réel',   'text' => 'Accès aux dernières transactions dans votre quartier pour positionner votre bien au juste prix.'],
    ['icon' => 'fa-handshake',        'title' => 'Accompagnement personnalisé',     'text' => 'Un conseiller dédié vous guide de l\'évaluation à la signature chez le notaire.'],
    ['icon' => 'fa-bolt',             'title' => 'Vente rapide et sécurisée',       'text' => 'Notre réseau d\'acquéreurs qualifiés permet de conclure en moyenne en 45 jours.'],
    ['icon' => 'fa-shield-halved',    'title' => 'Zéro frais si pas de vente',      'text' => 'Vous ne payez des honoraires qu\'à la signature — aucun risque financier pour vous.'],
    ['icon' => 'fa-magnifying-glass', 'title' => 'Diagnostic complet offert',       'text' => 'Analyse de votre bien, du marché local et des tendances pour une stratégie optimale.'],
    ['icon' => 'fa-calendar-check',   'title' => 'Disponible 7j/7',                'text' => 'Votre conseiller est joignable en semaine et le week-end pour s\'adapter à vos contraintes.'],
];

// Message de formulaire selon le type
$formTitle  = match ($formType) {
    'estimation' => 'Demandez votre estimation gratuite',
    'rdv'        => 'Prenez rendez-vous',
    'contact'    => 'Contactez votre conseiller',
    default      => 'Recevez votre guide maintenant',
};
$messagePlaceholder = match ($formType) {
    'estimation' => 'Décrivez brièvement votre bien (type, surface, étage…)',
    'rdv'        => 'Précisez vos disponibilités ou votre projet',
    default      => 'Votre question ou projet (optionnel)',
};
?>

<style>
/* ── Reset minimal LP ─────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
.cv1-container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }

/* ── HERO ────────────────────────────────────── */
.cv1-hero {
    background: linear-gradient(135deg, #0f2d5a 0%, #1e4d8c 60%, #1a5cb8 100%);
    color: #fff;
    padding: 60px 0 70px;
    position: relative;
    overflow: hidden;
}
.cv1-hero::before {
    content: '';
    position: absolute;
    top: -80px; right: -80px;
    width: 400px; height: 400px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
    pointer-events: none;
}
.cv1-hero::after {
    content: '';
    position: absolute;
    bottom: -120px; left: -60px;
    width: 500px; height: 500px;
    border-radius: 50%;
    background: rgba(255,255,255,.04);
    pointer-events: none;
}
.cv1-hero-grid {
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 48px;
    align-items: center;
    position: relative;
    z-index: 1;
}
.cv1-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.3);
    color: #e0f2fe;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 999px;
    margin-bottom: 20px;
}
.cv1-hero h1 {
    font-size: clamp(1.7rem, 4vw, 2.6rem);
    font-weight: 800;
    line-height: 1.18;
    color: #fff;
    margin-bottom: 16px;
}
.cv1-hero-promise {
    font-size: 1.05rem;
    color: #bfdbfe;
    margin-bottom: 24px;
    line-height: 1.5;
}
.cv1-hero-trust {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}
.cv1-trust-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .83rem;
    color: #dbeafe;
}
.cv1-trust-item i { color: #86efac; }

/* ── FORMULAIRE hero ─────────────────────────── */
.cv1-form-card {
    background: #fff;
    border-radius: 16px;
    padding: 28px 24px 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
}
.cv1-form-title {
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
}
.cv1-form-sub {
    font-size: .8rem;
    color: #64748b;
    margin-bottom: 16px;
}
.cv1-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.cv1-form-group { display: flex; flex-direction: column; gap: 4px; margin-bottom: 10px; }
.cv1-form-group label { font-size: .75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .04em; }
.cv1-form-group input,
.cv1-form-group textarea {
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 9px 12px;
    font-size: .9rem;
    color: #0f172a;
    transition: border-color .15s;
    width: 100%;
    font-family: inherit;
}
.cv1-form-group input:focus,
.cv1-form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
}
.cv1-form-group textarea { resize: vertical; min-height: 72px; }
.cv1-consent {
    display: flex;
    gap: 8px;
    align-items: flex-start;
    margin-bottom: 14px;
}
.cv1-consent input[type=checkbox] { margin-top: 2px; flex-shrink: 0; }
.cv1-consent-text { font-size: .76rem; color: #64748b; line-height: 1.4; }
.cv1-consent-text a { color: #3b82f6; }
.cv1-btn-cta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff;
    font-size: .95rem;
    font-weight: 800;
    padding: 14px 20px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    letter-spacing: .02em;
    box-shadow: 0 4px 16px rgba(217,119,6,.35);
    transition: transform .15s, box-shadow .15s;
    text-decoration: none;
}
.cv1-btn-cta:hover { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(217,119,6,.45); }
.cv1-form-security {
    text-align: center;
    font-size: .72rem;
    color: #94a3b8;
    margin-top: 10px;
}
.lp-form__honey { position: absolute; left: -9999px; opacity: 0; height: 0; }

/* ── TRUST BAR ───────────────────────────────── */
.cv1-trustbar {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    padding: 16px 0;
}
.cv1-trustbar-inner {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 24px;
}
.cv1-trustbar-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .83rem;
    color: #475569;
    font-weight: 600;
}
.cv1-trustbar-item i { color: #3b82f6; }

/* ── BÉNÉFICES ───────────────────────────────── */
.cv1-benefits { padding: 64px 0; background: #fff; }
.cv1-section-tag {
    display: inline-block;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 999px;
    margin-bottom: 12px;
}
.cv1-section-h2 {
    font-size: clamp(1.4rem, 3vw, 2rem);
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 8px;
}
.cv1-section-sub { font-size: 1rem; color: #64748b; margin-bottom: 40px; }
.cv1-benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}
.cv1-benefit-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
}
.cv1-benefit-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #eff6ff;
    color: #1d4ed8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.cv1-benefit-card h4 { font-size: .9rem; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
.cv1-benefit-card p { font-size: .82rem; color: #64748b; line-height: 1.4; }

/* ── PROOF ───────────────────────────────────── */
.cv1-proof {
    padding: 64px 0;
    background: linear-gradient(135deg, #0f2d5a 0%, #1e4d8c 100%);
    color: #fff;
}
.cv1-proof-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 36px;
}
.cv1-testimonial {
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 12px;
    padding: 20px;
}
.cv1-testimonial-stars { color: #fbbf24; font-size: .85rem; margin-bottom: 10px; }
.cv1-testimonial-quote { font-size: .88rem; color: #e0f2fe; line-height: 1.5; margin-bottom: 12px; font-style: italic; }
.cv1-testimonial-author { font-size: .8rem; font-weight: 700; color: #bfdbfe; }
.cv1-stats-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 36px;
    margin-top: 40px;
}
.cv1-stat { text-align: center; }
.cv1-stat-num { font-size: 2.2rem; font-weight: 800; color: #fbbf24; }
.cv1-stat-label { font-size: .82rem; color: #93c5fd; margin-top: 2px; }

/* ── CTA SECTION ─────────────────────────────── */
.cv1-cta-section {
    padding: 64px 0;
    background: #f8fafc;
    text-align: center;
}
.cv1-cta-section h2 { font-size: clamp(1.4rem, 3vw, 2rem); font-weight: 800; color: #0f172a; margin-bottom: 10px; }
.cv1-cta-section p { font-size: 1rem; color: #64748b; margin-bottom: 28px; max-width: 520px; margin-left: auto; margin-right: auto; }
.cv1-btn-cta--outline {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #fff;
    color: #1e3a5f;
    border: 2px solid #1e3a5f;
    font-size: .95rem;
    font-weight: 700;
    padding: 13px 28px;
    border-radius: 10px;
    text-decoration: none;
    transition: background .15s, color .15s;
}
.cv1-btn-cta--outline:hover { background: #1e3a5f; color: #fff; }

/* ── FAQ ─────────────────────────────────────── */
.cv1-faq { padding: 60px 0; background: #fff; }
.cv1-faq-list { max-width: 680px; margin: 36px auto 0; }
.cv1-faq-item { border-bottom: 1px solid #e2e8f0; }
.cv1-faq-q {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    cursor: pointer;
    font-weight: 700;
    font-size: .92rem;
    color: #0f172a;
    gap: 12px;
}
.cv1-faq-q i { color: #94a3b8; font-size: .8rem; transition: transform .2s; flex-shrink: 0; }
.cv1-faq-item.open .cv1-faq-q i { transform: rotate(180deg); }
.cv1-faq-a { font-size: .88rem; color: #475569; line-height: 1.55; padding: 0 0 14px; display: none; }
.cv1-faq-item.open .cv1-faq-a { display: block; }

/* ── ADVISOR ─────────────────────────────────── */
.cv1-advisor { padding: 48px 0; background: #eff6ff; }
.cv1-advisor-inner {
    display: flex;
    gap: 24px;
    align-items: center;
    max-width: 700px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid #dbeafe;
    border-radius: 16px;
    padding: 24px 28px;
}
.cv1-advisor-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    background: #e0f2fe;
}
.cv1-advisor-name { font-size: 1rem; font-weight: 800; color: #0f172a; }
.cv1-advisor-title { font-size: .82rem; color: #1d4ed8; font-weight: 600; margin-bottom: 6px; }
.cv1-advisor-bio { font-size: .85rem; color: #475569; line-height: 1.45; }

/* ── RESPONSIVE ──────────────────────────────── */
@media (max-width: 780px) {
    .cv1-hero-grid { grid-template-columns: 1fr; gap: 32px; }
    .cv1-form-row { grid-template-columns: 1fr; }
    .cv1-advisor-inner { flex-direction: column; text-align: center; }
}
</style>

<!-- ─── HERO ───────────────────────────────────── -->
<section class="cv1-hero">
    <div class="cv1-container">
        <div class="cv1-hero-grid">

            <!-- Content -->
            <div class="cv1-hero-content">
                <div class="cv1-hero-badge">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= $ville ? "Expert local — $ville" : 'Expert immobilier local' ?>
                </div>
                <h1><?= $h1 ?></h1>
                <p class="cv1-hero-promise"><?= $promise ?></p>
                <div class="cv1-hero-trust">
                    <span class="cv1-trust-item"><i class="fas fa-check-circle"></i> Estimation gratuite</span>
                    <span class="cv1-trust-item"><i class="fas fa-check-circle"></i> Sans engagement</span>
                    <span class="cv1-trust-item"><i class="fas fa-check-circle"></i> Réponse sous 48h</span>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="cv1-form-card" id="form-top">
                <div class="cv1-form-title"><?= $formTitle ?></div>
                <div class="cv1-form-sub">Remplissez le formulaire — c'est gratuit et sans engagement.</div>

                <form action="<?= $formAction ?>" method="POST" id="lp-form">
                    <!-- Honeypot anti-spam -->
                    <input type="text" name="website" class="lp-form__honey" tabindex="-1" autocomplete="off">

                    <div class="cv1-form-row">
                        <div class="cv1-form-group">
                            <label for="cv1_firstname">Prénom *</label>
                            <input type="text" id="cv1_firstname" name="first_name" placeholder="Marie"
                                   required autocomplete="given-name">
                        </div>
                        <div class="cv1-form-group">
                            <label for="cv1_lastname">Nom *</label>
                            <input type="text" id="cv1_lastname" name="last_name" placeholder="Dupont"
                                   required autocomplete="family-name">
                        </div>
                    </div>

                    <div class="cv1-form-group">
                        <label for="cv1_email">Email *</label>
                        <input type="email" id="cv1_email" name="email" placeholder="marie@exemple.fr"
                               required autocomplete="email">
                    </div>

                    <div class="cv1-form-group">
                        <label for="cv1_phone">Téléphone</label>
                        <input type="tel" id="cv1_phone" name="phone" placeholder="06 xx xx xx xx"
                               autocomplete="tel">
                    </div>

                    <div class="cv1-form-group">
                        <label for="cv1_message">Message (optionnel)</label>
                        <textarea id="cv1_message" name="message"
                                  placeholder="<?= htmlspecialchars($messagePlaceholder) ?>"></textarea>
                    </div>

                    <label class="cv1-consent">
                        <input type="checkbox" name="consent" value="1" required>
                        <span class="cv1-consent-text">
                            J'accepte d'être recontacté(e) par email et téléphone concernant mon projet immobilier.
                            Voir notre <a href="<?= rtrim(APP_URL, '/') ?>/politique-de-confidentialite" target="_blank">politique de confidentialité</a>.
                            Désabonnement à tout moment.
                        </span>
                    </label>

                    <button type="submit" class="cv1-btn-cta" data-track-cta>
                        <i class="fas fa-paper-plane"></i><?= $ctaLabel ?>
                    </button>

                    <p class="cv1-form-security">
                        <i class="fas fa-lock"></i> Données sécurisées — jamais vendues ni partagées
                    </p>
                </form>
            </div>

        </div>
    </div>
</section>

<!-- ─── TRUST BAR ──────────────────────────────── -->
<div class="cv1-trustbar">
    <div class="cv1-container">
        <div class="cv1-trustbar-inner">
            <span class="cv1-trustbar-item"><i class="fas fa-shield-halved"></i> RGPD conforme</span>
            <span class="cv1-trustbar-item"><i class="fas fa-bolt"></i> Réponse rapide</span>
            <span class="cv1-trustbar-item"><i class="fas fa-user-tie"></i> Conseiller certifié</span>
            <span class="cv1-trustbar-item"><i class="fas fa-star"></i> +200 clients accompagnés</span>
            <?php if ($advisorPhone): ?>
            <span class="cv1-trustbar-item"><i class="fas fa-phone"></i> <?= $advisorPhone ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ─── BÉNÉFICES ─────────────────────────────── -->
<section class="cv1-benefits">
    <div class="cv1-container">
        <div style="text-align:center;margin-bottom:0">
            <span class="cv1-section-tag">Nos engagements</span>
            <h2 class="cv1-section-h2">Pourquoi nous faire confiance ?</h2>
            <p class="cv1-section-sub">
                <?= $ville ? "Expert du marché de $ville" : 'Expert du marché local' ?> depuis plus de 10 ans.
            </p>
        </div>
        <div class="cv1-benefits-grid">
            <?php foreach ($benefits as $b): ?>
            <div class="cv1-benefit-card">
                <div class="cv1-benefit-icon"><i class="fas <?= htmlspecialchars($b['icon']) ?>"></i></div>
                <div>
                    <h4><?= htmlspecialchars($b['title']) ?></h4>
                    <p><?= htmlspecialchars($b['text']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:36px">
            <a href="#form-top" class="cv1-btn-cta" style="max-width:380px;display:inline-flex" data-track-cta>
                <i class="fas fa-arrow-up"></i><?= $ctaLabel ?>
            </a>
        </div>
    </div>
</section>

<!-- ─── PROOF / TÉMOIGNAGES ───────────────────── -->
<section class="cv1-proof">
    <div class="cv1-container">
        <div style="text-align:center">
            <span class="cv1-section-tag" style="background:rgba(255,255,255,.15);color:#bfdbfe">Témoignages</span>
            <h2 class="cv1-section-h2" style="color:#fff;margin-top:8px">Ce que disent nos clients</h2>
        </div>
        <div class="cv1-proof-grid">
            <div class="cv1-testimonial">
                <div class="cv1-testimonial-stars">★★★★★</div>
                <p class="cv1-testimonial-quote">
                    "Estimation réaliste, vente conclue en 3 semaines. Pascal a su mettre en valeur notre appartement et nous a accompagnés jusqu'à la signature."
                </p>
                <span class="cv1-testimonial-author">— Isabelle M.<?= $ville ? ", $ville" : '' ?></span>
            </div>
            <div class="cv1-testimonial">
                <div class="cv1-testimonial-stars">★★★★★</div>
                <p class="cv1-testimonial-quote">
                    "Très professionnel, disponible et transparent sur les prix du marché. Je recommande sans hésiter pour tout projet immobilier."
                </p>
                <span class="cv1-testimonial-author">— Jean-Pierre V.<?= $ville ? ", $ville" : '' ?></span>
            </div>
            <div class="cv1-testimonial">
                <div class="cv1-testimonial-stars">★★★★★</div>
                <p class="cv1-testimonial-quote">
                    "Aucune mauvaise surprise, tout s'est passé comme prévu. Le meilleur conseiller que j'ai eu pour vendre mon bien."
                </p>
                <span class="cv1-testimonial-author">— Carole T.<?= $ville ? ", $ville" : '' ?></span>
            </div>
        </div>
        <div class="cv1-stats-row">
            <div class="cv1-stat">
                <div class="cv1-stat-num">97%</div>
                <div class="cv1-stat-label">clients satisfaits</div>
            </div>
            <div class="cv1-stat">
                <div class="cv1-stat-num">45j</div>
                <div class="cv1-stat-label">délai moyen de vente</div>
            </div>
            <div class="cv1-stat">
                <div class="cv1-stat-num">+200</div>
                <div class="cv1-stat-label">biens vendus</div>
            </div>
            <div class="cv1-stat">
                <div class="cv1-stat-num">10ans</div>
                <div class="cv1-stat-label">d'expertise locale</div>
            </div>
        </div>
    </div>
</section>

<!-- ─── ADVISOR ───────────────────────────────── -->
<?php if ($advisorName): ?>
<section class="cv1-advisor">
    <div class="cv1-container">
        <div class="cv1-advisor-inner">
            <img src="<?= rtrim(APP_URL, '/') ?>/assets/images/advisor-avatar.jpg"
                 alt="<?= $advisorName ?>"
                 class="cv1-advisor-avatar"
                 onerror="this.style.opacity=0">
            <div>
                <div class="cv1-advisor-name"><?= $advisorName ?></div>
                <div class="cv1-advisor-title">Conseiller immobilier<?= $ville ? " — expert $ville" : '' ?></div>
                <p class="cv1-advisor-bio">
                    Spécialiste du marché local, je vous accompagne à chaque étape de votre projet avec transparence et réactivité.
                    Mon objectif : vous obtenir le meilleur prix dans les meilleurs délais.
                </p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ─── FAQ ───────────────────────────────────── -->
<?php if (!empty($funnel['faq_json'])): ?>
<section class="cv1-faq">
    <div class="cv1-container">
        <div style="text-align:center">
            <span class="cv1-section-tag">FAQ</span>
            <h2 class="cv1-section-h2" style="margin-top:8px">Questions fréquentes</h2>
        </div>
        <div class="cv1-faq-list">
            <?php foreach ($funnel['faq_json'] as $faq): ?>
            <div class="cv1-faq-item">
                <div class="cv1-faq-q">
                    <?= htmlspecialchars($faq['q'] ?? '') ?>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="cv1-faq-a"><?= htmlspecialchars($faq['a'] ?? '') ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<script>
document.querySelectorAll('.cv1-faq-item').forEach(item => {
    item.querySelector('.cv1-faq-q').addEventListener('click', () => item.classList.toggle('open'));
});
</script>
<?php endif; ?>

<!-- ─── CTA FINAL ──────────────────────────────── -->
<section class="cv1-cta-section">
    <div class="cv1-container">
        <h2>Prêt à passer à l'action ?</h2>
        <p>Rejoignez les <?= $ville ? "propriétaires de $ville" : 'propriétaires' ?> qui nous font confiance pour vendre vite et au bon prix.</p>
        <a href="#form-top" class="cv1-btn-cta" style="max-width:400px;display:inline-flex" data-track-cta>
            <i class="fas fa-paper-plane"></i><?= $ctaLabel ?>
        </a>
        <?php if ($advisorPhone): ?>
        <p style="margin-top:16px;font-size:.85rem;color:#64748b">
            Ou appelez directement : <a href="tel:<?= preg_replace('/\s+/', '', $advisorPhone) ?>" style="color:#1d4ed8;font-weight:700"><?= $advisorPhone ?></a>
        </p>
        <?php endif; ?>
    </div>
</section>
