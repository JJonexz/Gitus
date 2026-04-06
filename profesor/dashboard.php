<?php
// ── SEGURIDAD — debe pasar por login primero ────────────────────────────────
session_start();
require_once "../includes/auth_check.php";
require_auth('profesor');

// ── Datos de sesión ─────────────────────────────────────────────────────────
$usuario        = $_SESSION['user_name'];
$rol            = $_SESSION['rol'];
$avatar_inicial = strtoupper(substr($usuario, 0, 1));
$logout_url     = "../auth/logout.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Docente — Gitus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Mona+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>

/* ── TOKENS — Identidad GitHub ───────────────────────────────────────────── */
:root {
    --bg-canvas:     #0d1117;
    --bg-overlay:    #161b22;
    --bg-subtle:     #21262d;
    --bg-muted:      #30363d;

    --border:        #30363d;
    --border-muted:  #21262d;

    --text-primary:  #e6edf3;
    --text-secondary:#8b949e;
    --text-muted:    #6e7681;

    --accent:        #238636;
    --accent-hover:  #2ea043;
    --accent-subtle: rgba(35,134,54,.15);

    --blue:          #1f6feb;
    --blue-subtle:   rgba(31,111,235,.15);

    --yellow:        #d29922;
    --yellow-subtle: rgba(210,153,34,.15);

    --red:           #da3633;
    --red-subtle:    rgba(218,54,51,.12);

    --radius:        6px;
    --radius-lg:     12px;
    --font-ui:       'Mona Sans', 'Segoe UI', sans-serif;
    --font-mono:     'JetBrains Mono', monospace;
}

/* ── RESET ───────────────────────────────────────────────────────────────── */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html { font-size:14px; }
body {
    font-family: var(--font-ui);
    background: var(--bg-canvas);
    color: var(--text-primary);
    min-height: 100vh;
    line-height: 1.5;
}
a { color: var(--blue); text-decoration: none; }
a:hover { text-decoration: underline; }

/* ── TOPBAR ──────────────────────────────────────────────────────────────── */
.topbar {
    position: sticky;
    top: 0;
    z-index: 100;
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
    font-weight: 600;
    font-size: 15px;
    color: var(--text-primary);
    text-decoration: none;
}
.topbar-logo svg { flex-shrink: 0; }
.topbar-logo:hover { text-decoration: none; }

.topbar-divider {
    width: 1px;
    height: 20px;
    background: var(--border);
}

.topbar-role-badge {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .04em;
    text-transform: uppercase;
    color: var(--yellow);
    background: var(--yellow-subtle);
    border: 1px solid rgba(210,153,34,.3);
    padding: 2px 8px;
    border-radius: 20px;
}

.topbar-right {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 12px;
}

.topbar-github-link {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--text-secondary);
    padding: 5px 10px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    transition: border-color .15s, color .15s;
}
.topbar-github-link:hover {
    color: var(--text-primary);
    border-color: var(--text-muted);
    text-decoration: none;
}

.avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--bg-subtle);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 13px;
    color: var(--text-primary);
    font-family: var(--font-mono);
    flex-shrink: 0;
    cursor: pointer;
    position: relative;
}

.avatar-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: var(--bg-overlay);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    min-width: 200px;
    box-shadow: 0 8px 32px rgba(0,0,0,.4);
    overflow: hidden;
    display: none;
}
.avatar-menu.open { display: block; }

.avatar-menu-header {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
}
.avatar-menu-header strong {
    display: block;
    font-size: 13px;
    color: var(--text-primary);
}
.avatar-menu-header span {
    font-size: 12px;
    color: var(--text-secondary);
    font-family: var(--font-mono);
}

.avatar-menu a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    font-size: 13px;
    color: var(--text-secondary);
    transition: background .12s, color .12s;
    text-decoration: none;
}
.avatar-menu a:hover { background: var(--bg-subtle); color: var(--text-primary); }
.avatar-menu a.danger { color: var(--red); }
.avatar-menu a.danger:hover { background: var(--red-subtle); }

/* ── LAYOUT ──────────────────────────────────────────────────────────────── */
.layout {
    display: flex;
    min-height: calc(100vh - 56px);
}

/* ── SIDEBAR ─────────────────────────────────────────────────────────────── */
.sidebar {
    width: 220px;
    flex-shrink: 0;
    background: var(--bg-overlay);
    border-right: 1px solid var(--border);
    padding: 20px 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.sidebar-label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--text-muted);
    padding: 4px 8px 8px;
    margin-top: 12px;
}
.sidebar-label:first-child { margin-top: 0; }

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: var(--radius);
    font-size: 13px;
    color: var(--text-secondary);
    cursor: pointer;
    transition: background .12s, color .12s;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    text-decoration: none;
}
.nav-item:hover { background: var(--bg-subtle); color: var(--text-primary); text-decoration: none; }
.nav-item.active { background: var(--bg-subtle); color: var(--text-primary); font-weight: 500; }
.nav-item svg { flex-shrink: 0; opacity: .7; }
.nav-item.active svg { opacity: 1; }

.nav-count {
    margin-left: auto;
    font-size: 11px;
    font-family: var(--font-mono);
    background: var(--bg-muted);
    color: var(--text-secondary);
    padding: 1px 6px;
    border-radius: 20px;
}

/* ── MAIN ────────────────────────────────────────────────────────────────── */
.main {
    flex: 1;
    padding: 28px 32px;
    overflow-x: hidden;
}

.page-header {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-muted);
}
.page-header h1 {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-primary);
}
.page-header p {
    margin-top: 4px;
    font-size: 13px;
    color: var(--text-secondary);
}

/* ── STAT CARDS ──────────────────────────────────────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}

.stat-card {
    background: var(--bg-overlay);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
    transition: border-color .15s;
}
.stat-card:hover { border-color: var(--text-muted); }

.stat-label {
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 8px;
    font-weight: 500;
}
.stat-value {
    font-size: 26px;
    font-weight: 700;
    color: var(--text-primary);
    font-family: var(--font-mono);
    line-height: 1;
}
.stat-sub {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 6px;
}
.stat-accent-green .stat-value { color: var(--accent-hover); }
.stat-accent-blue  .stat-value { color: var(--blue); }
.stat-accent-yellow .stat-value { color: var(--yellow); }

/* ── SECCIÓN GRID ────────────────────────────────────────────────────────── */
.section-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}
@media (max-width: 900px) { .section-grid { grid-template-columns: 1fr; } }

/* ── CARD ────────────────────────────────────────────────────────────────── */
.card {
    background: var(--bg-overlay);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
}
.card-header h2 {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}
.card-header h2 svg { opacity: .6; }

.card-action {
    font-size: 12px;
    color: var(--blue);
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: var(--radius);
    transition: background .12s;
}
.card-action:hover { background: var(--blue-subtle); text-decoration: none; }

/* ── BUSCADOR ────────────────────────────────────────────────────────────── */
.search-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-bottom: 1px solid var(--border);
    background: var(--bg-canvas);
}
.search-bar input {
    flex: 1;
    background: var(--bg-subtle);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 6px 10px;
    font-size: 13px;
    color: var(--text-primary);
    font-family: var(--font-ui);
    outline: none;
    transition: border-color .15s;
}
.search-bar input::placeholder { color: var(--text-muted); }
.search-bar input:focus { border-color: var(--blue); }

.search-bar select {
    background: var(--bg-subtle);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 6px 10px;
    font-size: 12px;
    color: var(--text-secondary);
    font-family: var(--font-ui);
    outline: none;
    cursor: pointer;
}

/* ── TABLA DE ALUMNOS ────────────────────────────────────────────────────── */
.table-wrap { overflow-x: auto; }

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
thead th {
    padding: 10px 18px;
    text-align: left;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--text-muted);
    background: var(--bg-canvas);
    border-bottom: 1px solid var(--border);
}
tbody tr {
    border-bottom: 1px solid var(--border-muted);
    transition: background .1s;
}
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: rgba(255,255,255,.02); }
tbody td { padding: 12px 18px; color: var(--text-secondary); vertical-align: middle; }
tbody td:first-child { color: var(--text-primary); }

/* ── ESTADO BADGES ───────────────────────────────────────────────────────── */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.badge-green  { background: var(--accent-subtle); color: var(--accent-hover); border: 1px solid rgba(35,134,54,.3); }
.badge-yellow { background: var(--yellow-subtle); color: var(--yellow); border: 1px solid rgba(210,153,34,.3); }
.badge-blue   { background: var(--blue-subtle);   color: var(--blue);   border: 1px solid rgba(31,111,235,.3); }
.badge-muted  { background: var(--bg-muted); color: var(--text-muted); border: 1px solid var(--border); }

/* ── ROW ACTIONS ─────────────────────────────────────────────────────────── */
.row-actions { display: flex; gap: 6px; }
.btn-icon {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: var(--radius);
    font-size: 12px;
    font-family: var(--font-ui);
    cursor: pointer;
    border: 1px solid var(--border);
    background: var(--bg-subtle);
    color: var(--text-secondary);
    transition: border-color .12s, color .12s;
    text-decoration: none;
}
.btn-icon:hover { border-color: var(--text-muted); color: var(--text-primary); text-decoration: none; }
.btn-icon.green { border-color: rgba(35,134,54,.4); color: var(--accent-hover); }
.btn-icon.green:hover { background: var(--accent-subtle); }

/* ── ACTIVIDAD RECIENTE ──────────────────────────────────────────────────── */
.activity-list { padding: 4px 0; }
.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 18px;
    border-bottom: 1px solid var(--border-muted);
    transition: background .1s;
}
.activity-item:last-child { border-bottom: none; }
.activity-item:hover { background: rgba(255,255,255,.02); }

.activity-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 5px;
}
.dot-green  { background: var(--accent); }
.dot-blue   { background: var(--blue); }
.dot-yellow { background: var(--yellow); }

.activity-text { font-size: 13px; color: var(--text-secondary); line-height: 1.4; }
.activity-text strong { color: var(--text-primary); font-weight: 500; }
.activity-time { margin-left: auto; font-size: 11px; color: var(--text-muted); font-family: var(--font-mono); flex-shrink: 0; padding-top: 3px; }

/* ── FULL WIDTH CARD ─────────────────────────────────────────────────────── */
.card-full { margin-bottom: 24px; }

/* ── EMPTY STATE ─────────────────────────────────────────────────────────── */
.empty-state {
    padding: 40px 20px;
    text-align: center;
    color: var(--text-muted);
    font-size: 13px;
}
.empty-state svg { margin-bottom: 10px; opacity: .4; }

/* ── NOTIF BANNER ────────────────────────────────────────────────────────── */
.banner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    border-radius: var(--radius-lg);
    margin-bottom: 24px;
    font-size: 13px;
    border: 1px solid;
}
.banner-yellow {
    background: var(--yellow-subtle);
    border-color: rgba(210,153,34,.3);
    color: var(--yellow);
}
.banner p { color: var(--text-secondary); }
.banner strong { color: var(--text-primary); }

/* ── RESPONSIVE ──────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .sidebar { display: none; }
    .main { padding: 20px 16px; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .topbar { padding: 0 16px; }
}

/* ── ENTRADA ─────────────────────────────────────────────────────────────── */
@keyframes fadeUp {
    from { opacity:0; transform:translateY(10px); }
    to   { opacity:1; transform:translateY(0); }
}
.main > * {
    animation: fadeUp .25s ease both;
}
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
        <!-- Gitus logo mark -->
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <rect width="20" height="20" rx="6" fill="#238636"/>
            <path d="M5 10 L10 5 L15 10 L10 15 Z" fill="white" opacity=".9"/>
        </svg>
        Gitus
    </a>
    <div class="topbar-divider"></div>
    <span class="topbar-role-badge">Profesor</span>

    <div class="topbar-right">
        <a class="topbar-github-link" href="#" title="Vincular GitHub">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/>
            </svg>
            Vincular GitHub
        </a>

        <div class="avatar" id="avatarBtn">
            <?php echo htmlspecialchars($avatar_inicial); ?>
            <div class="avatar-menu" id="avatarMenu">
                <div class="avatar-menu-header">
                    <strong><?php echo htmlspecialchars($usuario); ?></strong>
                    <span>Profesor</span>
                </div>
                <a href="#">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M10.5 5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zm.061 3.073a4 4 0 10-5.123 0 6.004 6.004 0 00-3.431 5.142.75.75 0 001.498.07 4.5 4.5 0 018.99 0 .75.75 0 101.498-.07 6.005 6.005 0 00-3.432-5.142z"/>
                    </svg>
                    Mi perfil
                </a>
                <a href="<?php echo htmlspecialchars($logout_url); ?>" class="danger">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                        <path fill-rule="evenodd" d="M2 2.75C2 1.784 2.784 1 3.75 1h5.5a.75.75 0 010 1.5h-5.5a.25.25 0 00-.25.25v10.5c0 .138.112.25.25.25h5.5a.75.75 0 010 1.5h-5.5A1.75 1.75 0 012 13.25V2.75zm10.44 4.5H6.75a.75.75 0 000 1.5h5.69l-1.97 1.97a.75.75 0 101.06 1.06l3.25-3.25a.75.75 0 000-1.06l-3.25-3.25a.75.75 0 10-1.06 1.06l1.97 1.97z"/>
                    </svg>
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
        <span class="sidebar-label">Panel</span>
        <button class="nav-item active" onclick="showSection('overview')">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M1 2.75A.75.75 0 011.75 2h12.5a.75.75 0 010 1.5H1.75A.75.75 0 011 2.75zm0 5A.75.75 0 011.75 7h12.5a.75.75 0 010 1.5H1.75A.75.75 0 011 7.75zM1.75 12a.75.75 0 000 1.5H9.25a.75.75 0 000-1.5H1.75z"/>
            </svg>
            Resumen
        </button>

        <span class="sidebar-label">Alumnos</span>
        <button class="nav-item" onclick="showSection('alumnos')">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M10.5 5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zm.061 3.073a4 4 0 10-5.123 0 6.004 6.004 0 00-3.431 5.142.75.75 0 001.498.07 4.5 4.5 0 018.99 0 .75.75 0 101.498-.07 6.005 6.005 0 00-3.432-5.142z"/>
            </svg>
            Mis alumnos
            <span class="nav-count">—</span>
        </button>
        <button class="nav-item" onclick="showSection('proyectos')">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M2 2.5A2.5 2.5 0 014.5 0h7A2.5 2.5 0 0114 2.5v11.586l-4.03-2.418a1 1 0 00-1.94 0L4 14.086V2.5z"/>
            </svg>
            Proyectos
            <span class="nav-count">—</span>
        </button>

        <span class="sidebar-label">Herramientas</span>
        <button class="nav-item" onclick="showSection('buscar')">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M10.68 11.74a6 6 0 01-7.922-8.982 6 6 0 018.982 7.922l3.04 3.04a.749.749 0 01-.326 1.275.749.749 0 01-.734-.215l-3.04-3.04zM11.5 7a4.499 4.499 0 11-8.997 0A4.499 4.499 0 0111.5 7z"/>
            </svg>
            Buscar usuario
        </button>
        <button class="nav-item" onclick="showSection('actividad')">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0zM8 0a8 8 0 100 16A8 8 0 008 0zm.5 4.75a.75.75 0 00-1.5 0v3.5a.75.75 0 00.471.696l2.5 1a.75.75 0 00.557-1.392L8.5 7.742V4.75z"/>
            </svg>
            Actividad
        </button>
    </nav>

    <!-- ── MAIN ──────────────────────────────────────────────────────────── -->
    <main class="main">

        <!-- BANNER GitHub no vinculado -->
        <div class="banner banner-yellow" id="githubBanner">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="flex-shrink:0">
                <path d="M8.22 1.754a.25.25 0 00-.44 0L1.698 13.132a.25.25 0 00.22.368h12.164a.25.25 0 00.22-.368L8.22 1.754zm-1.763-.707c.659-1.234 2.427-1.234 3.086 0l6.082 11.378A2 2 0 0113.82 15H2.18a2 2 0 01-1.885-2.575L6.457 1.047zM9 11a1 1 0 11-2 0 1 1 0 012 0zm-.25-5.25a.75.75 0 00-1.5 0v2.5a.75.75 0 001.5 0v-2.5z"/>
            </svg>
            <p><strong>Tu cuenta de GitHub no está vinculada.</strong> Vinculala para supervisar proyectos y repositorios de tus alumnos.</p>
        </div>

        <!-- ══ SECCIÓN: RESUMEN ══════════════════════════════════════════ -->
        <section id="sec-overview">
            <div class="page-header">
                <h1>Bienvenido, <?php echo htmlspecialchars($usuario); ?></h1>
                <p>Panel docente — supervisión y evaluación de proyectos académicos</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Alumnos asignados</div>
                    <div class="stat-value">—</div>
                    <div class="stat-sub">Pendiente vinculación GitHub</div>
                </div>
                <div class="stat-card stat-accent-blue">
                    <div class="stat-label">Repositorios activos</div>
                    <div class="stat-value">—</div>
                    <div class="stat-sub">Vía API GitHub</div>
                </div>
                <div class="stat-card stat-accent-yellow">
                    <div class="stat-label">Pendientes de revisión</div>
                    <div class="stat-value">—</div>
                    <div class="stat-sub">Sin entregas registradas</div>
                </div>
                <div class="stat-card stat-accent-green">
                    <div class="stat-label">Evaluados este mes</div>
                    <div class="stat-value">—</div>
                    <div class="stat-sub">Sin historial aún</div>
                </div>
            </div>

            <div class="section-grid">

                <!-- Actividad reciente -->
                <div class="card">
                    <div class="card-header">
                        <h2>
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0zM8 0a8 8 0 100 16A8 8 0 008 0zm.5 4.75a.75.75 0 00-1.5 0v3.5a.75.75 0 00.471.696l2.5 1a.75.75 0 00.557-1.392L8.5 7.742V4.75z"/>
                            </svg>
                            Actividad reciente
                        </h2>
                    </div>
                    <div class="activity-list">
                        <div class="empty-state">
                            <svg width="32" height="32" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0z"/>
                            </svg>
                            <p>Sin actividad reciente.<br>Vinculá GitHub para comenzar.</p>
                        </div>
                    </div>
                </div>

                <!-- Entregas pendientes -->
                <div class="card">
                    <div class="card-header">
                        <h2>
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M2 2.5A2.5 2.5 0 014.5 0h7A2.5 2.5 0 0114 2.5v11.586l-4.03-2.418a1 1 0 00-1.94 0L4 14.086V2.5z"/>
                            </svg>
                            Entregas pendientes
                        </h2>
                        <button class="card-action">Ver todas</button>
                    </div>
                    <div class="empty-state">
                        <svg width="32" height="32" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M2 2.5A2.5 2.5 0 014.5 0h7A2.5 2.5 0 0114 2.5v11.586l-4.03-2.418a1 1 0 00-1.94 0L4 14.086V2.5z"/>
                        </svg>
                        <p>No hay entregas pendientes.<br>Los proyectos aparecerán aquí.</p>
                    </div>
                </div>

            </div>
        </section>

        <!-- ══ SECCIÓN: ALUMNOS ══════════════════════════════════════════ -->
        <section id="sec-alumnos" style="display:none">
            <div class="page-header">
                <h1>Mis alumnos</h1>
                <p>Listado de alumnos asignados — sus repositorios y estado de entrega</p>
            </div>

            <div class="card card-full">
                <div class="search-bar">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" style="color:var(--text-muted);flex-shrink:0">
                        <path d="M10.68 11.74a6 6 0 01-7.922-8.982 6 6 0 018.982 7.922l3.04 3.04a.749.749 0 01-.326 1.275.749.749 0 01-.734-.215l-3.04-3.04zM11.5 7a4.499 4.499 0 11-8.997 0A4.499 4.499 0 0111.5 7z"/>
                    </svg>
                    <input type="text" id="searchAlumnos" placeholder="Buscar por nombre o DNI..." oninput="filtrarTabla()">
                    <select id="filtroEstado" onchange="filtrarTabla()">
                        <option value="">Todos los estados</option>
                        <option value="entregado">Entregado</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en progreso">En progreso</option>
                    </select>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Alumno</th>
                                <th>GitHub</th>
                                <th>Repositorios</th>
                                <th>Estado entrega</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaAlumnos">
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <p>Los alumnos vinculados a tu cuenta aparecerán aquí.<br>Requiere integración con la API de GitHub.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ══ SECCIÓN: PROYECTOS ════════════════════════════════════════ -->
        <section id="sec-proyectos" style="display:none">
            <div class="page-header">
                <h1>Proyectos</h1>
                <p>Repositorios académicos de tus alumnos vía API de GitHub</p>
            </div>

            <div class="card card-full">
                <div class="search-bar">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" style="color:var(--text-muted);flex-shrink:0">
                        <path d="M10.68 11.74a6 6 0 01-7.922-8.982 6 6 0 018.982 7.922l3.04 3.04a.749.749 0 01-.326 1.275.749.749 0 01-.734-.215l-3.04-3.04z"/>
                    </svg>
                    <input type="text" placeholder="Buscar repositorio...">
                    <select>
                        <option>Todos</option>
                        <option>Públicos</option>
                        <option>Privados</option>
                    </select>
                </div>
                <div class="empty-state" style="padding:48px 20px">
                    <svg width="40" height="40" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M2 2.5A2.5 2.5 0 014.5 0h7A2.5 2.5 0 0114 2.5v11.586l-4.03-2.418a1 1 0 00-1.94 0L4 14.086V2.5z"/>
                    </svg>
                    <p style="margin-top:12px">Sin repositorios cargados.<br>
                    Se mostrarán al vincular GitHub y cuando tus alumnos creen proyectos.</p>
                </div>
            </div>
        </section>

        <!-- ══ SECCIÓN: BUSCAR USUARIO ═══════════════════════════════════ -->
        <section id="sec-buscar" style="display:none">
            <div class="page-header">
                <h1>Buscar usuario</h1>
                <p>Buscador de alumnos dentro del sistema Gitus</p>
            </div>

            <div class="card card-full">
                <div class="search-bar" style="padding:16px 18px; gap:12px">
                    <input type="text" id="globalSearch" placeholder="Nombre, DNI o email institucional..." style="font-size:14px; padding:10px 14px">
                    <button class="btn-icon green" onclick="buscarUsuario()">
                        <svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M10.68 11.74a6 6 0 01-7.922-8.982 6 6 0 018.982 7.922l3.04 3.04a.749.749 0 01-.326 1.275.749.749 0 01-.734-.215l-3.04-3.04z"/>
                        </svg>
                        Buscar
                    </button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>DNI</th>
                                <th>GitHub</th>
                                <th>Último acceso</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="resultadosBusqueda">
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <p>Ingresá un término para buscar.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ══ SECCIÓN: ACTIVIDAD ════════════════════════════════════════ -->
        <section id="sec-actividad" style="display:none">
            <div class="page-header">
                <h1>Historial de actividad</h1>
                <p>Eventos recientes de tus alumnos en Gitus y GitHub</p>
            </div>

            <div class="card card-full">
                <div class="activity-list">
                    <div class="empty-state" style="padding:48px 20px">
                        <svg width="40" height="40" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0z"/>
                        </svg>
                        <p style="margin-top:12px">Sin actividad registrada.<br>
                        El historial aparecerá cuando los alumnos operen en el sistema.</p>
                    </div>
                </div>
            </div>
        </section>

    </main>
</div><!-- /layout -->

<script>

// ── Navegación entre secciones ───────────────────────────────────────────
const sections = ['overview','alumnos','proyectos','buscar','actividad'];

function showSection(id) {
    sections.forEach(s => {
        const el = document.getElementById('sec-' + s);
        if (el) el.style.display = (s === id) ? 'block' : 'none';
    });
    document.querySelectorAll('.nav-item').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('onclick') === `showSection('${id}')`);
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

// ── Filtro tabla alumnos ──────────────────────────────────────────────────
function filtrarTabla() {
    const q      = document.getElementById('searchAlumnos').value.toLowerCase();
    const estado = document.getElementById('filtroEstado').value.toLowerCase();
    const filas  = document.querySelectorAll('#tablaAlumnos tr');
    filas.forEach(row => {
        const texto  = row.textContent.toLowerCase();
        const matchQ = !q      || texto.includes(q);
        const matchE = !estado || texto.includes(estado);
        row.style.display = (matchQ && matchE) ? '' : 'none';
    });
}

// ── Buscar usuario (placeholder — conectar con API PHP) ───────────────────
function buscarUsuario() {
    const q = document.getElementById('globalSearch').value.trim();
    if (!q) return;
    // TODO: fetch('../api/buscar_usuario.php?q=' + encodeURIComponent(q))
    //       .then(r => r.json()).then(renderResultados)
    document.getElementById('resultadosBusqueda').innerHTML =
        `<tr><td colspan="5"><div class="empty-state"><p>Búsqueda de "<strong>${q}</strong>" — requiere integración API.</p></div></td></tr>`;
}

// ── Cerrar banner GitHub ──────────────────────────────────────────────────
// (se quitará automáticamente cuando github_id esté vinculado en sesión)
// Por ahora se puede cerrar manualmente
document.getElementById('githubBanner').addEventListener('dblclick', function() {
    this.style.display = 'none';
});

</script>
</body>
</html>