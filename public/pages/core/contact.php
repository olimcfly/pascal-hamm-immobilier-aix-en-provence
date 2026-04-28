<?php
declare(strict_types=1);

$extraCss = ['/assets/css/contact.css'];

$siteSettings = $siteSettings ?? [];

$advisorName  = $advisorName ?? ($siteSettings['advisor_name'] ?? ($_ENV['ADVISOR_NAME'] ?? 'Votre conseiller'));
$advisorCity  = $advisorCity ?? ($siteSettings['city'] ?? ($_ENV['APP_CITY'] ?? 'Votre ville'));
$advisorPhone = $advisorPhone ?? ($siteSettings['phone'] ?? ($_ENV['APP_PHONE'] ?? ''));
$advisorEmail = $advisorEmail ?? ($siteSettings['email'] ?? ($_ENV['APP_EMAIL'] ?? ''));
$advisorAddress = $siteSettings['address'] ?? (defined('APP_ADDRESS') ? APP_ADDRESS : $advisorCity);
$advisorTagline = trim((string) ($siteSettings['advisor_tagline'] ?? ''));
if ($advisorTagline === '' && defined('APP_ADVISOR_TAGLINE')) {
    $advisorTagline = (string) APP_ADVISOR_TAGLINE;
}
$advisorRsac = trim((string) ($siteSettings['advisor_rsac'] ?? ''));
if ($advisorRsac === '' && defined('ADVISOR_RSAC')) {
    $advisorRsac = (string) ADVISOR_RSAC;
}

$contactFormError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email   = trim((string)($_POST['email'] ?? ''));
    $prenom  = trim((string)($_POST['prenom'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if (
        $email !== '' &&
        $prenom !== '' &&
        filter_var($email, FILTER_VALIDATE_EMAIL) &&
        $message !== ''
    ) {
        LeadService::capture([
            'source_type' => LeadService::SOURCE_CONTACT,
            'pipeline'    => LeadService::SOURCE_CONTACT,
            'stage'       => 'a_traiter',
            'first_name'  => $prenom,
            'last_name'   => trim((string)($_POST['nom'] ?? '')),
            'email'       => $email,
            'phone'       => trim((string)($_POST['telephone'] ?? '')),
            'intent'      => trim((string)($_POST['sujet'] ?? 'Contact')),
            'notes'       => $message,
            'consent'     => !empty($_POST['rgpd']),
        ]);

        // Track conversion
        ConversionTrackingService::track(
            ConversionTrackingService::TYPE_CONTACT_FORM,
            email: $email,
            firstName: $prenom,
            phone: trim((string)($_POST['telephone'] ?? null)),
            metadata: [
                'sujet' => trim((string)($_POST['sujet'] ?? 'Contact')),
                'message_length' => strlen($message),
            ]
        );

        redirect('/merci');
    } else {
        $contactFormError = 'Merci de remplir correctement les champs obligatoires.';
    }
}

$pageTitle = "Contact — {$advisorName} | Immobilier {$advisorCity}";
$metaDesc  = "Contactez {$advisorName}, conseiller immobilier à {$advisorCity}. Réponse rapide et accompagnement personnalisé.";

$contactTitle     = $siteSettings['contact_title'] ?? "Contactez {$advisorName}";
$contactSubtitle  = $siteSettings['contact_subtitle'] ?? "Je vous réponds personnellement sous 24h.";
$contactFormTitle = $siteSettings['contact_form_title'] ?? "Envoyez-moi un message";

$contactPhoneHref = preg_replace('/\s+/', '', (string)$advisorPhone);
$mapEmbed = $siteSettings['contact_map_embed'] ?? '';
$advisorFirstName = trim((string) preg_replace('/\s+.*$/u', '', (string) $advisorName));
if ($advisorFirstName === '') {
    $advisorFirstName = $advisorName;
}
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/">Accueil</a><span>Contact</span>
        </nav>
        <h1><?= e($contactTitle) ?></h1>
        <p><?= e($contactSubtitle) ?></p>
        <?php if ($advisorTagline !== ''): ?>
            <p class="page-header__intro"><?= e($advisorTagline) ?></p>
        <?php endif; ?>
    </div>
</div>

<section class="section section--contact">
    <div class="container">
        <div class="contact-layout">

            <div class="contact-form-wrap" data-animate>
                <div class="contact-form-box">
                    <div class="contact-form-box__head">
                        <span class="section-label">Formulaire</span>
                        <h2 id="contact-form-title"><?= e($contactFormTitle) ?></h2>
                        <p class="contact-form-box__lede">Les champs marqués d’une <span class="req">*</span> sont obligatoires. Je vous recontacte en général le jour même.</p>
                    </div>

                <?php if ($contactFormError): ?>
                    <div class="contact-alert contact-alert--error" role="alert">
                        <svg class="contact-alert__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span><?= e($contactFormError) ?></span>
                    </div>
                <?php endif; ?>

                <form class="contact-form" method="POST" action="/contact" aria-labelledby="contact-form-title" novalidate>
                    <?= csrfField() ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="contact-prenom">Prénom <span class="req">*</span></label>
                            <input class="form-control" type="text" name="prenom" id="contact-prenom" placeholder="Votre prénom" required autocomplete="given-name" value="">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contact-nom">Nom <span class="req">*</span></label>
                            <input class="form-control" type="text" name="nom" id="contact-nom" placeholder="Votre nom" required autocomplete="family-name" value="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact-email">E-mail <span class="req">*</span></label>
                        <input class="form-control" type="email" name="email" id="contact-email" placeholder="nom@exemple.fr" required autocomplete="email" inputmode="email" value="">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact-tel">Téléphone</label>
                        <input class="form-control" type="tel" name="telephone" id="contact-tel" placeholder="+33 6 12 34 56 78" autocomplete="tel" inputmode="tel" value="">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact-sujet">Sujet</label>
                        <div class="contact-select-wrap">
                            <select class="form-control form-control--select" name="sujet" id="contact-sujet">
                                <option value="Contact">Prise de contact</option>
                                <option value="Achat">Achat d’un bien</option>
                                <option value="Vente">Vente de mon bien</option>
                                <option value="Estimation">Estimation / estimation gratuite</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact-message">Message <span class="req">*</span></label>
                        <textarea class="form-control" name="message" id="contact-message" placeholder="Décrivez votre projet, vos questions…" required rows="5"></textarea>
                    </div>

                    <div class="contact-rgpd">
                        <input type="checkbox" name="rgpd" id="contact-rgpd" required>
                        <label for="contact-rgpd">J’accepte la <a href="/politique-confidentialite" class="contact-inline-link" target="_blank" rel="noopener">politique de confidentialité</a> <span class="req">*</span></label>
                    </div>

                    <button type="submit" class="btn btn--primary btn--lg btn--full contact-submit">
                        <span>Envoyer le message</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </form>
            </div>
            </div>

            <aside class="contact-side" data-animate>
                <div class="contact-card contact-card--highlight">
                    <div class="contact-card--highlight__icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 6v6l3 2"/></svg>
                    </div>
                    <h3 class="contact-card__title">Réactivité</h3>
                    <p class="contact-card__value">Sous 24h</p>
                    <p class="contact-card__text">Un échange direct avec <?= e($advisorFirstName) ?>, sans intermédiaire.</p>
                </div>

                <div class="contact-card contact-card--coords">
                    <h3 class="contact-card__title">Coordonnées</h3>
                    <ul class="contact-details">
                        <li class="contact-details__row">
                            <span class="contact-details__icon" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </span>
                            <span class="contact-details__text"><?= e($advisorAddress) ?></span>
                        </li>
                        <?php if ($advisorPhone): ?>
                        <li class="contact-details__row">
                            <span class="contact-details__icon" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 14a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 3.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 10a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </span>
                            <a class="contact-details__link" href="tel:<?= e($contactPhoneHref) ?>"><?= e($advisorPhone) ?></a>
                        </li>
                        <?php endif; ?>
                        <li class="contact-details__row">
                            <span class="contact-details__icon" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </span>
                            <a class="contact-details__link" href="mailto:<?= e($advisorEmail) ?>"><?= e($advisorEmail) ?></a>
                        </li>
                    </ul>
                    <?php if ($advisorRsac !== ''): ?>
                        <p class="contact-details__rsac">RSAC&nbsp;: <?= e($advisorRsac) ?></p>
                    <?php endif; ?>
                </div>

                <div class="contact-card">
                    <h3 class="contact-card__title">Disponibilité</h3>
                    <ul class="contact-bullets">
                        <li>
                            <span class="contact-bullets__icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span>Réponse personnalisée rapidement</span>
                        </li>
                        <li>
                            <span class="contact-bullets__icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span>Rendez-vous sur Aix &amp; Pays d’Aix</span>
                        </li>
                    </ul>
                </div>

                <div class="contact-card contact-card--map">
                    <div class="contact-map__head">
                        <div class="contact-map__head-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div class="contact-map__head-text">
                            <h3 class="contact-map__title">Secteur d’intervention</h3>
                            <p class="contact-map__sub"><?= e($advisorCity) ?>, Pays d’Aix (13)</p>
                        </div>
                    </div>
                    <?php if ($mapEmbed): ?>
                        <div class="contact-map__frame contact-map__frame--embed"><?= $mapEmbed ?></div>
                    <?php else: ?>
                        <div class="contact-map__frame">
                            <iframe title="Carte — <?= e($advisorCity) ?>"
                                loading="lazy"
                                src="https://maps.google.com/maps?q=<?= urlencode($advisorCity) ?>&amp;output=embed"
                                allowfullscreen
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>

        </div>
    </div>
</section>