<?php
// ── SEGURIDAD — debe pasar por login primero ────────────────────────────────
session_start();
require_once "../includes/auth_check.php";
require_auth('alumno');

// ── Datos de sesión ─────────────────────────────────────────────────────────
$usuario        = $_SESSION['user_name'];
$rol            = $_SESSION['rol'];
$avatar_inicial = strtoupper(substr($usuario, 0, 1));
$logout_url     = "../auth/logout.php";

// ── Simulación de comentarios con expiración 72h (placeholder hasta API) ───
// Cuando la BD esté conectada, estos vendrán de:
// SELECT * FROM comentarios WHERE destinatario_id = ? AND expires_at > NOW()
$comentarios_activos = []; // array vacío hasta integración real
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mis Proyectos — Gitus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Mona+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>

/* ── TOKENS — Identidad GitHub ───────────────────────────────────────────── */
:root {
    --bg-canvas:      #0d1117;
    --bg-overlay:     #161b22;
    --bg-subtle:      #21262d;
    --bg-muted:       #30363d;

    --border:         #30363d;
    --border-muted:   #21262d;

    --text-primary:   #e6edf3;
    --text-secondary: #8b949e;
    --text-muted:     #6e7681;

    --accent:         #238636;
    --accent-hover:   #2ea043;
    --accent-subtle:  rgba(35,134,54,.15);

    --blue:           #1f6feb;
    --blue-subtle:    rgba(31,111,235,.15);

    --purple:         #8957e5;
    --purple-subtle:  rgba(137,87,229,.15);

    --yellow:         #d29922;
    --yellow-subtle:  rgba(210,153,34,.15);

    --red:            #da3633;
    --red-subtle:     rgba(218,54,51,.12);

    --radius:         6px;
    --radius-lg:      12px;
    --font-ui:        'Mona Sans', 'Segoe UI', sans-serif;
    --font-mono:      'JetBrains Mono', monospace;
}

/* ── RESET ───────────────────────────────────────────────────────────────── */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html { font-size: 14px; }
body {
    font-family: var(--font-ui);
    background: var(--bg-canvas);
    color: var(--text-primary);
    min-height: 100vh;
    line-height: 1.5;
}
a { color: var(--blue); text-decoration: none; }
a:hover { text-decoration: underline; }
button { cursor: pointer; font-family: var(--font-ui); }

/* ── TOPBAR ──────────────────────────────────────────────────────────────── */
.topbar {
    position: sticky;
    top: 0;
    z-index: 200;
    height: 56px;
    background: var(--bg-overlay);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    padding: 0 24px;
    gap: 16px;
}
.topbar-logo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: var(--font-mono);
    font-weight: 700;
    font-size: 15px;
    color: var(--text-primary);
    text-decoration: none;
    letter-spacing: -.01em;
}
.topbar-logo:hover { text-decoration: none; }
.topbar-divider { width:1px; height:20px; background:var(--border); }
.topbar-role-badge {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: var(--purple);
    background: var(--purple-subtle);
    border: 1px solid rgba(137,87,229,.3);
    padding: 2px 8px;
    border-radius: 20px;
}
.topbar-right {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 10px;
}
.topbar-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--text-secondary);
    padding: 5px 12px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    background: none;
    transition: border-color .15s, color .15s, background .15s;
}
.topbar-btn:hover { color: var(--text-primary); border-color: var(--text-muted); background: var(--bg-subtle); }
.topbar-btn.primary {
    background: var(--accent);
    border-color: var(--accent);
    color: #fff;
    font-weight: 600;
}
.topbar-btn.primary:hover { background: var(--accent-hover); border-color: var(--accent-hover); }

/* Avatar */
.avatar-wrap { position: relative; }
.avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: var(--bg-subtle);
    border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 13px;
    color: var(--text-primary);
    font-family: var(--font-mono);
    cursor: pointer;
    flex-shrink: 0;
    transition: border-color .15s;
}
.avatar:hover { border-color: var(--text-muted); }
.avatar-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: var(--bg-overlay);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    min-width: 210px;
    box-shadow: 0 8px 32px rgba(0,0,0,.5);
    overflow: hidden;
    display: none;
    z-index: 300;
}
.avatar-menu.open { display: block; }
.avatar-menu-header {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
}
.avatar-menu-header strong { display: block; font-size: 13px; }
.avatar-menu-header span { font-size: 12px; color: var(--text-secondary); font-family: var(--font-mono); }
.avatar-menu a {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 16px;
    font-size: 13px; color: var(--text-secondary);
    transition: background .1s, color .1s;
    text-decoration: none;
}
.avatar-menu a:hover { background: var(--bg-subtle); color: var(--text-primary); }
.avatar-menu a.danger { color: var(--red); }
.avatar-menu a.danger:hover { background: var(--red-subtle); }
.avatar-menu-sep { height: 1px; background: var(--border); }

/* ── LAYOUT ──────────────────────────────────────────────────────────────── */
.layout { display: flex; min-height: calc(100vh - 56px); }

/* ── SIDEBAR ─────────────────────────────────────────────────────────────── */
.sidebar {
    width: 216px;
    flex-shrink: 0;
    background: var(--bg-overlay);
    border-right: 1px solid var(--border);
    padding: 16px 10px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.sidebar-label {
    font-size: 11px; font-weight: 600;
    letter-spacing: .08em; text-transform: uppercase;
    color: var(--text-muted);
    padding: 4px 8px 6px;
    margin-top: 10px;
}
.sidebar-label:first-child { margin-top: 0; }
.nav-item {
    display: flex; align-items: center; gap: 9px;
    padding: 7px 10px;
    border-radius: var(--radius);
    font-size: 13px; color: var(--text-secondary);
    cursor: pointer;
    transition: background .1s, color .1s;
    border: none; background: none;
    width: 100%; text-align: left;
    text-decoration: none;
}
.nav-item:hover { background: var(--bg-subtle); color: var(--text-primary); text-decoration: none; }
.nav-item.active { background: var(--bg-subtle); color: var(--text-primary); font-weight: 500; }
.nav-item svg { flex-shrink: 0; opacity: .65; }
.nav-item.active svg { opacity: 1; }
.nav-badge {
    margin-left: auto;
    font-size: 11px; font-family: var(--font-mono);
    background: var(--blue); color: #fff;
    padding: 1px 6px; border-radius: 20px;
    font-weight: 600;
}

/* ── MAIN ────────────────────────────────────────────────────────────────── */
.main { flex: 1; padding: 28px 32px; overflow-x: hidden; }

.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 20px; font-weight: 600; }
.page-header p { margin-top: 3px; font-size: 13px; color: var(--text-secondary); }

/* ── BANNER ──────────────────────────────────────────────────────────────── */
.banner {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 14px 16px;
    border-radius: var(--radius-lg);
    margin-bottom: 20px;
    font-size: 13px;
    border: 1px solid;
    animation: fadeUp .3s ease both;
}
.banner-yellow { background: var(--yellow-subtle); border-color: rgba(210,153,34,.35); }
.banner-yellow svg { color: var(--yellow); flex-shrink: 0; margin-top:1px; }
.banner p { color: var(--text-secondary); line-height: 1.5; }
.banner strong { color: var(--text-primary); }
.banner-close {
    margin-left: auto; background: none; border: none;
    color: var(--text-muted); padding: 0; cursor: pointer;
    font-size: 16px; line-height: 1; flex-shrink: 0;
}
.banner-close:hover { color: var(--text-primary); }

/* ── COMENTARIOS 72H ─────────────────────────────────────────────────────── */
.comment-card {
    background: var(--bg-overlay);
    border: 1px solid rgba(137,87,229,.35);
    border-radius: var(--radius-lg);
    margin-bottom: 20px;
    overflow: hidden;
    animation: fadeUp .25s ease both;
}
.comment-card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 16px;
    background: var(--purple-subtle);
    border-bottom: 1px solid rgba(137,87,229,.2);
}
.comment-card-header span {
    font-size: 12px; font-weight: 600;
    color: var(--purple);
    display: flex; align-items: center; gap: 6px;
}
.comment-ttl {
    font-size: 11px; font-family: var(--font-mono);
    color: var(--text-muted);
}
.comment-body { padding: 14px 16px; font-size: 13px; color: var(--text-secondary); line-height: 1.6; }
.comment-from { margin-top: 8px; font-size: 12px; color: var(--text-muted); }
.comment-from strong { color: var(--text-secondary); }

/* ── STATS ───────────────────────────────────────────────────────────────── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.stat {
    background: var(--bg-overlay);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 16px 18px;
    transition: border-color .15s;
}
.stat:hover { border-color: var(--text-muted); }
.stat-label { font-size: 12px; color: var(--text-muted); margin-bottom: 6px; font-weight: 500; }
.stat-val {
    font-size: 24px; font-weight: 700;
    font-family: var(--font-mono); line-height: 1;
    color: var(--text-primary);
}
.stat-sub { font-size: 11px; color: var(--text-muted); margin-top: 5px; }
.c-green  .stat-val { color: var(--accent-hover); }
.c-blue   .stat-val { color: var(--blue); }
.c-purple .stat-val { color: var(--purple); }

/* ── CARD ────────────────────────────────────────────────────────────────── */
.card {
    background: var(--bg-overlay);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: 20px;
}
.card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 18px;
    border-bottom: 1px solid var(--border);
}
.card-header h2 {
    font-size: 14px; font-weight: 600;
    display: flex; align-items: center; gap: 8px;
}
.card-header h2 svg { opacity: .6; }

/* ── BOTONES ─────────────────────────────────────────────────────────────── */
.btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px;
    border-radius: var(--radius);
    font-size: 13px; font-weight: 500;
    border: 1px solid var(--border);
    background: var(--bg-subtle);
    color: var(--text-secondary);
    transition: border-color .12s, color .12s, background .12s;
    text-decoration: none;
}
.btn:hover { color: var(--text-primary); border-color: var(--text-muted); text-decoration: none; }
.btn-green {
    background: var(--accent); border-color: var(--accent);
    color: #fff; font-weight: 600;
}
.btn-green:hover { background: var(--accent-hover); border-color: var(--accent-hover); }
.btn-sm { padding: 4px 10px; font-size: 12px; }

/* ── REPOSITORIOS GRID ───────────────────────────────────────────────────── */
.repo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
    padding: 18px;
}
.repo-item {
    background: var(--bg-canvas);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 16px 18px;
    transition: border-color .15s, transform .15s;
    display: flex; flex-direction: column; gap: 10px;
}
.repo-item:hover { border-color: var(--blue); transform: translateY(-1px); }
.repo-name {
    display: flex; align-items: center; gap: 8px;
    font-weight: 600; font-size: 13px; color: var(--blue);
    font-family: var(--font-mono);
}
.repo-desc { font-size: 12px; color: var(--text-muted); line-height: 1.4; }
.repo-meta {
    display: flex; align-items: center; gap: 14px;
    font-size: 11px; color: var(--text-muted);
    margin-top: auto;
}
.repo-meta span { display: flex; align-items: center; gap: 4px; }
.repo-lang-dot {
    width: 10px; height: 10px;
    border-radius: 50%; flex-shrink: 0;
}
.repo-actions { display: flex; gap: 6px; }

/* Visibilidad badge */
.vis-badge {
    font-size: 10px; font-weight: 600; letter-spacing: .04em;
    padding: 1px 6px; border-radius: 20px;
    text-transform: uppercase;
}
.vis-public  { border: 1px solid rgba(35,134,54,.4); color: var(--accent-hover); }
.vis-private { border: 1px solid var(--border); color: var(--text-muted); }

/* ── NUEVO REPO FORM ─────────────────────────────────────────────────────── */
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.form-group label { font-size: 12px; font-weight: 600; color: var(--text-secondary); }
.form-group input,
.form-group textarea,
.form-group select {
    background: var(--bg-subtle);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 8px 12px;
    font-size: 13px; font-family: var(--font-ui);
    color: var(--text-primary);
    outline: none;
    transition: border-color .15s;
    width: 100%;
}
.form-group input::placeholder,
.form-group textarea::placeholder { color: var(--text-muted); }
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus { border-color: var(--blue); box-shadow: 0 0 0 3px var(--blue-subtle); }
.form-group textarea { resize: vertical; min-height: 80px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-footer { display: flex; justify-content: flex-end; gap: 8px; padding-top: 8px; border-top: 1px solid var(--border); }
.form-hint { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

/* Modal */
.modal-backdrop {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.65);
    z-index: 400;
    display: none; align-items: center; justify-content: center;
    backdrop-filter: blur(2px);
}
.modal-backdrop.open { display: flex; }
.modal {
    background: var(--bg-overlay);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    width: 100%; max-width: 480px;
    box-shadow: 0 20px 60px rgba(0,0,0,.6);
    overflow: hidden;
    animation: fadeUp .2s ease;
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}
.modal-header h3 { font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.modal-close { background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer; }
.modal-close:hover { color:var(--text-primary); }
.modal-body { padding: 20px; }

/* ── BÚSQUEDA ────────────────────────────────────────────────────────────── */
.search-box {
    display: flex; gap: 8px;
    padding: 12px 18px;
    border-bottom: 1px solid var(--border);
    background: var(--bg-canvas);
}
.search-box input {
    flex: 1;
    background: var(--bg-subtle);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 7px 12px;
    font-size: 13px; font-family: var(--font-ui);
    color: var(--text-primary); outline: none;
    transition: border-color .15s;
}
.search-box input::placeholder { color: var(--text-muted); }
.search-box input:focus { border-color: var(--blue); }

/* ── TABLA ───────────────────────────────────────────────────────────────── */
.tbl-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
thead th {
    padding: 9px 18px;
    text-align: left; font-size: 11px; font-weight: 600;
    letter-spacing: .06em; text-transform: uppercase;
    color: var(--text-muted); background: var(--bg-canvas);
    border-bottom: 1px solid var(--border);
}
tbody tr { border-bottom: 1px solid var(--border-muted); transition: background .1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: rgba(255,255,255,.02); }
tbody td { padding: 11px 18px; color: var(--text-secondary); vertical-align: middle; }
tbody td:first-child { color: var(--text-primary); }

/* ── ACTIVIDAD ───────────────────────────────────────────────────────────── */
.act-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 12px 18px;
    border-bottom: 1px solid var(--border-muted);
    transition: background .1s;
}
.act-item:last-child { border-bottom: none; }
.act-item:hover { background: rgba(255,255,255,.02); }
.act-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; margin-top:5px; }
.d-green  { background: var(--accent); }
.d-blue   { background: var(--blue); }
.d-purple { background: var(--purple); }
.d-yellow { background: var(--yellow); }
.act-text { font-size:13px; color:var(--text-secondary); line-height:1.4; }
.act-text strong { color:var(--text-primary); font-weight:500; }
.act-time { margin-left:auto; font-size:11px; color:var(--text-muted); font-family:var(--font-mono); flex-shrink:0; padding-top:2px; }

/* ── EMPTY STATE ─────────────────────────────────────────────────────────── */
.empty {
    padding: 44px 20px; text-align: center;
    color: var(--text-muted); font-size: 13px;
}
.empty svg { opacity: .3; display: block; margin: 0 auto 12px; }
.empty p { line-height: 1.6; }
.empty .empty-action { margin-top: 16px; }

/* ── RESPONSIVE ──────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .sidebar { display: none; }
    .main { padding: 18px 14px; }
    .topbar { padding: 0 14px; }
    .stats-row { grid-template-columns: 1fr 1fr; }
    .repo-grid { grid-template-columns: 1fr; padding: 12px; }
    .form-row { grid-template-columns: 1fr; }
}

/* ── ANIMACIONES ─────────────────────────────────────────────────────────── */
@keyframes fadeUp {
    from { opacity:0; transform:translateY(8px); }
    to   { opacity:1; transform:translateY(0); }
}
.main > * { animation: fadeUp .22s ease both; }
.main > *:nth-child(1) { animation-delay: .04s; }
.main > *:nth-child(2) { animation-delay: .08s; }
.main > *:nth-child(3) { animation-delay: .12s; }
.main > *:nth-child(4) { animation-delay: .16s; }

</style>
</head>
<body>

<!-- ══ TOPBAR ══════════════════════════════════════════════════════════════ -->
<header class="topbar">
    <a class="topbar-logo" href="#">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <rect width="20" height="20" rx="6" fill="#238636"/>
            <path d="M5 10 L10 5 L15 10 L10 15 Z" fill="white" opacity=".9"/>
        </svg>
        Gitus
    </a>
    <div class="topbar-divider"></div>
    <span class="topbar-role-badge">Alumno</span>

    <div class="topbar-right">
        <!-- Botón rápido nuevo repo -->
        <button class="topbar-btn primary" onclick="abrirModalRepo()">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                <path d="M7.75 2a.75.75 0 01.75.75V7h4.25a.75.75 0 010 1.5H8.5v4.25a.75.75 0 01-1.5 0V8.5H2.75a.75.75 0 010-1.5H7V2.75A.75.75 0 017.75 2z"/>
            </svg>
            Nuevo repositorio
        </button>

        <button class="topbar-btn" onclick="showSection('buscar')">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                <path d="M10.68 11.74a6 6 0 01-7.922-8.982 6 6 0 018.982 7.922l3.04 3.04a.749.749 0 01-.326 1.275.749.749 0 01-.734-.215l-3.04-3.04z"/>
            </svg>
            Buscar
        </button>

        <div class="avatar-wrap">
            <div class="avatar" id="avatarBtn">
                <?php echo htmlspecialchars($avatar_inicial); ?>
            </div>
            <div class="avatar-menu" id="avatarMenu">
                <div class="avatar-menu-header">
                    <strong><?php echo htmlspecialchars($usuario); ?></strong>
                    <span>alumno</span>
                </div>
                <a href="#">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M10.5 5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zm.061 3.073a4 4 0 10-5.123 0 6.004 6.004 0 00-3.431 5.142.75.75 0 001.498.07 4.5 4.5 0 018.99 0 .75.75 0 101.498-.07 6.005 6.005 0 00-3.432-5.142z"/></svg>
                    Mi perfil
                </a>
                <a href="#">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M7.429 1.525a6.593 6.593 0 011.142 0c.036.003.108.036.137.146l.289 1.105c.147.56.55.967.997 1.189.174.086.341.183.501.29.417.278.97.423 1.53.27l1.102-.303c.11-.03.175.016.195.046a6.45 6.45 0 01.571.99c.034.(.057.07-.004.106-.071l-.714.998c-.22.308-.33.688-.33 1.068 0 .38.11.76.33 1.068l.714.998c.075.105.069.139.004.106a6.45 6.45 0 01-.571.99c-.02.03-.085.076-.195.046l-1.102-.303c-.56-.153-1.113-.008-1.53.27-.16.107-.327.204-.501.29-.447.222-.85.629-.997 1.189l-.289 1.105c-.029.11-.101.143-.137.146a6.593 6.593 0 01-1.142 0c-.036-.003-.108-.036-.137-.146l-.289-1.105c-.147-.56-.55-.967-.997-1.189a4.502 4.502 0 01-.501-.29c-.417-.278-.97-.423-1.53-.27l-1.102.303c-.11.03-.175-.016-.195-.046a6.45 6.45 0 01-.571-.99c-.034-.(.057-.07.004-.106.071l.714-.998c.22-.308.33-.688.33-1.068 0-.38-.11-.76-.33-1.068l-.714-.998c-.075-.105-.069-.139-.004-.106a6.45 6.45 0 01.571-.99c.02-.03.085-.076.195-.046l1.102.303c.56.153 1.113.008 1.53-.27.16-.107.327-.204.501-.29.447-.222.85-.629.997-1.189l.289-1.105c.029-.11.101-.143.137-.146zM8 10.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z"/></svg>
                    Configuración
                </a>
                <div class="avatar-menu-sep"></div>
                <a href="<?php echo htmlspecialchars($logout_url); ?>" class="danger">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M2 2.75C2 1.784 2.784 1 3.75 1h5.5a.75.75 0 010 1.5h-5.5a.25.25 0 00-.25.25v10.5c0 .138.112.25.25.25h5.5a.75.75 0 010 1.5h-5.5A1.75 1.75 0 012 13.25V2.75zm10.44 4.5H6.75a.75.75 0 000 1.5h5.69l-1.97 1.97a.75.75 0 101.06 1.06l3.25-3.25a.75.75 0 000-1.06l-3.25-3.25a.75.75 0 10-1.06 1.06l1.97 1.97z"/></svg>
                    Cerrar sesión
                </a>
            </div>
        </div>
    </div>
</header>

<!-- ══ LAYOUT ══════════════════════════════════════════════════════════════ -->
<div class="layout">

    <!-- ── SIDEBAR ──────────────────────────────────────────────────────── -->
    <nav class="sidebar">
        <span class="sidebar-label">Mi espacio</span>
        <button class="nav-item active" onclick="showSection('inicio')">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M6.906.664a1.749 1.749 0 012.187 0l5.25 4.2c.415.332.657.835.657 1.367v7.019A1.75 1.75 0 0113.25 15h-3.5a.75.75 0 01-.75-.75V9H7v5.25a.75.75 0 01-.75.75h-3.5A1.75 1.75 0 011 13.25V6.23c0-.531.242-1.034.657-1.366l5.25-4.2h-.001z"/></svg>
            Inicio
        </button>
        <button class="nav-item" onclick="showSection('repos')">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M2 2.5A2.5 2.5 0 014.5 0h8.75a.75.75 0 01.75.75v12.5a.75.75 0 01-.75.75h-2.5a.75.75 0 010-1.5h1.75v-2h-8a1 1 0 00-.714 1.7.75.75 0 01-1.072 1.05A2.495 2.495 0 012 11.5v-9zm10.5-1h-8a1 1 0 00-1 1v6.708A2.486 2.486 0 014.5 9h8V1.5z"/></svg>
            Repositorios
            <span class="nav-badge" id="repoCount">0</span>
        </button>
        <button class="nav-item" onclick="showSection('actividad')">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0zM8 0a8 8 0 100 16A8 8 0 008 0zm.5 4.75a.75.75 0 00-1.5 0v3.5a.75.75 0 00.471.696l2.5 1a.75.75 0 00.557-1.392L8.5 7.742V4.75z"/></svg>
            Actividad
        </button>

        <span class="sidebar-label">Herramientas</span>
        <button class="nav-item" onclick="showSection('buscar')">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M10.68 11.74a6 6 0 01-7.922-8.982 6 6 0 018.982 7.922l3.04 3.04a.749.749 0 01-.326 1.275.749.749 0 01-.734-.215l-3.04-3.04z"/></svg>
            Buscar usuarios
        </button>

        <span class="sidebar-label">Cuenta</span>
        <button class="nav-item" id="sidebarGithubBtn">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/></svg>
            Vincular GitHub
        </button>
    </nav>

    <!-- ── MAIN ──────────────────────────────────────────────────────────── -->
    <main class="main">

        <!-- ══ SECCIÓN: INICIO ═════════════════════════════════════════ -->
        <section id="sec-inicio">

            <!-- Banner GitHub no vinculado -->
            <div class="banner banner-yellow" id="githubBanner">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8.22 1.754a.25.25 0 00-.44 0L1.698 13.132a.25.25 0 00.22.368h12.164a.25.25 0 00.22-.368L8.22 1.754zm-1.763-.707c.659-1.234 2.427-1.234 3.086 0l6.082 11.378A2 2 0 0113.82 15H2.18a2 2 0 01-1.885-2.575L6.457 1.047zM9 11a1 1 0 11-2 0 1 1 0 012 0zm-.25-5.25a.75.75 0 00-1.5 0v2.5a.75.75 0 001.5 0v-2.5z"/>
                </svg>
                <p><strong>Vinculá tu cuenta de GitHub</strong> para crear y gestionar repositorios desde Gitus. Sin esta conexión no podrás crear proyectos.</p>
                <button class="banner-close" onclick="this.parentElement.style.display='none'">×</button>
            </div>

            <?php if (!empty($comentarios_activos)): ?>
            <!-- Comentarios institucionales con expiración 72h -->
            <?php foreach ($comentarios_activos as $c): ?>
            <div class="comment-card">
                <div class="comment-card-header">
                    <span>
                        <svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor"><path d="M1 2.75C1 1.784 1.784 1 2.75 1h10.5c.966 0 1.75.784 1.75 1.75v7.5A1.75 1.75 0 0113.25 12H9.06l-2.573 2.573A1.458 1.458 0 014 13.543V12H2.75A1.75 1.75 0 011 10.25v-7.5z"/></svg>
                        Mensaje institucional
                    </span>
                    <span class="comment-ttl">Expira en <?php echo $c['ttl']; ?></span>
                </div>
                <div class="comment-body">
                    <?php echo htmlspecialchars($c['texto']); ?>
                    <div class="comment-from">— <strong><?php echo htmlspecialchars($c['autor']); ?></strong></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <div class="page-header">
                <h1>Hola, <?php echo htmlspecialchars($usuario); ?> 👋</h1>
                <p>Tu espacio de proyectos académicos en Gitus</p>
            </div>

            <div class="stats-row">
                <div class="stat c-blue">
                    <div class="stat-label">Repositorios</div>
                    <div class="stat-val" id="statRepos">0</div>
                    <div class="stat-sub">Creados por vos</div>
                </div>
                <div class="stat c-green">
                    <div class="stat-label">Commits totales</div>
                    <div class="stat-val">—</div>
                    <div class="stat-sub">Vía API GitHub</div>
                </div>
                <div class="stat c-purple">
                    <div class="stat-label">Mensajes activos</div>
                    <div class="stat-val"><?php echo count($comentarios_activos); ?></div>
                    <div class="stat-sub">Expiran en 72h</div>
                </div>
            </div>

            <!-- Repos recientes -->
            <div class="card">
                <div class="card-header">
                    <h2>
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M2 2.5A2.5 2.5 0 014.5 0h8.75a.75.75 0 01.75.75v12.5a.75.75 0 01-.75.75h-2.5a.75.75 0 010-1.5h1.75v-2h-8a1 1 0 00-.714 1.7.75.75 0 01-1.072 1.05A2.495 2.495 0 012 11.5v-9zm10.5-1h-8a1 1 0 00-1 1v6.708A2.486 2.486 0 014.5 9h8V1.5z"/></svg>
                        Repositorios recientes
                    </h2>
                    <button class="btn btn-sm" onclick="showSection('repos')">Ver todos</button>
                </div>
                <div id="reposRecientes">
                    <div class="empty">
                        <svg width="36" height="36" viewBox="0 0 16 16" fill="currentColor"><path d="M2 2.5A2.5 2.5 0 014.5 0h8.75a.75.75 0 01.75.75v12.5a.75.75 0 01-.75.75h-2.5a.75.75 0 010-1.5h1.75v-2h-8a1 1 0 00-.714 1.7.75.75 0 01-1.072 1.05A2.495 2.495 0 012 11.5v-9zm10.5-1h-8a1 1 0 00-1 1v6.708A2.486 2.486 0 014.5 9h8V1.5z"/></svg>
                        <p>Todavía no tenés repositorios.<br>Creá tu primer proyecto para empezar.</p>
                        <div class="empty-action">
                            <button class="btn btn-green" onclick="abrirModalRepo()">
                                <svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor"><path d="M7.75 2a.75.75 0 01.75.75V7h4.25a.75.75 0 010 1.5H8.5v4.25a.75.75 0 01-1.5 0V8.5H2.75a.75.75 0 010-1.5H7V2.75A.75.75 0 017.75 2z"/></svg>
                                Nuevo repositorio
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actividad reciente resumen -->
            <div class="card">
                <div class="card-header">
                    <h2>
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0z"/></svg>
                        Actividad reciente
                    </h2>
                </div>
                <div class="empty">
                    <p>Sin actividad reciente. Vinculá GitHub para ver tu historial.</p>
                </div>
            </div>

        </section>

        <!-- ══ SECCIÓN: REPOSITORIOS ════════════════════════════════════ -->
        <section id="sec-repos" style="display:none">
            <div class="page-header">
                <h1>Mis repositorios</h1>
                <p>Todos tus proyectos académicos</p>
            </div>

            <div class="card">
                <div class="search-box">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" style="color:var(--text-muted);flex-shrink:0;align-self:center">
                        <path d="M10.68 11.74a6 6 0 01-7.922-8.982 6 6 0 018.982 7.922l3.04 3.04a.749.749 0 01-.326 1.275.749.749 0 01-.734-.215l-3.04-3.04z"/>
                    </svg>
                    <input type="text" id="searchRepo" placeholder="Buscar repositorio..." oninput="filtrarRepos()">
                    <select id="filtroVisibilidad" onchange="filtrarRepos()" style="background:var(--bg-subtle);border:1px solid var(--border);border-radius:var(--radius);padding:7px 10px;font-size:12px;color:var(--text-secondary);font-family:var(--font-ui);outline:none;cursor:pointer;">
                        <option value="">Todos</option>
                        <option value="publico">Públicos</option>
                        <option value="privado">Privados</option>
                    </select>
                    <button class="btn btn-green btn-sm" onclick="abrirModalRepo()">
                        <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M7.75 2a.75.75 0 01.75.75V7h4.25a.75.75 0 010 1.5H8.5v4.25a.75.75 0 01-1.5 0V8.5H2.75a.75.75 0 010-1.5H7V2.75A.75.75 0 017.75 2z"/></svg>
                        Nuevo
                    </button>
                </div>
                <div id="repoGrid" class="repo-grid">
                    <div style="grid-column:1/-1">
                        <div class="empty">
                            <svg width="36" height="36" viewBox="0 0 16 16" fill="currentColor"><path d="M2 2.5A2.5 2.5 0 014.5 0h8.75a.75.75 0 01.75.75v12.5a.75.75 0 01-.75.75h-2.5a.75.75 0 010-1.5h1.75v-2h-8a1 1 0 00-.714 1.7.75.75 0 01-1.072 1.05A2.495 2.495 0 012 11.5v-9zm10.5-1h-8a1 1 0 00-1 1v6.708A2.486 2.486 0 014.5 9h8V1.5z"/></svg>
                            <p>Sin repositorios todavía.<br>Vinculá tu cuenta de GitHub para comenzar.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══ SECCIÓN: ACTIVIDAD ════════════════════════════════════════ -->
        <section id="sec-actividad" style="display:none">
            <div class="page-header">
                <h1>Mi actividad</h1>
                <p>Historial de commits, pushes y eventos en tus repositorios</p>
            </div>
            <div class="card">
                <div class="empty" style="padding:48px">
                    <svg width="40" height="40" viewBox="0 0 16 16" fill="currentColor"><path d="M1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0z"/></svg>
                    <p>No hay actividad registrada.<br>Aparecerá aquí cuando hagas commits y pushes en GitHub.</p>
                </div>
            </div>
        </section>

        <!-- ══ SECCIÓN: BUSCAR USUARIOS ════════════════════════════════ -->
        <section id="sec-buscar" style="display:none">
            <div class="page-header">
                <h1>Buscar usuarios</h1>
                <p>Encontrá compañeros, profesores o directivos en Gitus</p>
            </div>
            <div class="card">
                <div class="search-box" style="padding:16px 18px">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor" style="color:var(--text-muted);flex-shrink:0;align-self:center">
                        <path d="M10.68 11.74a6 6 0 01-7.922-8.982 6 6 0 018.982 7.922l3.04 3.04a.749.749 0 01-.326 1.275.749.749 0 01-.734-.215l-3.04-3.04z"/>
                    </svg>
                    <input type="text" id="globalSearch" placeholder="Nombre, DNI o usuario de GitHub..." style="font-size:14px;padding:9px 12px">
                    <select id="filtroBusqueda" style="background:var(--bg-subtle);border:1px solid var(--border);border-radius:var(--radius);padding:9px 10px;font-size:13px;color:var(--text-secondary);font-family:var(--font-ui);outline:none;cursor:pointer;">
                        <option value="">Todos los roles</option>
                        <option value="alumno">Alumnos</option>
                        <option value="profesor">Profesores</option>
                        <option value="directivo">Directivos</option>
                    </select>
                    <button class="btn btn-green" onclick="buscarUsuarios()">Buscar</button>
                </div>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>GitHub</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="resultBusqueda">
                            <tr>
                                <td colspan="4">
                                    <div class="empty"><p>Ingresá un nombre o DNI para buscar.</p></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>
</div>

<!-- ══ MODAL: NUEVO REPOSITORIO ════════════════════════════════════════════ -->
<div class="modal-backdrop" id="modalRepo">
    <div class="modal">
        <div class="modal-header">
            <h3>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M2 2.5A2.5 2.5 0 014.5 0h8.75a.75.75 0 01.75.75v12.5a.75.75 0 01-.75.75h-2.5a.75.75 0 010-1.5h1.75v-2h-8a1 1 0 00-.714 1.7.75.75 0 01-1.072 1.05A2.495 2.495 0 012 11.5v-9zm10.5-1h-8a1 1 0 00-1 1v6.708A2.486 2.486 0 014.5 9h8V1.5z"/></svg>
                Nuevo repositorio
            </h3>
            <button class="modal-close" onclick="cerrarModalRepo()">×</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Nombre del repositorio *</label>
                <input type="text" id="repoNombre" placeholder="ej: mi-proyecto-final" autocomplete="off">
                <span class="form-hint">Solo letras, números, guiones y guiones bajos. Sin espacios.</span>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea id="repoDesc" placeholder="Breve descripción del proyecto (opcional)"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Visibilidad</label>
                    <select id="repoVisibilidad">
                        <option value="public">Público</option>
                        <option value="private">Privado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Inicializar con README</label>
                    <select id="repoReadme">
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>
            <div id="repoMsgError" style="display:none;color:var(--red);font-size:13px;margin-bottom:12px;padding:8px 12px;background:var(--red-subtle);border-radius:var(--radius);border:1px solid rgba(218,54,51,.3);"></div>
            <div class="form-footer">
                <button class="btn" onclick="cerrarModalRepo()">Cancelar</button>
                <button class="btn btn-green" onclick="crearRepo()">
                    <svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor"><path d="M7.75 2a.75.75 0 01.75.75V7h4.25a.75.75 0 010 1.5H8.5v4.25a.75.75 0 01-1.5 0V8.5H2.75a.75.75 0 010-1.5H7V2.75A.75.75 0 017.75 2z"/></svg>
                    Crear repositorio
                </button>
            </div>
        </div>
    </div>
</div>

<script>

// ── Repositorios en memoria (se reemplaza con fetch a la API) ─────────────
let repos = [];

// ── Secciones ─────────────────────────────────────────────────────────────
const SECTIONS = ['inicio','repos','actividad','buscar'];

function showSection(id) {
    SECTIONS.forEach(s => {
        const el = document.getElementById('sec-' + s);
        if (el) el.style.display = s === id ? 'block' : 'none';
    });
    document.querySelectorAll('.nav-item').forEach(btn => {
        const fn = btn.getAttribute('onclick') || '';
        btn.classList.toggle('active', fn === `showSection('${id}')`);
    });
}

// ── Avatar menu ───────────────────────────────────────────────────────────
document.getElementById('avatarBtn').addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('avatarMenu').classList.toggle('open');
});
document.addEventListener('click', () => {
    document.getElementById('avatarMenu').classList.remove('open');
});

// ── Modal nuevo repo ──────────────────────────────────────────────────────
function abrirModalRepo() {
    document.getElementById('modalRepo').classList.add('open');
    document.getElementById('repoNombre').focus();
}
function cerrarModalRepo() {
    document.getElementById('modalRepo').classList.remove('open');
    document.getElementById('repoNombre').value = '';
    document.getElementById('repoDesc').value   = '';
    document.getElementById('repoMsgError').style.display = 'none';
}
document.getElementById('modalRepo').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalRepo();
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') cerrarModalRepo();
});

// ── Crear repo (placeholder — conectar con api/crear_repo.php) ────────────
function crearRepo() {
    const nombre = document.getElementById('repoNombre').value.trim();
    const errEl  = document.getElementById('repoMsgError');

    if (!nombre) {
        errEl.textContent = 'El nombre del repositorio es obligatorio.';
        errEl.style.display = 'block';
        return;
    }
    if (!/^[a-zA-Z0-9_\-]+$/.test(nombre)) {
        errEl.textContent = 'Solo letras, números, guiones y guiones bajos. Sin espacios.';
        errEl.style.display = 'block';
        return;
    }

    const desc       = document.getElementById('repoDesc').value.trim();
    const visib      = document.getElementById('repoVisibilidad').value;
    const conReadme  = document.getElementById('repoReadme').value === '1';

    // ── Agregar al estado local (hasta que la API esté disponible) ────────
    const nuevo = { nombre, desc, visib, conReadme, fecha: 'ahora' };
    repos.unshift(nuevo);
    renderRepos();
    actualizarContadores();
    cerrarModalRepo();

    // TODO: fetch('../api/crear_repo.php', { method:'POST', body: JSON.stringify({nombre, desc, visib, conReadme}) })
}

// ── Render repos ──────────────────────────────────────────────────────────
const LANG_COLORS = {
    'JavaScript': '#f1e05a', 'PHP': '#4F5D95',
    'Python': '#3572A5', 'HTML': '#e34c26',
    'CSS': '#563d7c', 'Java': '#b07219',
};

function renderRepos(lista) {
    const data   = lista ?? repos;
    const grid   = document.getElementById('repoGrid');
    const recent = document.getElementById('reposRecientes');

    if (!data.length) {
        const emptyHTML = `<div class="empty">
            <svg width="36" height="36" viewBox="0 0 16 16" fill="currentColor"><path d="M2 2.5A2.5 2.5 0 014.5 0h8.75a.75.75 0 01.75.75v12.5a.75.75 0 01-.75.75h-2.5a.75.75 0 010-1.5h1.75v-2h-8a1 1 0 00-.714 1.7.75.75 0 01-1.072 1.05A2.495 2.495 0 012 11.5v-9zm10.5-1h-8a1 1 0 00-1 1v6.708A2.486 2.486 0 014.5 9h8V1.5z"/></svg>
            <p>Sin repositorios todavía.</p>
            <div class="empty-action">
                <button class="btn btn-green" onclick="abrirModalRepo()">Nuevo repositorio</button>
            </div>
        </div>`;
        grid.innerHTML = `<div style="grid-column:1/-1">${emptyHTML}</div>`;
        if (recent) recent.innerHTML = emptyHTML;
        return;
    }

    const card = r => `
    <div class="repo-item" data-vis="${r.visib}" data-nombre="${r.nombre.toLowerCase()}">
        <div class="repo-name">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M2 2.5A2.5 2.5 0 014.5 0h8.75a.75.75 0 01.75.75v12.5a.75.75 0 01-.75.75h-2.5a.75.75 0 010-1.5h1.75v-2h-8a1 1 0 00-.714 1.7.75.75 0 01-1.072 1.05A2.495 2.495 0 012 11.5v-9zm10.5-1h-8a1 1 0 00-1 1v6.708A2.486 2.486 0 014.5 9h8V1.5z"/></svg>
            ${r.nombre}
            <span class="vis-badge ${r.visib === 'public' ? 'vis-public' : 'vis-private'}">${r.visib === 'public' ? 'Público' : 'Privado'}</span>
        </div>
        ${r.desc ? `<div class="repo-desc">${r.desc}</div>` : ''}
        <div class="repo-meta">
            <span><span class="repo-lang-dot" style="background:var(--blue)"></span>PHP</span>
            <span>Actualizado ${r.fecha}</span>
        </div>
        <div class="repo-actions">
            <a class="btn btn-sm" href="#" title="Ver en GitHub">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/></svg>
                GitHub
            </a>
            <button class="btn btn-sm" onclick="eliminarRepo('${r.nombre}')" style="color:var(--red)" title="Eliminar">
                <svg width="12" height="12" viewBox="0 0 16 16" fill="currentColor"><path d="M11 1.75V3h2.25a.75.75 0 010 1.5H2.75a.75.75 0 010-1.5H5V1.75C5 .784 5.784 0 6.75 0h2.5C10.216 0 11 .784 11 1.75zM4.496 6.675l.66 6.6a.25.25 0 00.249.225h5.19a.25.25 0 00.249-.225l.66-6.6a.75.75 0 011.492.149l-.66 6.6A1.748 1.748 0 0110.595 15h-5.19a1.75 1.75 0 01-1.741-1.575l-.66-6.6a.75.75 0 111.492-.15z"/></svg>
            </button>
        </div>
    </div>`;

    grid.innerHTML = data.map(r => card(r)).join('');

    if (recent) {
        const slice = data.slice(0, 3);
        recent.innerHTML = `<div class="repo-grid" style="padding:14px">${slice.map(r => card(r)).join('')}</div>`;
    }
}

function eliminarRepo(nombre) {
    if (!confirm(`¿Eliminar el repositorio "${nombre}"? Esta acción no se puede deshacer.`)) return;
    repos = repos.filter(r => r.nombre !== nombre);
    renderRepos();
    actualizarContadores();
    // TODO: fetch('../api/eliminar_repo.php', { method:'POST', body: JSON.stringify({nombre}) })
}

function filtrarRepos() {
    const q     = document.getElementById('searchRepo').value.toLowerCase();
    const vis   = document.getElementById('filtroVisibilidad').value;
    const items = document.querySelectorAll('#repoGrid .repo-item');
    items.forEach(el => {
        const matchQ   = !q   || el.dataset.nombre.includes(q);
        const matchVis = !vis || el.dataset.vis === vis;
        el.style.display = matchQ && matchVis ? '' : 'none';
    });
}

function actualizarContadores() {
    document.getElementById('repoCount').textContent  = repos.length;
    document.getElementById('statRepos').textContent  = repos.length;
}

// ── Buscar usuarios (placeholder) ────────────────────────────────────────
function buscarUsuarios() {
    const q      = document.getElementById('globalSearch').value.trim();
    const filtro = document.getElementById('filtroBusqueda').value;
    const tbody  = document.getElementById('resultBusqueda');
    if (!q && !filtro) return;
    // TODO: fetch('../api/buscar_usuario.php?q=' + encodeURIComponent(q) + '&rol=' + filtro)
    tbody.innerHTML = `<tr><td colspan="4"><div class="empty"><p>Búsqueda de "<strong>${q || filtro}</strong>" — requiere integración con la API.</p></div></td></tr>`;
}
document.getElementById('globalSearch').addEventListener('keydown', e => {
    if (e.key === 'Enter') buscarUsuarios();
});

</script>
</body>
</html>