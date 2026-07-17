<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>@yield('title', 'SAPD Mekarsari')</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,500&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<style>
:root{
  --gd:#1a3a2a;--gm:#2d6a4f;--gf:#40916c;--gl:#74c69d;--gp:#d8f3dc;
  --cr:#f7f3eb;--cd:#e8e2d6;
  --am:#e9a84c;--amp:#fef3dc;--amd:#b07c1a;
  --rs:#e05c5c;--rsp:#fdeaea;--rsd:#c0392b;
  --bl:#2563c0;--blp:#e8f0fb;--bld:#1a4a96;
  --ink:#1c1c1c;--mu:#5a5a5a;--hi:#9a9a9a;--wh:#fff;
  --s1:0 1px 4px rgba(26,58,42,.08);
  --s2:0 4px 16px rgba(26,58,42,.12);
  --r:16px;--rs2:10px;
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:var(--cr);color:var(--ink)}
.topbar{background:var(--gd);padding:16px 24px;display:flex;align-items:center;justify-content:space-between}
.topbar-brand{font-family:'Fraunces',serif;font-size:17px;font-weight:700;color:#fff}
.topbar-back{color:rgba(255,255,255,.75);text-decoration:none;font-size:12px;font-weight:600;padding:7px 14px;border:1.5px solid rgba(255,255,255,.2);border-radius:9px;transition:all .18s}
.topbar-back:hover{background:rgba(255,255,255,.1);color:#fff}
.wrap{max-width:960px;margin:0 auto;padding:28px 20px}
.page-title{font-family:'Fraunces',serif;font-size:22px;font-weight:700;color:var(--ink);margin-bottom:4px}
.page-sub{font-size:13px;color:var(--mu);margin-bottom:20px}
.card{background:var(--wh);border-radius:var(--r);box-shadow:var(--s1);overflow:hidden}
.card-p{padding:20px 22px}
table{width:100%;border-collapse:collapse}
thead tr{background:var(--cr)}
th{padding:10px 14px;font-size:10px;font-weight:700;color:var(--mu);text-transform:uppercase;letter-spacing:.6px;text-align:left;border-bottom:1px solid var(--cd)}
td{padding:12px 14px;font-size:13px;color:var(--ink);border-bottom:1px solid var(--cd);vertical-align:middle}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover{background:#f5f8f4}
.badge{display:inline-flex;align-items:center;padding:4px 11px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px}
.bp{background:var(--amp);color:var(--amd)}
.bap{background:var(--gp);color:var(--gd)}
.brj{background:var(--rsp);color:var(--rsd)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--rs2);font-family:'DM Sans',sans-serif;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .18s;border:none}
.btn-p{background:var(--gd);color:#fff}.btn-p:hover{background:var(--gm)}
.btn-s{background:var(--wh);border:1.5px solid var(--cd);color:var(--ink)}.btn-s:hover{border-color:var(--gm);color:var(--gm)}
.info-row{display:flex;gap:8px;padding:10px 0;border-bottom:1px solid var(--cd);font-size:13px}
.info-row:last-child{border-bottom:none}
.info-label{color:var(--mu);width:160px;flex-shrink:0}
.info-val{font-weight:500;color:var(--ink)}
.empty{text-align:center;padding:40px;color:var(--mu)}
@media(max-width:600px){
  .topbar{padding:12px 16px}
  .wrap{padding:18px 14px}
  .info-row{flex-direction:column;gap:2px}
  .info-label{width:auto}
}
</style>
</head>
<body>
<div class="topbar">
  <div class="topbar-brand">SAPD Mekarsari</div>
  <a href="{{ url('/dashboard') }}" class="topbar-back">&larr; Kembali ke Dashboard</a>
</div>
<div class="wrap">
    @if (session('success'))
        <div class="alert alert-success" style="background:var(--gp);color:var(--gd);padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px">{{ session('success') }}</div>
    @endif

    @yield('content')
</div>
</body>
</html>
