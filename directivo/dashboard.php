<?php
session_start();

// 1. Guard — redirige si no hay sesión o el rol no coincide
require_once "../includes/auth_check.php";
require_auth('directivo');

// 2. Datos reales desde sesión
$usuario        = $_SESSION['user_name'];
$rol            = $_SESSION['rol'];
$avatar_inicial = strtoupper(substr($usuario, 0, 1));

// 3. Ruta de logout relativa a este archivo
$logout_url = "../auth/logout.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Gitus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>

/* ── TOKENS ─────────────────────────────────────────────── */
:root{
  --bg:        #0d1117;
  --surface:   #161b22;
  --surface2:  #1c2128;
  --border:    #30363d;
  --border2:   #21262d;
  --text:      #e6edf3;
  --muted:     #7d8590;
  --accent:    #2ea043;
  --accent-hi: #3fb950;
  --accent-dim:#238636;
  --blue:      #388bfd;
  --blue-dim:  #1f6feb;
  --orange:    #d29922;
  --red:       #f85149;
  --purple:    #8957e5;
  --cyan:      #39c5cf;
  --sidebar-w: 220px;
  --right-w:   290px;
  --topbar-h:  52px;
  --radius:    6px;
  --radius-lg: 10px;
}

/* ── RESET ──────────────────────────────────────────────── */
*{margin:0;padding:0;box-sizing:border-box}
body{
  font-family:'Outfit',sans-serif;
  font-size:14px;
  background:var(--bg);
  color:var(--text);
  overflow-x:hidden;
}
a{color:inherit;text-decoration:none}
button{font-family:inherit;cursor:pointer}
ul{list-style:none}

/* ── TOPBAR ─────────────────────────────────────────────── */
.topbar{
  position:fixed;top:0;left:0;right:0;
  height:var(--topbar-h);
  background:var(--surface);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  padding:0 20px;
  z-index:100;
  gap:16px;
}
.topbar-left{display:flex;align-items:center;gap:16px}

.logo{
  display:flex;align-items:center;gap:8px;
  font-size:15px;font-weight:700;
  color:var(--text);
  white-space:nowrap;
}
.logo-icon{
  width:28px;height:28px;
  background:linear-gradient(135deg,var(--accent-dim),var(--accent-hi));
  border-radius:6px;
  display:flex;align-items:center;justify-content:center;
  font-size:14px;font-weight:800;color:#fff;
  font-family:'JetBrains Mono',monospace;
}

.topbar-search{
  display:flex;align-items:center;gap:8px;
  background:var(--bg);
  border:1px solid var(--border);
  border-radius:var(--radius);
  padding:6px 12px;
  width:260px;
  transition:border-color .2s,width .3s;
}
.topbar-search:focus-within{border-color:var(--blue-dim);width:320px}
.topbar-search input{
  background:none;border:none;outline:none;
  color:var(--text);font-family:'Outfit',sans-serif;font-size:13px;
  width:100%;
}
.topbar-search input::placeholder{color:var(--muted)}
.topbar-search kbd{
  font-family:'JetBrains Mono',monospace;font-size:11px;
  color:var(--muted);background:var(--surface2);
  border:1px solid var(--border);border-radius:4px;
  padding:1px 5px;white-space:nowrap;
}

.topbar-right{display:flex;align-items:center;gap:10px}

.icon-btn{
  background:none;border:none;
  padding:6px;border-radius:var(--radius);
  color:var(--muted);
  position:relative;
  transition:color .15s,background .15s;
}
.icon-btn:hover{color:var(--text);background:var(--border2)}

.notif-badge{
  position:absolute;top:4px;right:4px;
  width:7px;height:7px;
  background:var(--orange);border-radius:50%;
  border:1.5px solid var(--surface);
}

.avatar-btn{
  width:30px;height:30px;
  background:linear-gradient(135deg,var(--blue-dim),var(--purple));
  border-radius:50%;
  border:2px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  font-size:12px;font-weight:700;color:#fff;
  cursor:pointer;transition:border-color .15s;
}
.avatar-btn:hover{border-color:var(--accent)}

/* ── LAYOUT ─────────────────────────────────────────────── */
.shell{
  display:flex;
  margin-top:var(--topbar-h);
  min-height:calc(100vh - var(--topbar-h));
}

/* ── SIDEBAR ────────────────────────────────────────────── */
.sidebar{
  width:var(--sidebar-w);
  min-width:var(--sidebar-w);
  background:var(--surface);
  border-right:1px solid var(--border);
  display:flex;flex-direction:column;
  padding:16px 12px;
  position:fixed;
  top:var(--topbar-h);
  height:calc(100vh - var(--topbar-h));
  overflow-y:auto;
}

.sidebar-profile{
  display:flex;align-items:center;gap:10px;
  padding:10px;
  background:var(--surface2);
  border:1px solid var(--border);
  border-radius:var(--radius-lg);
  margin-bottom:20px;
}
.sidebar-avatar{
  width:32px;height:32px;
  background:linear-gradient(135deg,var(--blue-dim),var(--purple));
  border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:13px;font-weight:700;color:#fff;
  flex-shrink:0;
}
.sidebar-profile-info{flex:1;min-width:0}
.sidebar-profile-name{
  font-size:13px;font-weight:600;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.sidebar-profile-role{
  font-size:11px;color:var(--muted);
  font-family:'JetBrains Mono',monospace;
}
.sidebar-status{
  width:8px;height:8px;background:var(--accent-hi);
  border-radius:50%;flex-shrink:0;
  box-shadow:0 0 6px var(--accent-hi);
}

.sidebar-label{
  font-size:10px;font-weight:600;
  text-transform:uppercase;letter-spacing:.08em;
  color:var(--muted);
  padding:0 8px;
  margin:14px 0 4px;
}

.nav-item{
  display:flex;align-items:center;gap:9px;
  padding:7px 10px;
  border-radius:var(--radius);
  color:var(--muted);
  cursor:pointer;
  font-size:13px;font-weight:500;
  transition:background .15s,color .15s;
  position:relative;
}
.nav-item:hover{background:var(--border2);color:var(--text)}
.nav-item.active{
  background:var(--border2);
  color:var(--text);
}
.nav-item.active::before{
  content:'';
  position:absolute;left:0;top:20%;bottom:20%;
  width:3px;background:var(--accent-hi);
  border-radius:0 3px 3px 0;
}
.nav-item svg{flex-shrink:0;opacity:.8}
.nav-item.active svg{opacity:1}
.nav-badge{
  margin-left:auto;
  font-size:10px;font-weight:600;
  background:var(--accent-dim);color:#fff;
  padding:1px 6px;border-radius:10px;
  font-family:'JetBrains Mono',monospace;
}
.nav-badge.orange{background:#9a4b00}
.nav-badge.blue{background:var(--blue-dim)}

.sidebar-divider{
  height:1px;background:var(--border);
  margin:14px 0;
}

.sidebar-bottom{margin-top:auto;padding-top:10px}

/* ── MAIN ───────────────────────────────────────────────── */
.main{
  flex:1;
  margin-left:var(--sidebar-w);
  margin-right:var(--right-w);
  padding:28px 28px;
  min-width:0;
}

/* ── PAGE HEADER ────────────────────────────────────────── */
.page-header{
  display:flex;align-items:flex-start;justify-content:space-between;
  margin-bottom:24px;
}
.page-header-left{}
.breadcrumb{
  font-size:11px;color:var(--muted);
  font-family:'JetBrains Mono',monospace;
  margin-bottom:4px;
}
.breadcrumb span{color:var(--accent-hi)}
.page-title{
  font-size:22px;font-weight:700;
  letter-spacing:-.02em;
}
.page-subtitle{
  font-size:13px;color:var(--muted);margin-top:3px;
}
.page-header-actions{display:flex;gap:8px;align-items:center}

/* ── BUTTONS ────────────────────────────────────────────── */
.btn{
  display:inline-flex;align-items:center;gap:6px;
  padding:7px 14px;border-radius:var(--radius);
  font-family:'Outfit',sans-serif;font-size:13px;font-weight:500;
  border:1px solid transparent;
  transition:all .15s;
}
.btn-green{
  background:var(--accent-dim);color:#fff;
  border-color:rgba(46,160,67,.3);
}
.btn-green:hover{background:var(--accent-hi);border-color:var(--accent-hi)}
.btn-ghost{
  background:var(--surface2);color:var(--text);
  border-color:var(--border);
}
.btn-ghost:hover{background:var(--border2);border-color:var(--muted)}
.btn-outline-red{
  background:transparent;color:var(--red);
  border-color:rgba(248,81,73,.3);
}
.btn-outline-red:hover{background:rgba(248,81,73,.1)}

/* ── STATS ──────────────────────────────────────────────── */
.stats{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:14px;
  margin-bottom:24px;
}
.stat-card{
  background:var(--surface);
  border:1px solid var(--border);
  border-radius:var(--radius-lg);
  padding:18px;
  position:relative;
  overflow:hidden;
  transition:border-color .2s,transform .2s;
}
.stat-card:hover{
  border-color:var(--border2);
  transform:translateY(-1px);
}
.stat-card::before{
  content:'';
  position:absolute;top:0;left:0;right:0;
  height:2px;
}
.stat-card.green::before{background:linear-gradient(90deg,transparent,var(--accent-hi),transparent)}
.stat-card.blue::before{background:linear-gradient(90deg,transparent,var(--blue),transparent)}
.stat-card.orange::before{background:linear-gradient(90deg,transparent,var(--orange),transparent)}
.stat-card.purple::before{background:linear-gradient(90deg,transparent,var(--purple),transparent)}

.stat-top{
  display:flex;justify-content:space-between;align-items:flex-start;
  margin-bottom:10px;
}
.stat-icon{
  width:32px;height:32px;
  border-radius:var(--radius);
  display:flex;align-items:center;justify-content:center;
}
.stat-icon.green{background:rgba(46,160,67,.12);color:var(--accent-hi)}
.stat-icon.blue{background:rgba(56,139,253,.12);color:var(--blue)}
.stat-icon.orange{background:rgba(210,153,34,.12);color:var(--orange)}
.stat-icon.purple{background:rgba(137,87,229,.12);color:var(--purple)}

.stat-trend{
  font-size:11px;font-weight:600;
  font-family:'JetBrains Mono',monospace;
  display:flex;align-items:center;gap:2px;
}
.stat-trend.up{color:var(--accent-hi)}
.stat-trend.down{color:var(--red)}
.stat-trend.neutral{color:var(--muted)}

.stat-value{
  font-size:28px;font-weight:700;
  letter-spacing:-.03em;
  line-height:1;
  margin-bottom:4px;
  font-family:'JetBrains Mono',monospace;
}
.stat-label{font-size:12px;color:var(--muted);font-weight:500}
.stat-sub{
  font-size:11px;color:var(--muted);
  margin-top:8px;padding-top:8px;
  border-top:1px solid var(--border2);
}

/* ── MINI SPARKLINE ─────────────────────────────────────── */
.sparkline{
  width:60px;height:20px;
  opacity:.7;
}

/* ── CONTENT GRID ───────────────────────────────────────── */
.content-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:18px;
}
.content-grid .full{grid-column:1/-1}

/* ── CARD ───────────────────────────────────────────────── */
.card{
  background:var(--surface);
  border:1px solid var(--border);
  border-radius:var(--radius-lg);
  overflow:hidden;
}
.card-header{
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 18px;
  border-bottom:1px solid var(--border);
}
.card-title{
  font-size:13px;font-weight:600;
  display:flex;align-items:center;gap:7px;
}
.card-header-actions{display:flex;gap:6px}
.card-body{padding:0}

/* tab bar */
.tab-bar{
  display:flex;
  border-bottom:1px solid var(--border);
  padding:0 18px;
}
.tab{
  padding:10px 0;margin-right:20px;
  font-size:13px;font-weight:500;color:var(--muted);
  cursor:pointer;
  border-bottom:2px solid transparent;
  transition:color .15s,border-color .15s;
  white-space:nowrap;
}
.tab.active{color:var(--text);border-color:var(--orange)}
.tab:hover:not(.active){color:var(--text)}
.tab-count{
  display:inline-flex;align-items:center;justify-content:center;
  background:var(--border2);
  font-size:10px;font-weight:600;
  padding:1px 5px;border-radius:10px;
  margin-left:4px;
  font-family:'JetBrains Mono',monospace;
}

/* ── ACTIVITY FEED ──────────────────────────────────────── */
.activity-list{padding:8px 0}
.activity-item{
  display:flex;align-items:flex-start;gap:12px;
  padding:11px 18px;
  transition:background .15s;
}
.activity-item:hover{background:var(--surface2)}
.activity-dot{
  width:28px;height:28px;
  border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:11px;font-weight:700;color:#fff;
  flex-shrink:0;
  margin-top:1px;
}
.ad-green{background:linear-gradient(135deg,#1a7f37,var(--accent-hi))}
.ad-blue{background:linear-gradient(135deg,var(--blue-dim),var(--blue))}
.ad-purple{background:linear-gradient(135deg,#6e40c9,var(--purple))}
.ad-orange{background:linear-gradient(135deg,#9a4b00,var(--orange))}
.ad-red{background:linear-gradient(135deg,#b91c1c,var(--red))}

.activity-content{flex:1;min-width:0}
.activity-main{
  font-size:13px;line-height:1.5;color:var(--text);
}
.activity-main strong{font-weight:600}
.activity-main .hl{color:var(--blue);font-weight:500}
.activity-meta{
  font-size:11px;color:var(--muted);
  margin-top:2px;
  font-family:'JetBrains Mono',monospace;
}
.activity-right{flex-shrink:0}
.tag{
  font-size:10px;font-weight:600;
  padding:2px 8px;border-radius:10px;
  font-family:'JetBrains Mono',monospace;
  border:1px solid;
}
.tag-green{background:rgba(46,160,67,.12);color:var(--accent-hi);border-color:rgba(46,160,67,.25)}
.tag-blue{background:rgba(56,139,253,.12);color:var(--blue);border-color:rgba(56,139,253,.25)}
.tag-orange{background:rgba(210,153,34,.12);color:var(--orange);border-color:rgba(210,153,34,.25)}
.tag-red{background:rgba(248,81,73,.12);color:var(--red);border-color:rgba(248,81,73,.25)}
.tag-muted{background:var(--surface2);color:var(--muted);border-color:var(--border)}

.feed-divider{
  display:flex;align-items:center;gap:8px;
  padding:6px 18px;
}
.feed-divider-line{flex:1;height:1px;background:var(--border)}
.feed-divider-date{
  font-size:10px;color:var(--muted);
  font-family:'JetBrains Mono',monospace;
  white-space:nowrap;
}

/* ── QUICK TABLE ────────────────────────────────────────── */
.mini-table{width:100%;border-collapse:collapse}
.mini-table th{
  font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;
  color:var(--muted);padding:8px 18px;
  text-align:left;border-bottom:1px solid var(--border);
}
.mini-table td{
  padding:10px 18px;
  font-size:13px;
  border-bottom:1px solid var(--border2);
}
.mini-table tr:last-child td{border-bottom:none}
.mini-table tr:hover td{background:var(--surface2)}
.status-dot{
  display:inline-flex;align-items:center;gap:5px;
}
.status-dot::before{
  content:'';width:6px;height:6px;border-radius:50%;flex-shrink:0;
}
.s-active::before{background:var(--accent-hi);box-shadow:0 0 5px var(--accent-hi)}
.s-inactive::before{background:var(--muted)}
.s-warn::before{background:var(--orange);box-shadow:0 0 5px var(--orange)}

.progress-bar{
  width:80px;height:5px;
  background:var(--border);border-radius:10px;
  overflow:hidden;
}
.progress-fill{
  height:100%;border-radius:10px;
}

/* ── RIGHT PANEL ────────────────────────────────────────── */
.right-panel{
  width:var(--right-w);
  min-width:var(--right-w);
  background:var(--surface);
  border-left:1px solid var(--border);
  padding:20px 16px;
  position:fixed;
  right:0;top:var(--topbar-h);
  height:calc(100vh - var(--topbar-h));
  overflow-y:auto;
  display:flex;flex-direction:column;gap:16px;
}

.panel-block{
  background:var(--bg);
  border:1px solid var(--border);
  border-radius:var(--radius-lg);
  overflow:hidden;
}
.panel-block-header{
  padding:11px 14px;
  border-bottom:1px solid var(--border);
  font-size:12px;font-weight:600;
  display:flex;align-items:center;justify-content:space-between;
}
.panel-block-header svg{color:var(--muted)}
.see-all{
  font-size:11px;color:var(--blue);
  font-weight:500;cursor:pointer;
}
.see-all:hover{text-decoration:underline}

/* ── CONTRIB HEATMAP ────────────────────────────────────── */
.heatmap-wrap{padding:12px 14px}
.heatmap-label{
  font-size:10px;color:var(--muted);
  font-family:'JetBrains Mono',monospace;
  margin-bottom:6px;
}
.heatmap-grid{
  display:flex;gap:2px;flex-wrap:wrap;
}
.hm-cell{
  width:9px;height:9px;border-radius:2px;
  background:var(--border);
  transition:transform .1s;
}
.hm-cell:hover{transform:scale(1.4)}
.hm-0{background:#161b22;border:1px solid var(--border)}
.hm-1{background:#0e4429}
.hm-2{background:#006d32}
.hm-3{background:#26a641}
.hm-4{background:#39d353}

/* ── NOTIF LIST ─────────────────────────────────────────── */
.notif-item{
  display:flex;align-items:flex-start;gap:10px;
  padding:10px 14px;
  border-bottom:1px solid var(--border2);
  cursor:pointer;transition:background .15s;
}
.notif-item:last-child{border-bottom:none}
.notif-item:hover{background:var(--surface2)}
.notif-icon{
  width:28px;height:28px;border-radius:var(--radius);
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;
}
.ni-orange{background:rgba(210,153,34,.12);color:var(--orange)}
.ni-red{background:rgba(248,81,73,.12);color:var(--red)}
.ni-blue{background:rgba(56,139,253,.12);color:var(--blue)}
.ni-green{background:rgba(46,160,67,.12);color:var(--accent-hi)}
.notif-text{font-size:12px;line-height:1.4;flex:1}
.notif-text b{font-weight:600}
.notif-time{
  font-size:10px;color:var(--muted);
  font-family:'JetBrains Mono',monospace;
  white-space:nowrap;margin-top:2px;
}

/* ── EVENT LIST ─────────────────────────────────────────── */
.event-item{
  display:flex;align-items:center;gap:10px;
  padding:9px 14px;
  border-bottom:1px solid var(--border2);
  cursor:pointer;transition:background .15s;
}
.event-item:last-child{border-bottom:none}
.event-item:hover{background:var(--surface2)}
.event-date-box{
  width:34px;text-align:center;flex-shrink:0;
}
.event-day{
  font-size:18px;font-weight:700;line-height:1;
  font-family:'JetBrains Mono',monospace;
}
.event-mon{
  font-size:9px;text-transform:uppercase;
  color:var(--muted);font-weight:600;letter-spacing:.06em;
}
.event-info{flex:1;min-width:0}
.event-name{font-size:12px;font-weight:500;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.event-room{font-size:11px;color:var(--muted);margin-top:1px}
.event-badge{
  width:8px;height:8px;border-radius:50%;flex-shrink:0;
}

/* ── QUICK ACTIONS ──────────────────────────────────────── */
.quick-grid{
  display:grid;grid-template-columns:1fr 1fr;gap:8px;
  padding:12px;
}
.quick-btn{
  display:flex;flex-direction:column;align-items:center;gap:5px;
  padding:12px 8px;
  background:var(--surface);border:1px solid var(--border);
  border-radius:var(--radius);cursor:pointer;
  transition:all .15s;color:var(--muted);
  font-size:11px;font-weight:500;text-align:center;line-height:1.3;
}
.quick-btn:hover{border-color:var(--border2);color:var(--text);background:var(--border2)}
.quick-btn svg{color:var(--muted)}
.quick-btn:hover svg{color:var(--accent-hi)}

/* ── SCROLLBAR ──────────────────────────────────────────── */
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:10px}
::-webkit-scrollbar-thumb:hover{background:var(--muted)}

/* ── RESPONSIVE ─────────────────────────────────────────── */
@media(max-width:1280px){
  .right-panel{display:none}
  .main{margin-right:0}
  .stats{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:860px){
  .sidebar{display:none}
  .main{margin-left:0;padding:16px}
  .content-grid{grid-template-columns:1fr}
}

/* ── ANIMATIONS ─────────────────────────────────────────── */
@keyframes fadeUp{
  from{opacity:0;transform:translateY(12px)}
  to{opacity:1;transform:translateY(0)}
}
.stat-card{animation:fadeUp .4s ease both}
.stat-card:nth-child(1){animation-delay:.05s}
.stat-card:nth-child(2){animation-delay:.1s}
.stat-card:nth-child(3){animation-delay:.15s}
.stat-card:nth-child(4){animation-delay:.2s}
.card{animation:fadeUp .4s ease .25s both}

</style>
</head>
<body>

<!-- ════════════════════ TOPBAR ════════════════════ -->
<header class="topbar">
  <div class="topbar-left">
    <div class="logo">
      <div class="logo-icon">G</div>
      Gitus
    </div>
    <div class="topbar-search">
      <!-- search icon -->
      <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M10.9 10.9l3.1 3.1M7 12A5 5 0 107 2a5 5 0 000 10z" stroke="#7d8590" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
      <input type="text" placeholder="Buscar alumnos, cursos, docentes…">
      <kbd>/</kbd>
    </div>
  </div>
  <div class="topbar-right">
    <!-- notif -->
    <button class="icon-btn" title="Notificaciones">
      <span class="notif-badge"></span>
      <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 16a2 2 0 001.985-1.75c.017-.137-.097-.25-.235-.25h-3.5c-.138 0-.252.113-.235.25A2 2 0 008 16zm6-4l-1-1V7.5C13 4.466 10.534 2 7.5 2S2 4.466 2 7.5V11l-1 1H3v1h10v-1h2z"/></svg>
    </button>
    <!-- plus -->
    <button class="icon-btn btn-ghost btn" style="gap:4px;padding:5px 10px;font-size:12px;">
      <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M7.75 2a.75.75 0 01.75.75V7h4.25a.75.75 0 010 1.5H8.5v4.25a.75.75 0 01-1.5 0V8.5H2.75a.75.75 0 010-1.5H7V2.75A.75.75 0 017.75 2z"/></svg>
      Nuevo
    </button>
    <div class="avatar-btn" title="<?= htmlspecialchars($usuario) ?>"><?= $avatar_inicial ?></div>
  </div>
</header>

<!-- ════════════════════ SHELL ════════════════════ -->
<div class="shell">

<!-- ─────────── SIDEBAR ─────────── -->
<aside class="sidebar">

  <div class="sidebar-profile">
    <div class="sidebar-avatar"><?= $avatar_inicial ?></div>
    <div class="sidebar-profile-info">
      <div class="sidebar-profile-name"><?= htmlspecialchars($usuario) ?></div>
      <div class="sidebar-profile-role">admin</div>
    </div>
    <div class="sidebar-status" title="En línea"></div>
  </div>

  <span class="sidebar-label">Principal</span>
  <ul>
    <li class="nav-item active">
      <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M6.5 1a.5.5 0 00-.5.5v3a.5.5 0 00.5.5h3a.5.5 0 00.5-.5v-3a.5.5 0 00-.5-.5h-3zm0 6a.5.5 0 00-.5.5v3a.5.5 0 00.5.5h3a.5.5 0 00.5-.5v-3a.5.5 0 00-.5-.5h-3zm-5 6a.5.5 0 00-.5.5v3a.5.5 0 00.5.5h3a.5.5 0 00.5-.5v-3a.5.5 0 00-.5-.5h-3zm5 0a.5.5 0 00-.5.5v3a.5.5 0 00.5.5h3a.5.5 0 00.5-.5v-3a.5.5 0 00-.5-.5h-3zm-5-6a.5.5 0 00-.5.5v3a.5.5 0 00.5.5h3a.5.5 0 00.5-.5v-3a.5.5 0 00-.5-.5h-3zm0-6a.5.5 0 00-.5.5v3a.5.5 0 00.5.5h3a.5.5 0 00.5-.5v-3a.5.5 0 00-.5-.5h-3z"/></svg>
      Dashboard
    </li>
    <li class="nav-item">
      <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M11.134 1.535C9.722 1.832 8 2.525 8 4c0 1.475 1.722 2.168 3.134 2.465 1.045.215 1.866.515 1.866 1.035 0 .52-.821.82-1.866 1.035C9.722 8.832 8 9.525 8 11c0 1.475 1.722 2.168 3.134 2.465.672.14 1.134.365 1.134.535s-.462.395-1.134.535C9.722 14.832 8 15.525 8 17h1c0-.52.821-.82 1.866-1.035 1.412-.297 3.134-.99 3.134-2.465 0-1.475-1.722-2.168-3.134-2.465C9.822 10.82 9 10.52 9 11c0-.52.822-.82 1.866-1.035C12.278 9.668 14 8.975 14 7.5c0-1.475-1.722-2.168-3.134-2.465C9.822 4.82 9 4.52 9 4c0-.52.822-.82 1.866-1.035L11.134 1.535z"/></svg>
      Actividad
      <span class="nav-badge">12</span>
    </li>
  </ul>

  <span class="sidebar-label">Académico</span>
  <ul>
    <li class="nav-item">
      <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M8 8a3 3 0 100-6 3 3 0 000 6zm4 1H4a4 4 0 00-4 4v.5a.5.5 0 00.5.5h15a.5.5 0 00.5-.5V13a4 4 0 00-4-4z"/></svg>
      Alumnos
      <span class="nav-badge blue">320</span>
    </li>
    <li class="nav-item">
      <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M11 5a3 3 0 11-6 0 3 3 0 016 0zM8 7a2 2 0 100-4 2 2 0 000 4zm.256 7a4.474 4.474 0 01-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025a4.548 4.548 0 01.515-.877A6.578 6.578 0 008 9c-5 0-6 3-6 4s1 1 1 1h5.256zm-3.2-6.924V11h.8V7.076h-.8zm.4-.325a.325.325 0 100-.65.325.325 0 000 .65z"/></svg>
      Docentes
      <span class="nav-badge">24</span>
    </li>
    <li class="nav-item">
      <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M1 2.5A1.5 1.5 0 012.5 1h3A1.5 1.5 0 017 2.5v3A1.5 1.5 0 015.5 7h-3A1.5 1.5 0 011 5.5v-3zm8 0A1.5 1.5 0 0110.5 1h3A1.5 1.5 0 0115 2.5v3A1.5 1.5 0 0113.5 7h-3A1.5 1.5 0 019 5.5v-3zm-8 8A1.5 1.5 0 012.5 9h3A1.5 1.5 0 017 10.5v3A1.5 1.5 0 015.5 15h-3A1.5 1.5 0 011 13.5v-3zm8 0A1.5 1.5 0 0110.5 9h3A1.5 1.5 0 0115 10.5v3A1.5 1.5 0 0113.5 15h-3A1.5 1.5 0 019 13.5v-3z"/></svg>
      Cursos
      <span class="nav-badge">12</span>
    </li>
    <li class="nav-item">
      <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M2 2a2 2 0 012-2h8a2 2 0 012 2v13.5a.5.5 0 01-.777.416L8 13.101l-5.223 2.815A.5.5 0 012 15.5V2zm2-1a1 1 0 00-1 1v12.566l4.723-2.482a.5.5 0 01.554 0L13 14.566V2a1 1 0 00-1-1H4z"/></svg>
      Materias
    </li>
    <li class="nav-item">
      <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M0 2a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H2a2 2 0 01-2-2V2zm3.5 1a.5.5 0 000 1h9a.5.5 0 000-1h-9zm0 3a.5.5 0 000 1h9a.5.5 0 000-1h-9zm0 3a.5.5 0 000 1h5a.5.5 0 000-1h-5z"/></svg>
      Calificaciones
      <span class="nav-badge orange">3</span>
    </li>
  </ul>

  <div class="sidebar-divider"></div>

  <span class="sidebar-label">Administración</span>
  <ul>
    <li class="nav-item">
      <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M8 8a3 3 0 100-6 3 3 0 000 6zm2-3a2 2 0 11-4 0 2 2 0 014 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/></svg>
      Usuarios
    </li>
    <li class="nav-item">
      <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M9 1H7v5.586L4.707 4.293 3.293 5.707 7 9.414V15h2V9.414l3.707-3.707-1.414-1.414L9 6.586V1z"/></svg>
      Reportes
    </li>
    <li class="nav-item">
      <svg width="15" height="15" viewBox="0 0 16 16" fill="currentColor"><path d="M8 4.754a3.246 3.246 0 100 6.492 3.246 3.246 0 000-6.492zM5.754 8a2.246 2.246 0 114.492 0 2.246 2.246 0 01-4.492 0z"/><path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 01-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 01-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 01.52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 011.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 011.255-.52l.292.16c1.64.893 3.433-.902 2.54-2.541l-.159-.292a.873.873 0 01.52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 01-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 01-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 002.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 001.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 00-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 00-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 00-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 001.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 003.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 002.692-1.115l.094-.319z"/></svg>
      Configuración
    </li>
  </ul>

  <div class="sidebar-bottom">
    <div class="nav-item" style="font-size:12px;">
      <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/></svg>
      Versión 2.1.0
    </div>
  </div>

</aside>

<!-- ─────────── MAIN ─────────── -->
<main class="main">

  <div class="page-header">
    <div class="page-header-left">
      <div class="breadcrumb">gitus / <span>admin</span> / dashboard</div>
      <h1 class="page-title">Panel de administración</h1>
      <p class="page-subtitle">Lunes, 30 de marzo de 2026 — Ciclo lectivo activo</p>
    </div>
    <div class="page-header-actions">
      <button class="btn btn-ghost">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor"><path d="M1 2.5A1.5 1.5 0 012.5 1h3A1.5 1.5 0 017 2.5v3A1.5 1.5 0 015.5 7h-3A1.5 1.5 0 011 5.5v-3zm8 0A1.5 1.5 0 0110.5 1h3A1.5 1.5 0 0115 2.5v3A1.5 1.5 0 0113.5 7h-3A1.5 1.5 0 019 5.5v-3zm-8 8A1.5 1.5 0 012.5 9h3A1.5 1.5 0 017 10.5v3A1.5 1.5 0 015.5 15h-3A1.5 1.5 0 011 13.5v-3zm8 0A1.5 1.5 0 0110.5 9h3A1.5 1.5 0 0115 10.5v3A1.5 1.5 0 0113.5 15h-3A1.5 1.5 0 019 13.5v-3z"/></svg>
        Vista general
      </button>
      <button class="btn btn-green">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor"><path d="M7.75 2a.75.75 0 01.75.75V7h4.25a.75.75 0 010 1.5H8.5v4.25a.75.75 0 01-1.5 0V8.5H2.75a.75.75 0 010-1.5H7V2.75A.75.75 0 017.75 2z"/></svg>
        Nuevo registro
      </button>
    </div>
  </div>

  <!-- STATS -->
  <div class="stats">

    <div class="stat-card green">
      <div class="stat-top">
        <div class="stat-icon green">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 8a3 3 0 100-6 3 3 0 000 6zm4 1H4a4 4 0 00-4 4v.5a.5.5 0 00.5.5h15a.5.5 0 00.5-.5V13a4 4 0 00-4-4z"/></svg>
        </div>
        <div class="stat-trend up">↑ +8 <span style="font-weight:400;color:var(--muted)">este mes</span></div>
      </div>
      <div class="stat-value">320</div>
      <div class="stat-label">Alumnos activos</div>
      <div class="stat-sub">312 regulares · 8 condicionales</div>
    </div>

    <div class="stat-card blue">
      <div class="stat-top">
        <div class="stat-icon blue">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M11 5a3 3 0 11-6 0 3 3 0 016 0zM8 7a2 2 0 100-4 2 2 0 000 4zm.256 7a4.474 4.474 0 01-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025a4.548 4.548 0 01.515-.877A6.578 6.578 0 008 9c-5 0-6 3-6 4s1 1 1 1h5.256z"/></svg>
        </div>
        <div class="stat-trend neutral">= estable</div>
      </div>
      <div class="stat-value">24</div>
      <div class="stat-label">Docentes</div>
      <div class="stat-sub">23 activos · 1 con licencia</div>
    </div>

    <div class="stat-card orange">
      <div class="stat-top">
        <div class="stat-icon orange">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M1 2.5A1.5 1.5 0 012.5 1h3A1.5 1.5 0 017 2.5v3A1.5 1.5 0 015.5 7h-3A1.5 1.5 0 011 5.5v-3zm8 0A1.5 1.5 0 0110.5 1h3A1.5 1.5 0 0115 2.5v3A1.5 1.5 0 0113.5 7h-3A1.5 1.5 0 019 5.5v-3zm-8 8A1.5 1.5 0 012.5 9h3A1.5 1.5 0 017 10.5v3A1.5 1.5 0 015.5 15h-3A1.5 1.5 0 011 13.5v-3zm8 0A1.5 1.5 0 0110.5 9h3A1.5 1.5 0 0115 10.5v3A1.5 1.5 0 0113.5 15h-3A1.5 1.5 0 019 13.5v-3z"/></svg>
        </div>
        <div class="stat-trend up">↑ +2</div>
      </div>
      <div class="stat-value">12</div>
      <div class="stat-label">Cursos</div>
      <div class="stat-sub">10 presenciales · 2 semipres.</div>
    </div>

    <div class="stat-card purple">
      <div class="stat-top">
        <div class="stat-icon purple">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M2 2a2 2 0 012-2h8a2 2 0 012 2v13.5a.5.5 0 01-.777.416L8 13.101l-5.223 2.815A.5.5 0 012 15.5V2zm2-1a1 1 0 00-1 1v12.566l4.723-2.482a.5.5 0 01.554 0L13 14.566V2a1 1 0 00-1-1H4z"/></svg>
        </div>
        <div class="stat-trend down">↓ −1</div>
      </div>
      <div class="stat-value">18</div>
      <div class="stat-label">Materias</div>
      <div class="stat-sub">3 con notas pendientes</div>
    </div>

  </div>

  <!-- CONTENT GRID -->
  <div class="content-grid">

    <!-- ── ACTIVITY FEED ── -->
    <div class="card full">
      <div class="card-header">
        <span class="card-title">
          <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M11.134 1.535C9.722 1.832 8 2.525 8 4c0 1.475 1.722 2.168 3.134 2.465 1.045.215 1.866.515 1.866 1.035 0 .52-.821.82-1.866 1.035C9.722 8.832 8 9.525 8 11c0 1.475 1.722 2.168 3.134 2.465.672.14 1.134.365 1.134.535s-.462.395-1.134.535C9.722 14.832 8 15.525 8 17h1c0-.52.821-.82 1.866-1.035 1.412-.297 3.134-.99 3.134-2.465 0-1.475-1.722-2.168-3.134-2.465C9.822 10.82 9 10.52 9 11c0-.52.822-.82 1.866-1.035C12.278 9.668 14 8.975 14 7.5c0-1.475-1.722-2.168-3.134-2.465C9.822 4.82 9 4.52 9 4c0-.52.822-.82 1.866-1.035L11.134 1.535z"/></svg>
          Actividad reciente
        </span>
        <div class="card-header-actions">
          <div class="tab-bar" style="border:none;padding:0;margin:0">
            <div class="tab active">Todo <span class="tab-count">12</span></div>
            <div class="tab">Sistema</div>
            <div class="tab">Docentes</div>
            <div class="tab">Alumnos</div>
          </div>
        </div>
      </div>

      <div class="card-body activity-list">

        <div class="feed-divider">
          <div class="feed-divider-line"></div>
          <div class="feed-divider-date">hoy — 30 mar 2026</div>
          <div class="feed-divider-line"></div>
        </div>

        <div class="activity-item">
          <div class="activity-dot ad-blue">JP</div>
          <div class="activity-content">
            <div class="activity-main"><strong>Juan Pérez</strong> cargó calificaciones del parcial en <span class="hl">Matemática II · 3° B</span></div>
            <div class="activity-meta">hace 14 min · docente</div>
          </div>
          <div class="activity-right"><span class="tag tag-green">notas</span></div>
        </div>

        <div class="activity-item">
          <div class="activity-dot ad-green">SA</div>
          <div class="activity-content">
            <div class="activity-main"><strong>Sistema</strong> registró nuevo alumno — <span class="hl">Valentina Romero</span> asignada a 1° C</div>
            <div class="activity-meta">hace 42 min · sistema</div>
          </div>
          <div class="activity-right"><span class="tag tag-blue">nuevo</span></div>
        </div>

        <div class="activity-item">
          <div class="activity-dot ad-purple">MA</div>
          <div class="activity-content">
            <div class="activity-main"><strong>M. Aguirre</strong> creó el curso <span class="hl">Programación II</span> — 18 alumnos inscriptos</div>
            <div class="activity-meta">hace 1h 20min · directivo</div>
          </div>
          <div class="activity-right"><span class="tag tag-muted">curso</span></div>
        </div>

        <div class="feed-divider">
          <div class="feed-divider-line"></div>
          <div class="feed-divider-date">ayer — 29 mar</div>
          <div class="feed-divider-line"></div>
        </div>

        <div class="activity-item">
          <div class="activity-dot ad-orange">DI</div>
          <div class="activity-content">
            <div class="activity-main"><strong>Dirección</strong> actualizó el calendario académico — semana del 14/04 marcada como evaluaciones</div>
            <div class="activity-meta">ayer 16:30 · admin</div>
          </div>
          <div class="activity-right"><span class="tag tag-orange">calendar</span></div>
        </div>

        <div class="activity-item">
          <div class="activity-dot ad-red">SI</div>
          <div class="activity-content">
            <div class="activity-main"><strong>Sistema</strong> detectó 3 alumnos con <span class="hl">calificaciones pendientes</span> en Física III</div>
            <div class="activity-meta">ayer 09:00 · alerta</div>
          </div>
          <div class="activity-right"><span class="tag tag-red">alerta</span></div>
        </div>

      </div>
    </div>

    <!-- ── CURSOS TABLE ── -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">
          <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M1 2.5A1.5 1.5 0 012.5 1h3A1.5 1.5 0 017 2.5v3A1.5 1.5 0 015.5 7h-3A1.5 1.5 0 011 5.5v-3zm8 0A1.5 1.5 0 0110.5 1h3A1.5 1.5 0 0115 2.5v3A1.5 1.5 0 0113.5 7h-3A1.5 1.5 0 019 5.5v-3zm-8 8A1.5 1.5 0 012.5 9h3A1.5 1.5 0 017 10.5v3A1.5 1.5 0 015.5 15h-3A1.5 1.5 0 011 13.5v-3zm8 0A1.5 1.5 0 0110.5 9h3A1.5 1.5 0 0115 10.5v3A1.5 1.5 0 0113.5 15h-3A1.5 1.5 0 019 13.5v-3z"/></svg>
          Cursos activos
        </span>
        <button class="btn btn-ghost" style="padding:4px 10px;font-size:12px">Ver todos</button>
      </div>
      <table class="mini-table">
        <thead>
          <tr>
            <th>Curso</th>
            <th>Docente</th>
            <th>Alumnos</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="font-weight:500">3° B — Matemática II</td>
            <td style="color:var(--muted)">J. Pérez</td>
            <td><span style="font-family:'JetBrains Mono',monospace">28</span></td>
            <td><span class="status-dot s-active">Activo</span></td>
          </tr>
          <tr>
            <td style="font-weight:500">1° C — Lengua</td>
            <td style="color:var(--muted)">M. González</td>
            <td><span style="font-family:'JetBrains Mono',monospace">31</span></td>
            <td><span class="status-dot s-active">Activo</span></td>
          </tr>
          <tr>
            <td style="font-weight:500">2° A — Física III</td>
            <td style="color:var(--muted)">— sin asignar</td>
            <td><span style="font-family:'JetBrains Mono',monospace">26</span></td>
            <td><span class="status-dot s-warn">Sin docente</span></td>
          </tr>
          <tr>
            <td style="font-weight:500">4° A — Programación II</td>
            <td style="color:var(--muted)">C. Ramos</td>
            <td><span style="font-family:'JetBrains Mono',monospace">18</span></td>
            <td><span class="status-dot s-active">Activo</span></td>
          </tr>
          <tr>
            <td style="font-weight:500">5° B — Historia</td>
            <td style="color:var(--muted)">L. Díaz</td>
            <td><span style="font-family:'JetBrains Mono',monospace">22</span></td>
            <td><span class="status-dot s-inactive">Inactivo</span></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ── NOTAS PENDIENTES ── -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">
          <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" style="color:var(--orange)"><path d="M8.982 1.566a1.13 1.13 0 00-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 01-1.1 0L7.1 5.995A.905.905 0 018 5zm.002 6a1 1 0 110 2 1 1 0 010-2z"/></svg>
          Pendientes y alertas
        </span>
        <span class="tag tag-orange">3 ítems</span>
      </div>
      <div class="card-body">

        <div class="activity-item">
          <div class="activity-dot ad-red" style="width:24px;height:24px;font-size:10px">!</div>
          <div class="activity-content">
            <div class="activity-main" style="font-size:13px"><strong>Física III · 2° A</strong> — 3 alumnos sin calificación</div>
            <div class="activity-meta">Sin docente asignado · urgente</div>
          </div>
          <button class="btn btn-ghost" style="padding:3px 8px;font-size:11px">Resolver</button>
        </div>

        <div class="activity-item">
          <div class="activity-dot ad-orange" style="width:24px;height:24px;font-size:10px">!</div>
          <div class="activity-content">
            <div class="activity-main" style="font-size:13px"><strong>Curso 2° B</strong> — cupo superado (35/30)</div>
            <div class="activity-meta">Requiere aprobación de dirección</div>
          </div>
          <button class="btn btn-ghost" style="padding:3px 8px;font-size:11px">Revisar</button>
        </div>

        <div class="activity-item">
          <div class="activity-dot ad-blue" style="width:24px;height:24px;font-size:10px">i</div>
          <div class="activity-content">
            <div class="activity-main" style="font-size:13px"><strong>Sistema</strong> — Backup programado para hoy 23:00</div>
            <div class="activity-meta">Mantenimiento automático</div>
          </div>
          <span class="tag tag-blue">auto</span>
        </div>

      </div>
    </div>

  </div>

</main>

<!-- ─────────── RIGHT PANEL ─────────── -->
<aside class="right-panel">

  <!-- Quick actions -->
  <div class="panel-block">
    <div class="panel-block-header">
      Acciones rápidas
    </div>
    <div class="quick-grid">
      <button class="quick-btn">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M8 8a3 3 0 100-6 3 3 0 000 6zm4 1H4a4 4 0 00-4 4v.5a.5.5 0 00.5.5h15a.5.5 0 00.5-.5V13a4 4 0 00-4-4z"/></svg>
        Nuevo alumno
      </button>
      <button class="quick-btn">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M11 5a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M8 7a2 2 0 100-4 2 2 0 000 4zm0 0v1.146a12.08 12.08 0 00-2.5 2.688 2 2 0 01-.566.438A2 2 0 014 12H3a3 3 0 006 0 2 2 0 00-1.936-1.993A10.955 10.955 0 018 8z"/></svg>
        Agregar docente
      </button>
      <button class="quick-btn">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M1 2.5A1.5 1.5 0 012.5 1h3A1.5 1.5 0 017 2.5v3A1.5 1.5 0 015.5 7h-3A1.5 1.5 0 011 5.5v-3zm8 0A1.5 1.5 0 0110.5 1h3A1.5 1.5 0 0115 2.5v3A1.5 1.5 0 0113.5 7h-3A1.5 1.5 0 019 5.5v-3zm-8 8A1.5 1.5 0 012.5 9h3A1.5 1.5 0 017 10.5v3A1.5 1.5 0 015.5 15h-3A1.5 1.5 0 011 13.5v-3zm8 0A1.5 1.5 0 0110.5 9h3A1.5 1.5 0 0115 10.5v3A1.5 1.5 0 0113.5 15h-3A1.5 1.5 0 019 13.5v-3z"/></svg>
        Crear curso
      </button>
      <button class="quick-btn">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"><path d="M9 1H7v5.586L4.707 4.293 3.293 5.707 7 9.414V15h2V9.414l3.707-3.707-1.414-1.414L9 6.586V1z"/></svg>
        Exportar reporte
      </button>
    </div>
  </div>

  <!-- Eventos -->
  <div class="panel-block">
    <div class="panel-block-header">
      <span>Próximos eventos</span>
      <span class="see-all">Ver calendario</span>
    </div>

    <div class="event-item">
      <div class="event-date-box">
        <div class="event-day" style="color:var(--orange)">12</div>
        <div class="event-mon">abr</div>
      </div>
      <div class="event-info">
        <div class="event-name">Examen Matemática II</div>
        <div class="event-room">Aula 14 · 3° B</div>
      </div>
      <div class="event-badge" style="background:var(--orange)"></div>
    </div>

    <div class="event-item">
      <div class="event-date-box">
        <div class="event-day" style="color:var(--blue)">15</div>
        <div class="event-mon">abr</div>
      </div>
      <div class="event-info">
        <div class="event-name">Reunión docente</div>
        <div class="event-room">Sala de profesores · 18:00</div>
      </div>
      <div class="event-badge" style="background:var(--blue)"></div>
    </div>

    <div class="event-item">
      <div class="event-date-box">
        <div class="event-day" style="color:var(--accent-hi)">20</div>
        <div class="event-mon">abr</div>
      </div>
      <div class="event-info">
        <div class="event-name">Inicio inscripciones</div>
        <div class="event-room">Ciclo lectivo 2026</div>
      </div>
      <div class="event-badge" style="background:var(--accent-hi)"></div>
    </div>

    <div class="event-item">
      <div class="event-date-box">
        <div class="event-day" style="color:var(--purple)">28</div>
        <div class="event-mon">abr</div>
      </div>
      <div class="event-info">
        <div class="event-name">Cierre 1er trimestre</div>
        <div class="event-room">Fecha límite notas</div>
      </div>
      <div class="event-badge" style="background:var(--purple)"></div>
    </div>

  </div>

  <!-- Actividad heatmap -->
  <div class="panel-block">
    <div class="panel-block-header">Actividad del sistema</div>
    <div class="heatmap-wrap">
      <div class="heatmap-label">últimas 10 semanas</div>
      <div class="heatmap-grid" id="heatmap"></div>
    </div>
  </div>

  <!-- Notificaciones -->
  <div class="panel-block">
    <div class="panel-block-header">
      <span>Notificaciones</span>
      <span class="see-all">Marcar leídas</span>
    </div>

    <div class="notif-item">
      <div class="notif-icon ni-red">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M8.982 1.566a1.13 1.13 0 00-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 01-1.1 0L7.1 5.995A.905.905 0 018 5zm.002 6a1 1 0 110 2 1 1 0 010-2z"/></svg>
      </div>
      <div>
        <div class="notif-text"><b>3 alumnos</b> sin calificaciones en Física III</div>
        <div class="notif-time">hace 2h</div>
      </div>
    </div>

    <div class="notif-item">
      <div class="notif-icon ni-orange">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M11 5a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2 13c0-3 2.5-5 6-5s6 2 6 5H2z"/></svg>
      </div>
      <div>
        <div class="notif-text"><b>1 curso</b> sin docente asignado</div>
        <div class="notif-time">hace 5h</div>
      </div>
    </div>

    <div class="notif-item">
      <div class="notif-icon ni-green">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M13.854 3.646a.5.5 0 010 .708l-7 7a.5.5 0 01-.708 0l-3.5-3.5a.5.5 0 11.708-.708L6.5 10.293l6.646-6.647a.5.5 0 01.708 0z"/></svg>
      </div>
      <div>
        <div class="notif-text">Sistema actualizado a <b>v2.1.0</b></div>
        <div class="notif-time">ayer</div>
      </div>
    </div>

    <div class="notif-item">
      <div class="notif-icon ni-blue">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M0 4a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H2a2 2 0 01-2-2V4zm2-1a1 1 0 00-1 1v.217l7 4.2 7-4.2V4a1 1 0 00-1-1H2zm13 2.383l-4.708 2.825L15 11.105V5.383zm-.034 6.876l-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 002 13h12a1 1 0 00.966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z"/></svg>
      </div>
      <div>
        <div class="notif-text">Recordatorio: reunión <b>15 abr</b> a las 18:00</div>
        <div class="notif-time">hace 1 día</div>
      </div>
    </div>

  </div>

</aside>

</div><!-- /shell -->

<script>
// ── HEATMAP GENERATOR ──────────────────────────────────────
(function(){
  const grid = document.getElementById('heatmap');
  const levels = [0,0,0,1,1,2,2,3,3,4];
  // 10 cols (weeks) × 7 rows (days) = 70 cells, but render by week columns
  for(let w=0;w<70;w++){
    const cell = document.createElement('div');
    const rand = Math.random();
    let lvl = 0;
    if(rand > .75) lvl = Math.floor(Math.random()*4)+1;
    cell.className = `hm-cell hm-${lvl}`;
    grid.appendChild(cell);
  }
})();

// ── TAB SWITCHER ───────────────────────────────────────────
document.querySelectorAll('.tab').forEach(tab=>{
  tab.addEventListener('click',function(){
    this.closest('.tab-bar').querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
    this.classList.add('active');
  });
});

// ── NAV ITEMS ──────────────────────────────────────────────
document.querySelectorAll('.nav-item').forEach(item=>{
  item.addEventListener('click',function(){
    document.querySelectorAll('.nav-item').forEach(i=>i.classList.remove('active'));
    this.classList.add('active');
  });
});
</script>

</body>
</html>
