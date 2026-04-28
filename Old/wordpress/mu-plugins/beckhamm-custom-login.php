<?php
/**
 * Plugin Name: Beckhamm — Login personnalisé
 * Description: Page wp-login sans logo WordPress, charte bleu / texte blanc.
 *
 * Installation : wp-content/mu-plugins/beckhamm-custom-login.php
 * Vérification : afficher le code source de wp-login.php et chercher
 *                « beckhamm-login mu-plugin actif »
 *
 * @package Beckhamm_Immobilier
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_filter('login_headerurl', static function (): string {
    return home_url('/');
});

add_filter('login_headertext', static function (): string {
    return get_bloginfo('name');
});

/** Repère dans le HTML que ce fichier est bien chargé */
add_action('login_head', static function (): void {
    echo "<!-- beckhamm-login mu-plugin actif -->\n";
}, 1);

/**
 * CSS après la feuille officielle `login` (obligatoire pour que les règles s’appliquent).
 * @see wp-includes/script-loader.php (handle 'login')
 */
add_action('login_enqueue_scripts', static function (): void {
    $css = <<<'CSS'
/* Beckhamm — login (inline sur handle "login") */
body.login {
  min-height: 100vh;
  background: linear-gradient(165deg, #0f2844 0%, #1a3c5e 45%, #2558a0 100%) !important;
  color: #ffffff !important;
  font-family: Inter, system-ui, -apple-system, sans-serif !important;
}

.login .wp-login-logo a,
body.login .wp-login-logo a,
.login h1 a {
  background-image: none !important;
  background-size: auto !important;
  width: auto !important;
  max-width: 100% !important;
  height: auto !important;
  min-height: 0 !important;
  padding: 0 !important;
  margin: 0 auto 1.35rem !important;
  text-indent: 0 !important;
  overflow: visible !important;
  display: block;
  text-align: center;
  color: #ffffff !important;
  font-family: "Playfair Display", Georgia, serif !important;
  font-size: clamp(1.35rem, 3.5vw, 1.75rem);
  font-weight: 600;
  line-height: 1.25;
  text-decoration: none;
  letter-spacing: 0.01em;
}
.login .wp-login-logo a:hover,
.login .wp-login-logo a:focus,
body.login .wp-login-logo a:hover,
body.login .wp-login-logo a:focus {
  color: #ffffff !important;
  box-shadow: none !important;
  outline: none !important;
}

.login #login {
  width: 90%;
  max-width: 400px;
  padding-top: 2.5rem;
}

.login #login form,
.login form.shake {
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.28) !important;
  border-radius: 16px !important;
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.25) !important;
  padding: 1.75rem 1.5rem 1.5rem !important;
}

.login form .input,
.login form input[type="text"],
.login form input[type="password"],
.login input[type="email"] {
  background: #ffffff !important;
  border: 1px solid #e5e0d8 !important;
  border-radius: 8px !important;
  color: #1a1a2e !important;
  font-size: 16px !important;
}
.login form .input:focus,
.login form input[type="text"]:focus,
.login form input[type="password"]:focus,
.login input[type="email"]:focus {
  border-color: #1a3c5e !important;
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.35) !important;
  outline: none !important;
}

.login label,
.login #login form p,
.login .user-pass-wrap label {
  color: #ffffff !important;
}

.login .button-primary,
.login .button.button-primary,
.login.wp-core-ui .button-primary,
.wp-core-ui .button.button-primary.button-large {
  background: #ffffff !important;
  border: none !important;
  border-radius: 8px !important;
  color: #1a3c5e !important;
  font-weight: 600 !important;
  text-shadow: none !important;
  box-shadow: none !important;
  padding: 0 1.25rem !important;
  min-height: 44px;
  line-height: 42px !important;
  width: 100%;
}
.login .button-primary:hover,
.login .button-primary:focus,
.wp-core-ui .button-primary:hover,
.wp-core-ui .button-primary:focus {
  background: #f0f4f8 !important;
  color: #0f2844 !important;
}

.login #nav a,
.login #backtoblog a,
.login .privacy-policy-page-link {
  color: rgba(255, 255, 255, 0.92) !important;
}
.login #nav a:hover,
.login #backtoblog a:hover,
.login .privacy-policy-page-link:hover {
  color: #ffffff !important;
}

.login .message,
.login .success,
.login #login_error {
  background: rgba(255, 255, 255, 0.95) !important;
  border-left: 4px solid #1a3c5e !important;
  color: #1a1a2e !important;
  border-radius: 8px !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12) !important;
}
.login .notice-error,
.login #login_error {
  border-left-color: #b91c1c !important;
}

.login .forgetmenot label {
  color: rgba(255, 255, 255, 0.95) !important;
}

.login .language-switcher label .dashicons {
  color: #ffffff !important;
}
.login .language-switcher select {
  background: #ffffff !important;
  color: #1a1a2e !important;
  border-radius: 6px !important;
}

CSS;

    wp_add_inline_style('login', $css);
}, 100);

/** Polices Google (pas de dépendance `login` : ordre d’enregistrement variable selon la version WP) */
add_action('login_enqueue_scripts', static function (): void {
    wp_enqueue_style(
        'beckhamm-login-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Inter:wght@400;500;600&display=swap',
        [],
        null
    );
}, 15);
