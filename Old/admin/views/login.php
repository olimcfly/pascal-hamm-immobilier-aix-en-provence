<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — <?= htmlspecialchars(defined('APP_NAME') ? APP_NAME : 'Administration') ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --clr-primary:     #1a3c5e;
            --clr-primary-lt:  #2558a0;
            --clr-accent:      #c9a84c;
            --clr-accent-lt:   #e8c76a;
            --clr-text:        #1a1a2e;
            --clr-text-muted:  #6b7280;
            --clr-bg:          #f8f7f4;
            --clr-white:       #ffffff;
            --clr-border:      #e5e0d8;
            --clr-danger:      #dc2626;
            --clr-success:     #16a34a;
            --radius:          8px;
            --radius-lg:       16px;
            --radius-xl:       24px;
            --shadow:          0 4px 16px rgba(0,0,0,.10);
            --shadow-sm:       0 1px 3px rgba(0,0,0,.08);
            --header-h:        72px;
            --font-display:    'Playfair Display', Georgia, serif;
            --font-body:       'Inter', system-ui, -apple-system, sans-serif;
            --transition:      0.25s ease;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { font-size: 16px; }

        body {
            font-family: var(--font-body);
            color: var(--clr-text);
            background: var(--clr-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        a { color: var(--clr-primary); text-decoration: none; transition: color var(--transition); }
        a:hover { color: var(--clr-primary-lt); }

        /* —— En-tête (rappel du site public) —— */
        .login-header {
            flex-shrink: 0;
            height: var(--header-h);
            background: var(--clr-white);
            border-bottom: 1px solid var(--clr-border);
            box-shadow: var(--shadow-sm);
        }
        .login-header__inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .login-header__brand {
            display: flex;
            align-items: center;
            gap: .65rem;
            color: var(--clr-primary);
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 1.15rem;
        }
        .login-header__brand:hover { color: var(--clr-primary-lt); }
        .login-header__brand-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius);
            background: linear-gradient(135deg, var(--clr-primary) 0%, var(--clr-primary-lt) 100%);
            display: grid;
            place-items: center;
            color: var(--clr-white);
            flex-shrink: 0;
        }
        .login-header__brand-icon svg { width: 20px; height: 20px; }
        .login-header__back {
            font-size: .875rem;
            font-weight: 500;
            color: var(--clr-text-muted);
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }
        .login-header__back:hover { color: var(--clr-primary); }

        /* —— Zone principale —— */
        .login-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem 3rem;
            background:
                radial-gradient(ellipse 90% 55% at 50% -15%, rgba(26, 60, 94, 0.09), transparent 55%),
                radial-gradient(ellipse 70% 45% at 100% 100%, rgba(201, 168, 76, 0.08), transparent 50%),
                var(--clr-bg);
        }

        .login-shell {
            width: 100%;
            max-width: 440px;
        }

        .login-intro {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-intro__label {
            display: inline-block;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--clr-accent);
            margin-bottom: .6rem;
        }
        .login-intro h1 {
            font-family: var(--font-display);
            font-size: clamp(1.65rem, 4vw, 2.1rem);
            font-weight: 600;
            color: var(--clr-primary);
            line-height: 1.2;
            margin-bottom: .4rem;
        }
        .login-intro p {
            font-size: .95rem;
            color: var(--clr-text-muted);
            line-height: 1.5;
        }

        .login-card {
            background: var(--clr-white);
            border: 1px solid var(--clr-border);
            border-radius: var(--radius-xl);
            padding: 2rem 2rem 2.25rem;
            box-shadow: var(--shadow);
        }

        .flash {
            display: flex;
            align-items: flex-start;
            gap: .5rem;
            padding: .9rem 1rem;
            border-radius: var(--radius);
            font-size: .875rem;
            line-height: 1.45;
            margin-bottom: 1.25rem;
        }
        .flash--error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .flash--success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .form-group { margin-bottom: 1.25rem; }
        .form-group:last-of-type { margin-bottom: 1rem; }

        .form-label {
            display: block;
            font-size: .875rem;
            font-weight: 600;
            color: var(--clr-primary);
            margin-bottom: .4rem;
        }

        .form-input {
            width: 100%;
            padding: .75rem 1rem;
            border: 1px solid var(--clr-border);
            border-radius: var(--radius);
            font-size: .95rem;
            color: var(--clr-text);
            background: var(--clr-white);
            transition: border-color var(--transition), box-shadow var(--transition);
            outline: none;
        }
        .form-input::placeholder { color: #9ca3af; }
        .form-input:hover { border-color: #d1c9bc; }
        .form-input:focus {
            border-color: var(--clr-primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--clr-primary) 18%, white);
        }

        .password-wrap { position: relative; }
        .password-wrap .form-input { padding-right: 2.75rem; }
        .password-toggle {
            position: absolute;
            right: .6rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--clr-text-muted);
            cursor: pointer;
            padding: .35rem;
            display: flex;
            align-items: center;
            border-radius: 6px;
            transition: color var(--transition), background var(--transition);
        }
        .password-toggle:hover {
            color: var(--clr-primary);
            background: rgba(26, 60, 94, 0.06);
        }

        .forgot-link {
            display: block;
            text-align: right;
            font-size: .8125rem;
            font-weight: 500;
            color: var(--clr-text-muted);
            margin-top: .45rem;
        }
        .forgot-link:hover { color: var(--clr-accent); }

        .btn-login {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 3rem;
            padding: .75rem 1.5rem;
            margin-top: .25rem;
            background: var(--clr-primary);
            color: var(--clr-white);
            font-family: var(--font-body);
            font-size: 1rem;
            font-weight: 600;
            border: 2px solid transparent;
            border-radius: var(--radius);
            cursor: pointer;
            transition: background var(--transition), transform .1s ease;
        }
        .btn-login:hover { background: var(--clr-primary-lt); }
        .btn-login:active { transform: translateY(1px); }
        .btn-login:focus-visible {
            outline: 3px solid color-mix(in srgb, var(--clr-primary) 35%, white);
            outline-offset: 2px;
        }

        .login-footer {
            text-align: center;
            margin-top: 1.75rem;
            font-size: .8125rem;
            color: var(--clr-text-muted);
        }
        .login-footer a { font-weight: 500; }
        .login-footer__sep { margin: 0 .5rem; opacity: .5; }
        .login-footer__webmail { font-size: .75rem; opacity: .75; }

        @media (max-width: 480px) {
            .login-card { padding: 1.5rem 1.25rem 1.75rem; }
            .login-header__inner { padding: 0 1rem; }
            .login-header__back span { display: none; }
        }
    </style>
</head>
<body>

<header class="login-header">
    <div class="login-header__inner">
        <a href="/" class="login-header__brand">
            <span class="login-header__brand-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/>
                    <path d="M9 21V12h6v9"/>
                </svg>
            </span>
            <?= htmlspecialchars(defined('APP_NAME') ? APP_NAME : 'Administration') ?>
        </a>
        <a href="/" class="login-header__back">
            ← <span>Retour au site</span>
        </a>
    </div>
</header>

<main class="login-main">
    <div class="login-shell">

        <div class="login-intro">
            <span class="login-intro__label">Espace sécurisé</span>
            <h1>Connexion</h1>
            <p>Accédez au tableau de bord et aux outils d’administration.</p>
        </div>

        <div class="login-card">

            <?php if (!empty($error)): ?>
            <div class="flash flash--error" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php elseif (!empty($flash)): ?>
            <div class="flash flash--<?= htmlspecialchars($flash['type']) ?>" role="alert">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="/admin/login" novalidate autocomplete="on">

                <div class="form-group">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input
                        id="email" name="email" type="email"
                        class="form-input"
                        placeholder="vous@example.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        autocomplete="email"
                        required autofocus
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <div class="password-wrap">
                        <input
                            id="password" name="password" type="password"
                            class="form-input"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="password-toggle" id="pwToggle"
                                aria-label="Afficher ou masquer le mot de passe">
                            <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <a href="/admin/forgot-password" class="forgot-link">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn-login">Se connecter</button>

            </form>
        </div>

        <div class="login-footer">
            <a href="/">&larr; Retour au site</a>
            <?php if (defined('APP_URL') && APP_URL !== ''): ?>
            <span class="login-footer__sep" aria-hidden="true">·</span>
            <a href="<?= htmlspecialchars(rtrim(APP_URL, '/') . '/webmail') ?>" target="_blank" rel="noopener" class="login-footer__webmail">Webmail</a>
            <?php endif; ?>
        </div>

    </div>
</main>

<script>
    document.getElementById('pwToggle').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>' +
                '<path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>' +
                '<line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    });
</script>

</body>
</html>
