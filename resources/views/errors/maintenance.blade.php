<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#040B15">
    <title>Platform maintenance · {{ config('app.name') }}</title>
    <style>
        :root { --bg:#040b15; --ink:#edf4fc; --muted:#8a9cb5; --accent:#4fd1e0; --blue:#3b8fd4; --panel:#071a30; --border:rgba(255,255,255,.09); }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; background:radial-gradient(60rem 40rem at 120% -10%, rgba(79,209,224,.12), transparent), radial-gradient(50rem 40rem at -10% 110%, rgba(124,124,242,.12), transparent), var(--bg); color:var(--ink); font-family:Inter, system-ui, -apple-system, sans-serif; -webkit-font-smoothing:antialiased; }
        .card { max-width:34rem; width:calc(100% - 3rem); text-align:center; padding:3.5rem 3rem; border:1px solid var(--border); border-radius:20px; background:linear-gradient(180deg, rgba(255,255,255,.07), rgba(255,255,255,.025)); box-shadow:0 40px 90px -50px rgba(0,0,0,.9); }
        .mark { width:3.5rem; height:3.5rem; margin:0 auto 1.5rem; display:grid; place-items:center; border-radius:14px; background:linear-gradient(135deg, var(--blue), var(--accent)); box-shadow:0 8px 30px -6px rgba(79,209,224,.45); }
        .mark svg { width:1.8rem; height:1.8rem; }
        h1 { margin:0; font-size:1.7rem; font-weight:800; letter-spacing:-.02em; }
        p { margin:0.85rem auto 0; max-width:28rem; color:var(--muted); font-size:.95rem; line-height:1.65; }
        .pill { display:inline-flex; align-items:center; gap:.5rem; margin-top:2rem; padding:.4rem .9rem; border-radius:999px; border:1px solid var(--border); background:rgba(79,209,224,.08); color:var(--accent); font-size:.75rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; }
        .dot { width:.5rem; height:.5rem; border-radius:999px; background:var(--accent); animation:pulse 1.6s ease-in-out infinite; }
        @keyframes pulse { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:.35; transform:scale(.8); } }
    </style>
</head>
<body>
    <div class="card">
        <div class="mark">
            <svg viewBox="0 0 24 24" fill="none"><path d="M4 12.5 8 16l8-8" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h1>{{ config('app.name') }} is undergoing maintenance</h1>
        <p>The console is temporarily unavailable while we run planned maintenance. Please check back shortly — your referral data remains safe and unchanged.</p>
        <span class="pill"><span class="dot"></span>Scheduled maintenance</span>
    </div>
</body>
</html>