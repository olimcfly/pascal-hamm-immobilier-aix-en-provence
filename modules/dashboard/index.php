<?php
$pageTitle = "Dashboard";
$pageDescription = "Votre copilote quotidien";

function renderContent() {
    $pdo = db();

    // ── DATA MINIMALE ─────────────────────
    $leads = (int) $pdo->query("
        SELECT COUNT(*) FROM crm_leads 
        WHERE created_at >= CURDATE()
    ")->fetchColumn();

   $messages = (int) $pdo->query("
    SELECT COUNT(*) 
    FROM contacts 
    WHERE status = 'new'
")->fetchColumn();

    $user = Auth::user();
    $prenom = explode(' ', $user['name'] ?? '')[0] ?? 'Conseiller';

?>

<style>
.db-container {
    padding: 16px;
    max-width: 600px;
    margin: auto;
}

/* ACTION DU JOUR */
.action-card {
    background: #0f2237;
    color: #fff;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
}

.action-title {
    font-size: 14px;
    opacity: .7;
    margin-bottom: 10px;
}

.action-main {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 16px;
}

.action-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.action-item {
    background: rgba(255,255,255,.1);
    padding: 10px;
    border-radius: 8px;
    font-size: 14px;
}

.btn-main {
    display: block;
    width: 100%;
    background: #c9a84c;
    color: #0f2237;
    text-align: center;
    padding: 14px;
    border-radius: 10px;
    font-weight: 700;
    margin-top: 15px;
    text-decoration: none;
}

/* SUIVI */
.follow-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 15px;
}

.follow-title {
    font-size: 12px;
    color: #888;
    margin-bottom: 10px;
}

.follow-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

/* IA */
.ai-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    border-top: 1px solid #eee;
    padding: 10px;
}

.ai-input {
    width: 100%;
    padding: 10px;
    border-radius: 20px;
    border: 1px solid #ddd;
}
</style>

<div class="db-container">

    <!-- 🔥 ACTION DU JOUR -->
    <div class="action-card">
        <div class="action-title">Bonjour <?= htmlspecialchars($prenom) ?></div>

        <div class="action-main">
            Aujourd’hui, on génère du business.
        </div>

        <div class="action-list">
            <div class="action-item">📞 Appeler 5 prospects</div>
            <div class="action-item">💬 Envoyer 10 messages</div>
            <div class="action-item">📱 Publier 1 contenu</div>
        </div>

        <a href="/admin/?module=prospection" class="btn-main">
            ▶ Lancer ma journée
        </a>
    </div>

    <!-- ⚡ SUIVI RAPIDE -->
    <div class="follow-card">
        <div class="follow-title">À traiter maintenant</div>

        <div class="follow-item">
            <span>Messages</span>
            <strong><?= $messages ?></strong>
        </div>

        <div class="follow-item">
            <span>Leads aujourd’hui</span>
            <strong><?= $leads ?></strong>
        </div>
    </div>

</div>

<!-- 🤖 ASSISTANT IA -->
<div class="ai-bar">
    <input class="ai-input" placeholder="Ex : créer mes messages du jour…">
</div>

<?php
}