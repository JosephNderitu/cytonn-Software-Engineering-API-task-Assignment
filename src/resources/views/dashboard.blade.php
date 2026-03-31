<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>TaskFlow — Task Manager</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0a0a0f;--bg2:#0f0f17;--bg3:#16161f;--bg4:#1e1e2a;
  --border:#ffffff0f;--border2:#ffffff18;
  --text:#e8e8f0;--muted:#6b6b80;--muted2:#9393a8;
  --accent:#7c6af7;--accent2:#a78bfa;--accent-glow:#7c6af720;
  --green:#22d3a0;--green-bg:#22d3a012;
  --amber:#f59e0b;--amber-bg:#f59e0b12;
  --red:#f43f5e;--red-bg:#f43f5e12;
  --blue:#38bdf8;--blue-bg:#38bdf812;
  --radius:10px;--radius-lg:14px;
  --mono:'Space Mono',monospace;--sans:'DM Sans',sans-serif;
}
html,body{height:100%;background:var(--bg);color:var(--text);font-family:var(--sans);font-size:14px;overflow:hidden}
.shell{display:grid;grid-template-columns:220px 1fr;height:100vh}
.sidebar{background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column}
.main{display:flex;flex-direction:column;overflow:hidden}
.logo{padding:24px 20px 20px;border-bottom:1px solid var(--border)}
.logo-mark{font-family:var(--mono);font-size:17px;font-weight:700;color:var(--text);letter-spacing:-0.5px}
.logo-mark span{color:var(--accent2)}
.logo-sub{font-size:11px;color:var(--muted);margin-top:3px;letter-spacing:0.5px;text-transform:uppercase}
.nav{padding:16px 12px;flex:1}
.nav-label{font-size:10px;color:var(--muted);letter-spacing:1px;text-transform:uppercase;padding:0 8px;margin-bottom:8px;margin-top:16px}
.nav-label:first-child{margin-top:0}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;cursor:pointer;color:var(--muted2);font-size:13px;transition:all .15s;border:1px solid transparent;user-select:none}
.nav-item:hover{background:var(--bg3);color:var(--text)}
.nav-item.active{background:var(--accent-glow);color:var(--accent2);border-color:#7c6af722}
.nav-icon{width:16px;height:16px;opacity:.7;flex-shrink:0}
.nav-item.active .nav-icon{opacity:1}
.sidebar-footer{padding:16px;border-top:1px solid var(--border)}
.status-dot{width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 6px var(--green);flex-shrink:0;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
.topbar{padding:16px 28px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--bg2);flex-shrink:0}
.page-title{font-size:16px;font-weight:600}
.page-sub{font-size:12px;color:var(--muted);margin-top:2px}
.topbar-actions{display:flex;gap:10px;align-items:center}
.btn{display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;border:none;font-family:var(--sans);transition:all .15s;position:relative}
.btn-primary{background:var(--accent);color:#fff}
.btn-primary:hover:not(:disabled){background:var(--accent2);transform:translateY(-1px)}
.btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none}
.btn-ghost{background:transparent;color:var(--muted2);border:1px solid var(--border2)}
.btn-ghost:hover:not(:disabled){background:var(--bg4);color:var(--text)}
.btn-ghost:disabled{opacity:.4;cursor:not-allowed}
.btn-sm{padding:5px 11px;font-size:12px}
.btn-pgadmin{background:#336791;color:#fff;font-size:12px;padding:6px 13px;border-radius:7px;border:none;cursor:pointer;font-family:var(--sans);display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:background .15s}
.btn-pgadmin:hover{background:#2d5a80}
.content{flex:1;overflow-y:auto;padding:28px;min-height:0}
.content::-webkit-scrollbar{width:4px}
.content::-webkit-scrollbar-thumb{background:var(--border2);border-radius:4px}
.page{display:none}
.page.active{display:block}
/* Stats */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.stat-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:18px 20px;position:relative;overflow:hidden}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px}
.stat-card.s-purple::before{background:var(--accent)}
.stat-card.s-green::before{background:var(--green)}
.stat-card.s-amber::before{background:var(--amber)}
.stat-card.s-red::before{background:var(--red)}
.stat-label{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px}
.stat-value{font-family:var(--mono);font-size:28px;font-weight:700;line-height:1}
.stat-sub{font-size:11px;color:var(--muted);margin-top:6px}
/* Filters */
.filters{display:flex;gap:10px;margin-bottom:20px;align-items:center;flex-wrap:wrap}
.filter-btn{padding:6px 14px;border-radius:20px;border:1px solid var(--border2);background:transparent;color:var(--muted2);font-size:12px;cursor:pointer;font-family:var(--sans);transition:all .15s}
.filter-btn:hover{border-color:#7c6af744;color:var(--text)}
.filter-btn.active{background:var(--accent-glow);border-color:#7c6af744;color:var(--accent2)}
.search-wrap{margin-left:auto;position:relative}
.search-input{background:var(--bg3);border:1px solid var(--border2);border-radius:8px;padding:7px 12px 7px 34px;color:var(--text);font-size:13px;font-family:var(--sans);width:200px;outline:none;transition:border .15s}
.search-input:focus{border-color:#7c6af744}
.search-input::placeholder{color:var(--muted)}
.search-icon{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);pointer-events:none}
/* Table */
.table-wrap{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden}
.table-head{display:grid;grid-template-columns:2fr 100px 110px 120px 110px;padding:11px 20px;border-bottom:1px solid var(--border);background:var(--bg3)}
.th{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;font-weight:500}
.task-row{display:grid;grid-template-columns:2fr 100px 110px 120px 110px;padding:14px 20px;border-bottom:1px solid var(--border);align-items:center;transition:background .15s}
.task-row:last-child{border-bottom:none}
.task-row:hover{background:var(--bg3)}
.task-title{font-size:13px;font-weight:500;display:flex;align-items:center;gap:8px}
.task-id{font-family:var(--mono);font-size:10px;color:var(--muted);background:var(--bg4);padding:2px 6px;border-radius:4px;flex-shrink:0}
.task-date{font-size:12px;color:var(--muted2);font-family:var(--mono)}
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:500}
.badge-high{background:var(--red-bg);color:var(--red);border:1px solid #f43f5e22}
.badge-medium{background:var(--amber-bg);color:var(--amber);border:1px solid #f59e0b22}
.badge-low{background:var(--blue-bg);color:var(--blue);border:1px solid #38bdf822}
.badge-pending{background:var(--bg4);color:var(--muted2);border:1px solid var(--border2)}
.badge-in_progress{background:var(--amber-bg);color:var(--amber);border:1px solid #f59e0b22}
.badge-done{background:var(--green-bg);color:var(--green);border:1px solid #22d3a022}
.row-actions{display:flex;gap:6px}
.icon-btn{width:28px;height:28px;border-radius:6px;border:1px solid var(--border2);background:transparent;color:var(--muted2);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s}
.icon-btn:hover{background:var(--bg4);color:var(--text)}
.icon-btn.del:hover{background:var(--red-bg);border-color:#f43f5e33;color:var(--red)}
.empty-state{padding:60px 20px;text-align:center;color:var(--muted)}
/* Skeleton loader */
.skeleton{background:linear-gradient(90deg,var(--bg3) 25%,var(--bg4) 50%,var(--bg3) 75%);background-size:200% 100%;animation:shimmer 1.4s infinite;border-radius:4px;height:14px}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
.skeleton-row{display:grid;grid-template-columns:2fr 100px 110px 120px 110px;padding:14px 20px;gap:16px;border-bottom:1px solid var(--border)}
/* Modal */
.overlay{position:fixed;inset:0;background:#00000088;backdrop-filter:blur(4px);z-index:100;display:none;align-items:center;justify-content:center}
.overlay.open{display:flex}
.modal{background:var(--bg2);border:1px solid var(--border2);border-radius:var(--radius-lg);padding:28px;width:460px;max-width:95vw;animation:slideUp .2s ease}
@keyframes slideUp{from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1}}
.modal-title{font-size:16px;font-weight:600;margin-bottom:6px}
.modal-sub{font-size:13px;color:var(--muted);margin-bottom:22px}
.form-row{margin-bottom:16px}
.form-label{font-size:12px;color:var(--muted2);margin-bottom:6px;display:block;font-weight:500}
.form-input,.form-select{width:100%;background:var(--bg3);border:1px solid var(--border2);border-radius:8px;padding:10px 13px;color:var(--text);font-size:13px;font-family:var(--sans);outline:none;transition:border .15s}
.form-input:focus,.form-select:focus{border-color:#7c6af766}
.form-select option{background:var(--bg3)}
.form-error{font-size:11px;color:var(--red);margin-top:5px;display:none}
.modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:22px}
/* Spinner inside button */
.spinner{width:13px;height:13px;border:2px solid #ffffff44;border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;display:none}
.btn.loading .spinner{display:inline-block}
.btn.loading .btn-label{display:none}
@keyframes spin{to{transform:rotate(360deg)}}
/* Toast */
.toast-wrap{position:fixed;top:20px;right:20px;z-index:200;display:flex;flex-direction:column;gap:8px;pointer-events:none}
.toast{background:var(--bg3);border:1px solid var(--border2);border-radius:10px;padding:12px 18px;font-size:13px;color:var(--text);display:flex;align-items:center;gap:10px;animation:toastIn .25s ease;pointer-events:all;min-width:260px;transition:opacity .3s}
.toast.success{border-left:3px solid var(--green)}
.toast.error{border-left:3px solid var(--red)}
.toast.info{border-left:3px solid var(--accent)}
@keyframes toastIn{from{transform:translateX(20px);opacity:0}to{transform:translateX(0);opacity:1}}
/* DB Viewer */
.db-layout{display:grid;grid-template-columns:200px 1fr;gap:16px;height:calc(100vh - 148px)}
.db-side{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;display:flex;flex-direction:column}
.db-panel-head{padding:12px 16px;border-bottom:1px solid var(--border);font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.db-table-list{overflow-y:auto;flex:1}
.db-table-item{padding:9px 16px;cursor:pointer;font-size:12px;color:var(--muted2);border-bottom:1px solid var(--border);transition:all .15s;display:flex;align-items:center;gap:8px}
.db-table-item:hover{background:var(--bg3);color:var(--text)}
.db-table-item.active{background:var(--accent-glow);color:var(--accent2)}
.db-table-item:last-child{border-bottom:none}
.db-main{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;display:flex;flex-direction:column}
.db-main-head{padding:13px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.db-table-name{font-family:var(--mono);font-size:13px;color:var(--accent2)}
.db-meta{font-size:11px;color:var(--muted);margin-top:2px}
.db-scroll{overflow:auto;flex:1}
.db-scroll::-webkit-scrollbar{width:4px;height:4px}
.db-scroll::-webkit-scrollbar-thumb{background:var(--border2);border-radius:4px}
.db-tbl{width:100%;border-collapse:collapse;font-size:12px}
.db-tbl th{padding:9px 14px;text-align:left;font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;border-bottom:1px solid var(--border);background:var(--bg3);font-weight:500;white-space:nowrap;position:sticky;top:0;z-index:1}
.db-tbl td{padding:9px 14px;border-bottom:1px solid var(--border);color:var(--muted2);font-family:var(--mono);font-size:11px;white-space:nowrap;max-width:280px;overflow:hidden;text-overflow:ellipsis}
.db-tbl tr:last-child td{border-bottom:none}
.db-tbl tr:hover td{background:var(--bg3);color:var(--text)}
.db-null{color:var(--muted);font-style:italic;font-family:var(--sans);font-size:11px}
.pgadmin-banner{background:var(--bg3);border:1px solid var(--border2);border-radius:10px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.pgadmin-info{font-size:13px;color:var(--muted2)}
.pgadmin-info strong{color:var(--text);font-weight:500}
/* Report */
.report-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
.report-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:22px}
.report-card-title{font-size:11px;font-weight:600;color:var(--muted);margin-bottom:16px;text-transform:uppercase;letter-spacing:.8px}
.date-picker-wrap{display:flex;gap:10px;align-items:center;margin-bottom:24px}
.date-input{background:var(--bg3);border:1px solid var(--border2);border-radius:8px;padding:8px 13px;color:var(--text);font-size:13px;font-family:var(--mono);outline:none}
.date-input:focus{border-color:#7c6af766}
.summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.summary-cell{background:var(--bg3);border-radius:8px;padding:14px}
.summary-priority{font-size:10px;color:var(--muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;font-weight:600}
.summary-row{display:flex;justify-content:space-between;font-size:11px;margin-bottom:5px}
.summary-key{color:var(--muted)}
.summary-val{font-family:var(--mono);color:var(--text)}
.chart-wrap{position:relative;height:240px}
.no-data{display:flex;align-items:center;justify-content:center;height:160px;color:var(--muted);font-size:13px}
</style>
</head>
<body>
<div class="shell">
  <aside class="sidebar">
    <div class="logo">
      <div class="logo-mark">Task<span>Flow</span></div>
      <div class="logo-sub">Management System</div>
    </div>
    <nav class="nav">
      <div class="nav-label">Workspace</div>
      <div class="nav-item active" data-page="tasks">
        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Tasks
      </div>
      <div class="nav-item" data-page="report">
        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Daily Report
      </div>
      <div class="nav-label">Database</div>
      <div class="nav-item" data-page="db">
        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
        DB Viewer
      </div>
    </nav>
    <div class="sidebar-footer">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
        <div class="status-dot"></div>
        <span style="font-size:11px;color:var(--muted2)">API · localhost:8080</span>
      </div>
      <a href="http://localhost:5050" target="_blank" class="btn-pgadmin" style="width:100%;justify-content:center">
        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        Open pgAdmin
      </a>
    </div>
  </aside>

  <div class="main">
    <div class="topbar">
      <div>
        <div class="page-title" id="topbar-title">Tasks</div>
        <div class="page-sub" id="topbar-sub">Manage and track all your tasks</div>
      </div>
      <div class="topbar-actions">
        <button class="btn btn-ghost btn-sm" id="refresh-btn" onclick="handleRefresh()">
          <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          <span class="btn-label">Refresh</span>
          <span class="spinner"></span>
        </button>
        <button class="btn btn-primary btn-sm" id="create-btn" onclick="openCreateModal()">
          <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          New Task
        </button>
      </div>
    </div>

    <!-- Tasks Page -->
    <div class="content page active" id="page-tasks">
      <div class="stats-grid">
        <div class="stat-card s-purple"><div class="stat-label">Total</div><div class="stat-value" id="stat-total">—</div><div class="stat-sub">All tasks</div></div>
        <div class="stat-card s-amber"><div class="stat-label">In Progress</div><div class="stat-value" id="stat-progress">—</div><div class="stat-sub">Active</div></div>
        <div class="stat-card s-red"><div class="stat-label">Pending</div><div class="stat-value" id="stat-pending">—</div><div class="stat-sub">Not started</div></div>
        <div class="stat-card s-green"><div class="stat-label">Done</div><div class="stat-value" id="stat-done">—</div><div class="stat-sub">Completed</div></div>
      </div>
      <div class="filters">
        <button class="filter-btn active" data-filter="all">All</button>
        <button class="filter-btn" data-filter="pending">Pending</button>
        <button class="filter-btn" data-filter="in_progress">In Progress</button>
        <button class="filter-btn" data-filter="done">Done</button>
        <div class="search-wrap">
          <svg class="search-icon" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
          <input class="search-input" type="text" placeholder="Search tasks…" id="search-input" oninput="renderTable()"/>
        </div>
      </div>
      <div class="table-wrap">
        <div class="table-head">
          <div class="th">Task</div><div class="th">Due Date</div><div class="th">Priority</div><div class="th">Status</div><div class="th">Actions</div>
        </div>
        <div id="task-tbody"></div>
      </div>
    </div>

    <!-- Report Page -->
    <div class="content page" id="page-report">
      <div class="date-picker-wrap">
        <span style="font-size:13px;color:var(--muted2)">Report for</span>
        <input type="date" class="date-input" id="report-date"/>
        <button class="btn btn-primary btn-sm" id="report-btn" onclick="loadReport()">
          <span class="btn-label">Generate</span>
          <span class="spinner"></span>
        </button>
      </div>
      <div id="report-content"><div class="no-data">Select a date and click Generate</div></div>
    </div>

    <!-- DB Viewer Page -->
    <div class="content page" id="page-db">
      <div class="pgadmin-banner">
        <div class="pgadmin-info">
          <strong>Full database access via pgAdmin</strong> — browse tables, run SQL queries, inspect indexes and constraints
        </div>
        <a href="http://localhost:5050" target="_blank" class="btn-pgadmin">
          <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          Open pgAdmin → localhost:5050
        </a>
      </div>
      <div class="db-layout">
        <div class="db-side">
          <div class="db-panel-head">
            <span>Tables</span>
            <span id="table-count" style="font-family:var(--mono);color:var(--accent2)"></span>
          </div>
          <div class="db-table-list" id="db-table-list"><div class="no-data" style="height:80px">Loading…</div></div>
        </div>
        <div class="db-main">
          <div class="db-main-head">
            <div>
              <div class="db-table-name" id="db-active-name">Select a table</div>
              <div class="db-meta" id="db-active-meta"></div>
            </div>
            <button class="btn btn-ghost btn-sm" onclick="refreshDbTable()">Refresh</button>
          </div>
          <div class="db-scroll"><div id="db-body"><div class="no-data">Click a table on the left to view its data</div></div></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Create Modal -->
<div class="overlay" id="task-modal">
  <div class="modal">
    <div class="modal-title">New Task</div>
    <div class="modal-sub">Fill in the details below to create a task</div>
    <div class="form-row">
      <label class="form-label">Title</label>
      <input class="form-input" id="f-title" type="text" placeholder="e.g. Fix login bug" autocomplete="off"/>
      <div class="form-error" id="err-title"></div>
    </div>
    <div class="form-row">
      <label class="form-label">Due Date</label>
      <input class="form-input" id="f-date" type="date"/>
      <div class="form-error" id="err-date"></div>
    </div>
    <div class="form-row">
      <label class="form-label">Priority</label>
      <select class="form-select" id="f-priority">
        <option value="high">High</option>
        <option value="medium" selected>Medium</option>
        <option value="low">Low</option>
      </select>
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" id="submit-btn" onclick="submitTask()">
        <span class="btn-label">Create Task</span>
        <span class="spinner"></span>
      </button>
    </div>
  </div>
</div>

<!-- Status Modal -->
<div class="overlay" id="status-modal">
  <div class="modal">
    <div class="modal-title">Update Status</div>
    <div class="modal-sub">Progress this task to the next stage</div>
    <div class="form-row">
      <label class="form-label">Current Status</label>
      <input class="form-input" id="s-current" disabled/>
    </div>
    <div class="form-row">
      <label class="form-label">Next Status</label>
      <select class="form-select" id="s-new"></select>
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeStatusModal()">Cancel</button>
      <button class="btn btn-primary" id="status-submit-btn" onclick="submitStatus()">
        <span class="btn-label">Update</span>
        <span class="spinner"></span>
      </button>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toast-wrap"></div>

<script>
const API = '/api';
let allTasks = [], currentFilter = 'all', statusTaskId = null, activeDbTable = null;
let barChart = null, submitting = false;

// ── Navigation ────────────────────────────────────────────────────────────────
document.querySelectorAll('.nav-item').forEach(item => {
  item.addEventListener('click', () => {
    const page = item.dataset.page;
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    item.classList.add('active');
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById('page-' + page).classList.add('active');
    const map = {
      tasks: ['Tasks', 'Manage and track all your tasks'],
      report: ['Daily Report', 'Task counts by priority and status'],
      db: ['Database Viewer', 'Live PostgreSQL data — all tables']
    };
    const [t, s] = map[page] || ['', ''];
    document.getElementById('topbar-title').textContent = t;
    document.getElementById('topbar-sub').textContent = s;
    document.getElementById('create-btn').style.display = page === 'tasks' ? 'inline-flex' : 'none';
    if (page === 'db') initDbViewer();
  });
});

document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentFilter = btn.dataset.filter;
    renderTable();
  });
});

// ── API ───────────────────────────────────────────────────────────────────────
async function api(method, path, body) {
  const opts = { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const r = await fetch(API + path, opts);
  const data = await r.json();
  return { ok: r.ok, status: r.status, data };
}

// ── Toast ─────────────────────────────────────────────────────────────────────
function toast(msg, type = 'info') {
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<span>${{ success: '✓', error: '✕', info: 'ℹ' }[type]}</span> ${msg}`;
  document.getElementById('toast-wrap').appendChild(el);
  setTimeout(() => { el.style.opacity = '0'; }, 2800);
  setTimeout(() => el.remove(), 3100);
}

// ── Button loading state ──────────────────────────────────────────────────────
function setBtnLoading(id, loading) {
  const btn = document.getElementById(id);
  if (!btn) return;
  btn.disabled = loading;
  btn.classList.toggle('loading', loading);
}

// ── Tasks ─────────────────────────────────────────────────────────────────────
function showSkeletons() {
  const tbody = document.getElementById('task-tbody');
  tbody.innerHTML = Array(4).fill(`
    <div class="skeleton-row">
      <div class="skeleton" style="height:13px;width:70%"></div>
      <div class="skeleton" style="height:13px;width:80px"></div>
      <div class="skeleton" style="height:13px;width:60px;border-radius:20px"></div>
      <div class="skeleton" style="height:13px;width:70px;border-radius:20px"></div>
      <div class="skeleton" style="height:13px;width:60px"></div>
    </div>`).join('');
}

async function loadTasks() {
  showSkeletons();
  const { ok, data } = await api('GET', '/tasks');
  if (!ok) { toast('Failed to load tasks', 'error'); return; }
  allTasks = data.data || [];
  document.getElementById('stat-total').textContent = allTasks.length;
  document.getElementById('stat-progress').textContent = allTasks.filter(t => t.status === 'in_progress').length;
  document.getElementById('stat-pending').textContent = allTasks.filter(t => t.status === 'pending').length;
  document.getElementById('stat-done').textContent = allTasks.filter(t => t.status === 'done').length;
  renderTable();
}

async function handleRefresh() {
  setBtnLoading('refresh-btn', true);
  await loadTasks();
  setBtnLoading('refresh-btn', false);
  toast('Tasks refreshed', 'info');
}

function renderTable() {
  const search = document.getElementById('search-input').value.toLowerCase();
  let tasks = allTasks;
  if (currentFilter !== 'all') tasks = tasks.filter(t => t.status === currentFilter);
  if (search) tasks = tasks.filter(t => t.title.toLowerCase().includes(search));
  const tbody = document.getElementById('task-tbody');
  if (!tasks.length) {
    tbody.innerHTML = '<div class="empty-state"><div style="font-size:30px;opacity:.25;margin-bottom:10px">◎</div><div style="font-size:13px">No tasks found</div></div>';
    return;
  }
  tbody.innerHTML = tasks.map(t => `
    <div class="task-row">
      <div class="task-title"><span class="task-id">#${t.id}</span>${esc(t.title)}</div>
      <div class="task-date">${t.due_date}</div>
      <div><span class="badge badge-${t.priority}">${t.priority}</span></div>
      <div><span class="badge badge-${t.status}">${fmtS(t.status)}</span></div>
      <div class="row-actions">
        ${t.status !== 'done' ? `<button class="icon-btn" title="Advance status" onclick="openStatusModal(${t.id},'${t.status}')">
          <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>` : '<span style="width:28px;display:inline-block"></span>'}
        <button class="icon-btn del" title="Delete" onclick="deleteTask(${t.id},'${t.status}')">
          <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
      </div>
    </div>`).join('');
}

function fmtS(s) { return s === 'in_progress' ? 'In Progress' : s.charAt(0).toUpperCase() + s.slice(1); }
function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

// ── Create Modal ──────────────────────────────────────────────────────────────
function openCreateModal() {
  document.getElementById('f-title').value = '';
  document.getElementById('f-date').value = new Date().toISOString().split('T')[0];
  document.getElementById('f-priority').value = 'medium';
  ['title', 'date'].forEach(f => { const e = document.getElementById('err-' + f); e.style.display = 'none'; e.textContent = ''; });
  setBtnLoading('submit-btn', false);
  document.getElementById('task-modal').classList.add('open');
  setTimeout(() => document.getElementById('f-title').focus(), 100);
}
function closeModal() { document.getElementById('task-modal').classList.remove('open'); }

async function submitTask() {
  if (submitting) return;  // prevent double-click
  submitting = true;
  setBtnLoading('submit-btn', true);
  ['title', 'date'].forEach(f => { const e = document.getElementById('err-' + f); e.style.display = 'none'; e.textContent = ''; });

  const title = document.getElementById('f-title').value.trim();
  const due_date = document.getElementById('f-date').value;
  const priority = document.getElementById('f-priority').value;

  const { ok, data } = await api('POST', '/tasks', { title, due_date, priority });
  submitting = false;
  setBtnLoading('submit-btn', false);

  if (ok) {
    toast('Task created successfully', 'success');
    closeModal();
    loadTasks();
  } else {
    if (data.errors) {
      if (data.errors.title) { const e = document.getElementById('err-title'); e.textContent = data.errors.title[0]; e.style.display = 'block'; }
      if (data.errors.due_date) { const e = document.getElementById('err-date'); e.textContent = data.errors.due_date[0]; e.style.display = 'block'; }
    }
    toast(data.message || 'Failed to create task', 'error');
  }
}

// ── Delete ────────────────────────────────────────────────────────────────────
async function deleteTask(id, status) {
  if (status !== 'done') { toast('Only "done" tasks can be deleted', 'error'); return; }
  if (!confirm('Delete this completed task? This cannot be undone.')) return;
  const { ok, data } = await api('DELETE', `/tasks/${id}`);
  if (ok) { toast('Task deleted', 'success'); loadTasks(); }
  else toast(data.message || 'Delete failed', 'error');
}

// ── Status Modal ──────────────────────────────────────────────────────────────
function openStatusModal(id, current) {
  statusTaskId = id;
  document.getElementById('s-current').value = fmtS(current);
  const sel = document.getElementById('s-new');
  sel.innerHTML = '';
  const next = { pending: 'in_progress', in_progress: 'done' }[current];
  if (next) { const o = document.createElement('option'); o.value = next; o.textContent = fmtS(next); sel.appendChild(o); }
  setBtnLoading('status-submit-btn', false);
  document.getElementById('status-modal').classList.add('open');
}
function closeStatusModal() { document.getElementById('status-modal').classList.remove('open'); }

async function submitStatus() {
  if (submitting) return;
  submitting = true;
  setBtnLoading('status-submit-btn', true);
  const status = document.getElementById('s-new').value;
  const { ok, data } = await api('PATCH', `/tasks/${statusTaskId}/status`, { status });
  submitting = false;
  setBtnLoading('status-submit-btn', false);
  if (ok) { toast('Status updated to "' + fmtS(status) + '"', 'success'); closeStatusModal(); loadTasks(); }
  else toast(data.message || 'Update failed', 'error');
}

// Close modals on overlay click
document.querySelectorAll('.overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) { o.classList.remove('open'); submitting = false; } });
});

// ── Report ────────────────────────────────────────────────────────────────────
async function loadReport() {
  const date = document.getElementById('report-date').value;
  if (!date) { toast('Please select a date', 'error'); return; }
  setBtnLoading('report-btn', true);
  const { ok, data } = await api('GET', `/tasks/report?date=${date}`);
  setBtnLoading('report-btn', false);
  if (!ok) { toast('Failed to load report', 'error'); return; }
  const s = data.summary;
  document.getElementById('report-content').innerHTML = `
    <div class="report-grid">
      <div class="report-card"><div class="report-card-title">Priority vs Status</div><div class="chart-wrap"><canvas id="bar-chart"></canvas></div></div>
      <div class="report-card"><div class="report-card-title">Summary — ${date}</div>
        <div class="summary-grid">
          ${['high', 'medium', 'low'].map(p => `<div class="summary-cell">
            <div class="summary-priority" style="color:${p==='high'?'var(--red)':p==='medium'?'var(--amber)':'var(--blue)'}">${p}</div>
            ${['pending', 'in_progress', 'done'].map(st => `<div class="summary-row"><span class="summary-key">${fmtS(st)}</span><span class="summary-val">${s[p][st]}</span></div>`).join('')}
          </div>`).join('')}
        </div>
      </div>
    </div>`;
  if (barChart) barChart.destroy();
  barChart = new Chart(document.getElementById('bar-chart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: ['Pending', 'In Progress', 'Done'],
      datasets: [
        { label: 'High', data: [s.high.pending, s.high.in_progress, s.high.done], backgroundColor: '#f43f5e44', borderColor: '#f43f5e', borderWidth: 1.5, borderRadius: 4 },
        { label: 'Medium', data: [s.medium.pending, s.medium.in_progress, s.medium.done], backgroundColor: '#f59e0b44', borderColor: '#f59e0b', borderWidth: 1.5, borderRadius: 4 },
        { label: 'Low', data: [s.low.pending, s.low.in_progress, s.low.done], backgroundColor: '#38bdf844', borderColor: '#38bdf8', borderWidth: 1.5, borderRadius: 4 },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { color: '#9393a8', font: { size: 12 } } } },
      scales: { x: { ticks: { color: '#6b6b80' }, grid: { color: '#ffffff08' } }, y: { ticks: { color: '#6b6b80', stepSize: 1 }, grid: { color: '#ffffff08' }, beginAtZero: true } }
    }
  });
}

// ── DB Viewer ─────────────────────────────────────────────────────────────────
async function initDbViewer() {
  document.getElementById('db-table-list').innerHTML = '<div class="no-data" style="height:80px">Loading tables…</div>';
  const { ok, data } = await api('GET', '/db/tables');
  if (!ok) { document.getElementById('db-table-list').innerHTML = '<div class="no-data" style="height:80px">Failed to load</div>'; return; }
  const tables = data.tables || [];
  document.getElementById('table-count').textContent = tables.length;
  const icons = { tasks: '◈', users: '◉', migrations: '◎', cache: '◇', jobs: '◆', sessions: '◈', password_reset_tokens: '◈', failed_jobs: '◆', job_batches: '◆' };
  document.getElementById('db-table-list').innerHTML = tables.map(t => `
    <div class="db-table-item" data-table="${t}" onclick="loadDbTable('${t}')">
      <span style="font-size:10px;color:var(--accent)">${icons[t] || '◈'}</span>
      <span style="font-family:var(--mono)">${t}</span>
    </div>`).join('');
  loadDbTable('tasks');
}

async function loadDbTable(name) {
  activeDbTable = name;
  document.querySelectorAll('.db-table-item').forEach(i => i.classList.toggle('active', i.dataset.table === name));
  document.getElementById('db-active-name').textContent = name;
  document.getElementById('db-active-meta').textContent = 'Loading…';
  document.getElementById('db-body').innerHTML = '<div class="no-data">Loading…</div>';

  const { ok, data } = await api('GET', `/db/tables/${name}`);
  if (!ok) {
    document.getElementById('db-active-meta').textContent = 'Error';
    document.getElementById('db-body').innerHTML = '<div class="no-data">Failed to load table data</div>';
    return;
  }
  const cols = data.columns || [];
  const rows = data.rows || [];
  document.getElementById('db-active-meta').textContent = `${data.total} rows · ${cols.length} columns`;

  if (!rows.length) {
    document.getElementById('db-body').innerHTML = `
      <table class="db-tbl"><thead><tr>${cols.map(c => `<th title="${c.data_type}">${c.column_name}</th>`).join('')}</tr></thead>
      <tbody><tr><td colspan="${cols.length}" style="text-align:center;padding:40px;color:var(--muted);font-family:var(--sans)">No rows in this table</td></tr></tbody></table>`;
    return;
  }

  const colNames = cols.map(c => c.column_name);
  document.getElementById('db-body').innerHTML = `
    <table class="db-tbl">
      <thead><tr>${cols.map(c => `<th title="${c.data_type}">${c.column_name}<div style="font-size:9px;color:var(--muted);font-weight:400;margin-top:1px">${c.data_type}</div></th>`).join('')}</tr></thead>
      <tbody>${rows.map(r => `<tr>${colNames.map(c => `<td title="${r[c] ?? ''}">${r[c] === null ? '<span class="db-null">NULL</span>' : esc(String(r[c]))}</td>`).join('')}</tr>`).join('')}</tbody>
    </table>`;
}

function refreshDbTable() { if (activeDbTable) loadDbTable(activeDbTable); }

// ── Init ──────────────────────────────────────────────────────────────────────
document.getElementById('report-date').value = new Date().toISOString().split('T')[0];
loadTasks();
</script>
</body>
</html>