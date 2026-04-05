<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$cartCount = 0;
if(isLoggedIn()){
  $s = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id=?");
  $s->execute([$_SESSION['user_id']]); $cartCount = (int)$s->fetchColumn();
} elseif(!empty($_SESSION['cart'])){ $cartCount = array_sum($_SESSION['cart']); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle).' | ' : '' ?>VoltMarket</title>

<!-- Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* ── VoltMarket Dark Theme ── */
:root {
  --vm-bg:      #08090d;
  --vm-bg2:     #0f1117;
  --vm-bg3:     #15171f;
  --vm-card:    #13151e;
  --vm-border:  #1e2130;
  --vm-border2: #2a2f45;
  --vm-accent:  #00e5a0;
  --vm-orange:  #ff5f2e;
  --vm-blue:    #3d8bff;
  --vm-text:    #e8eaf0;
  --vm-text2:   #8890a8;
  --vm-text3:   #555d7a;
  --vm-mono:    'Space Mono', monospace;
  --vm-sans:    'DM Sans', sans-serif;
  --vm-display: 'Syne', sans-serif;
  --vm-radius:  6px;
  --vm-radius-lg: 12px;
  --vm-glow:    0 0 24px rgba(0,229,160,.14);
  --vm-shadow:  0 4px 24px rgba(0,0,0,.5);
}

/* Global */
*, *::before, *::after { box-sizing: border-box; }
html { scroll-behavior: smooth; }
body { background: var(--vm-bg) !important; color: var(--vm-text) !important; font-family: var(--vm-sans) !important; min-height: 100vh; }
h1,h2,h3,h4,h5,h6 { font-family: var(--vm-display) !important; color: var(--vm-text) !important; }
a { color: var(--vm-accent); text-decoration: none; transition: color .2s; }
a:hover { color: #fff; }
p { color: var(--vm-text2); }

/* Scrollbar */
::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-track { background: var(--vm-bg2); }
::-webkit-scrollbar-thumb { background: var(--vm-border2); border-radius: 3px; }

/* ── Navbar ── */
.vm-navbar {
  background: rgba(8,9,13,.97) !important;
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--vm-border);
  padding: 0 !important;
  position: sticky; top: 0; z-index: 1030;
}
.vm-navbar .navbar-brand {
  font-family: var(--vm-mono);
  font-weight: 700;
  color: #fff !important;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  gap: 8px;
}
.vm-bolt {
  width: 22px; height: 22px;
  background: var(--vm-accent);
  clip-path: polygon(60% 0%,40% 45%,70% 45%,35% 100%,55% 55%,25% 55%);
  display: inline-block;
  animation: boltglow 3s ease infinite;
  flex-shrink: 0;
}
@keyframes boltglow {
  0%,100% { filter: drop-shadow(0 0 5px var(--vm-accent)); }
  50%      { filter: drop-shadow(0 0 14px var(--vm-accent)); }
}
.vm-navbar .nav-link { color: var(--vm-text2) !important; font-size: .875rem; font-weight: 500; padding: 8px 12px !important; border-radius: var(--vm-radius); transition: color .2s, background .2s; display: flex; align-items: center; gap: 5px; }
.vm-navbar .nav-link:hover { color: #fff !important; background: var(--vm-bg3); }
.vm-navbar .nav-link.vm-accent-btn { background: var(--vm-accent); color: #000 !important; font-weight: 700; }
.vm-navbar .nav-link.vm-accent-btn:hover { background: #00ffb3; }
.vm-search-form { display: flex; background: var(--vm-bg3); border: 1px solid var(--vm-border2); border-radius: var(--vm-radius); overflow: hidden; }
.vm-search-form:focus-within { border-color: var(--vm-accent); }
.vm-search-form input { background: transparent; border: none; outline: none; color: var(--vm-text); padding: 8px 14px; font-size: .875rem; width: 300px; }
.vm-search-form input::placeholder { color: var(--vm-text3); }
.vm-search-form button { background: var(--vm-accent); border: none; color: #000; padding: 0 14px; font-weight: 700; font-size: .82rem; cursor: pointer; white-space: nowrap; }
.vm-search-form button:hover { background: #00ffb3; }
.cart-pill { background: var(--vm-orange); color: #fff; font-size: .6rem; font-weight: 700; padding: 1px 5px; border-radius: 99px; font-family: var(--vm-mono); vertical-align: middle; }
.vm-navbar .dropdown-menu { background: var(--vm-card); border: 1px solid var(--vm-border2); border-radius: var(--vm-radius-lg); padding: 8px; box-shadow: var(--vm-shadow); }
.vm-navbar .dropdown-item { color: var(--vm-text2); border-radius: var(--vm-radius); font-size: .875rem; padding: 8px 12px; }
.vm-navbar .dropdown-item:hover { background: var(--vm-bg3); color: #fff; }
.vm-navbar .navbar-toggler { border: 1px solid var(--vm-border2); color: var(--vm-text2); padding: 6px 10px; }
.vm-navbar .navbar-toggler-icon { filter: invert(1); }
.vm-navbar .navbar-collapse { padding: 8px 0; }

/* ── Hero ── */
.vm-hero {
  background: var(--vm-bg2);
  border-bottom: 1px solid var(--vm-border);
  padding: 80px 0 70px;
  position: relative;
  overflow: hidden;
}
.vm-hero::before {
  content:'';
  position: absolute; inset: 0;
  background-image: linear-gradient(rgba(0,229,160,.025) 1px,transparent 1px),
    linear-gradient(90deg,rgba(0,229,160,.025) 1px,transparent 1px);
  background-size: 42px 42px;
  pointer-events: none;
}
.vm-hero::after {
  content:'';
  position: absolute; top:-40%; left:50%; transform:translateX(-50%);
  width: 700px; height: 700px;
  background: radial-gradient(circle,rgba(0,229,160,.06) 0%,transparent 65%);
  pointer-events: none;
}
.vm-hero-inner { position: relative; z-index: 1; }
.vm-hero-tag {
  display: inline-flex; align-items: center; gap: 6px;
  background: rgba(0,229,160,.08); border: 1px solid rgba(0,229,160,.2);
  color: var(--vm-accent); font-family: var(--vm-mono); font-size: .72rem;
  letter-spacing: .06em; padding: 4px 12px; border-radius: 99px; margin-bottom: 18px;
}
.vm-hero h1 {
  font-size: clamp(2.2rem,5vw,3.8rem); font-weight: 800;
  line-height: 1.1; letter-spacing: -.03em; color: #fff !important; margin-bottom: 18px;
}
.vm-hero h1 em { color: var(--vm-accent); font-style: normal; }
.vm-hero .vm-sub { color: var(--vm-text2) !important; font-size: 1.05rem; max-width: 500px; margin-bottom: 28px; }
.vm-hero-stats { display: flex; gap: 36px; flex-wrap: wrap; margin-top: 44px; padding-top: 32px; border-top: 1px solid var(--vm-border); }
.vm-stat-num { font-family: var(--vm-mono); font-size: 1.4rem; color: var(--vm-accent); font-weight: 700; }
.vm-stat-lbl { font-size: .78rem; color: var(--vm-text3); }

/* ── Buttons ── */
.btn-volt { display: inline-flex; align-items: center; gap: 6px; background: var(--vm-accent); color: #000 !important; font-weight: 700; padding: 10px 20px; border-radius: var(--vm-radius); font-size: .875rem; border: none; cursor: pointer; transition: background .2s, transform .1s; text-decoration: none !important; }
.btn-volt:hover { background: #00ffb3; color: #000 !important; transform: translateY(-1px); }
.btn-vm-outline { display: inline-flex; align-items: center; gap: 6px; background: transparent; color: var(--vm-text2) !important; border: 1px solid var(--vm-border2); padding: 9px 18px; border-radius: var(--vm-radius); font-size: .875rem; cursor: pointer; transition: all .2s; text-decoration: none !important; }
.btn-vm-outline:hover { border-color: var(--vm-accent); color: var(--vm-accent) !important; }
.btn-volt-sm { padding: 6px 14px !important; font-size: .8rem !important; }

/* ── Section ── */
.vm-section { padding: 44px 0; }
.vm-section-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; }
.vm-section-title { font-family: var(--vm-display) !important; font-size: 1.25rem; font-weight: 700; color: #fff !important; display: flex; align-items: center; gap: 10px; margin: 0; }
.vm-section-title::before { content:''; width: 3px; height: 18px; background: var(--vm-accent); border-radius: 2px; display: block; }

/* ── Category Chips ── */
.vm-cat-chip {
  background: var(--vm-card); border: 1px solid var(--vm-border); border-radius: var(--vm-radius);
  padding: 16px 12px; text-align: center; text-decoration: none !important; color: var(--vm-text2) !important;
  transition: all .2s; display: block;
}
.vm-cat-chip:hover {
  border-color: var(--vm-accent); color: var(--vm-accent) !important;
  background: rgba(0,229,160,.04); transform: translateY(-2px); box-shadow: var(--vm-glow);
}
.vm-cat-chip .vm-icon { font-size: 1.5rem; display: block; margin-bottom: 8px; }
.vm-cat-chip .vm-name { font-size: .75rem; font-weight: 700; font-family: var(--vm-mono); display: block; }
.vm-cat-chip .vm-cnt { font-size: .68rem; color: var(--vm-text3); display: block; margin-top: 3px; }

/* ── Product Cards ── */
.vm-product-card {
  background: var(--vm-card); border: 1px solid var(--vm-border); border-radius: var(--vm-radius-lg);
  overflow: hidden; transition: border-color .25s, transform .25s, box-shadow .25s;
  display: flex; flex-direction: column; height: 100%;
}
.vm-product-card:hover { border-color: rgba(0,229,160,.4); transform: translateY(-4px); box-shadow: 0 8px 32px rgba(0,229,160,.1); }
.vm-card-img { position: relative; background: var(--vm-bg3); height: 200px; overflow: hidden; }
.vm-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
.vm-product-card:hover .vm-card-img img { transform: scale(1.06); }
.vm-card-badges { position: absolute; top: 10px; left: 10px; display: flex; gap: 5px; flex-wrap: wrap; }
.vm-badge { font-family: var(--vm-mono); font-size: .62rem; letter-spacing: .04em; padding: 2px 7px; border-radius: 3px; font-weight: 700; text-transform: uppercase; }
.vm-badge-blue   { background: rgba(61,139,255,.15); color: var(--vm-blue);   border: 1px solid rgba(61,139,255,.3); }
.vm-badge-green  { background: rgba(0,229,160,.12);  color: var(--vm-accent); border: 1px solid rgba(0,229,160,.3); }
.vm-badge-orange { background: rgba(255,95,46,.12);  color: var(--vm-orange); border: 1px solid rgba(255,95,46,.3); }
.vm-badge-gray   { background: rgba(136,144,168,.1); color: var(--vm-text2);  border: 1px solid var(--vm-border2); }
.vm-card-body { padding: 15px; flex: 1; display: flex; flex-direction: column; }
.vm-card-title { font-weight: 600; font-size: .9rem; color: var(--vm-text) !important; text-decoration: none !important; display: block; margin-bottom: 5px; transition: color .2s; line-height: 1.35; }
.vm-card-title:hover { color: var(--vm-accent) !important; }
.vm-card-meta { font-size: .75rem; color: var(--vm-text3); font-family: var(--vm-mono); }
.vm-card-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 12px; border-top: 1px solid var(--vm-border); }
.vm-price { font-family: var(--vm-mono); font-size: 1.05rem; font-weight: 700; color: var(--vm-accent); }
.btn-card { background: rgba(0,229,160,.08); border: 1px solid rgba(0,229,160,.2); color: var(--vm-accent) !important; padding: 6px 13px; border-radius: var(--vm-radius); font-size: .78rem; font-weight: 600; transition: all .2s; text-decoration: none !important; cursor: pointer; }
.btn-card:hover { background: var(--vm-accent); color: #000 !important; }

/* ── Stars ── */
.vm-stars { color: #fbbf24; font-size: .85rem; }

/* ── Cards / Panels ── */
.vm-card { background: var(--vm-card); border: 1px solid var(--vm-border); border-radius: var(--vm-radius-lg); }
.vm-panel { background: var(--vm-card); border: 1px solid var(--vm-border); border-radius: var(--vm-radius-lg); overflow: hidden; }
.vm-panel-head { padding: 14px 20px; border-bottom: 1px solid var(--vm-border); display: flex; justify-content: space-between; align-items: center; }
.vm-panel-head h5 { font-family: var(--vm-mono); font-size: .75rem; letter-spacing: .1em; text-transform: uppercase; color: var(--vm-text2); margin: 0; }

/* ── Forms ── */
.vm-input { background: var(--vm-bg3) !important; border: 1px solid var(--vm-border2) !important; color: var(--vm-text) !important; border-radius: var(--vm-radius) !important; padding: 9px 12px !important; font-family: var(--vm-sans) !important; font-size: .875rem !important; width: 100%; transition: border-color .2s, box-shadow .2s; outline: none; }
.vm-input:focus { border-color: var(--vm-accent) !important; box-shadow: 0 0 0 2px rgba(0,229,160,.08) !important; color: var(--vm-text) !important; }
.vm-input::placeholder { color: var(--vm-text3) !important; }
select.vm-input option { background: var(--vm-bg2); color: var(--vm-text); }
textarea.vm-input { min-height: 90px; resize: vertical; }
.vm-label { display: block; font-size: .75rem; font-weight: 700; color: var(--vm-text2); margin-bottom: 7px; font-family: var(--vm-mono); letter-spacing: .05em; text-transform: uppercase; }
.vm-form-group { margin-bottom: 18px; }
input[type=range].vm-range { -webkit-appearance: none; width: 100%; height: 3px; background: var(--vm-border2); border-radius: 2px; outline: none; cursor: pointer; }
input[type=range].vm-range::-webkit-slider-thumb { -webkit-appearance: none; width: 14px; height: 14px; background: var(--vm-accent); border-radius: 50%; cursor: pointer; box-shadow: 0 0 6px rgba(0,229,160,.5); }

/* ── Tables ── */
.vm-table { width: 100%; border-collapse: collapse; }
.vm-table th { background: var(--vm-bg3); color: var(--vm-text2); font-family: var(--vm-mono); font-size: .68rem; letter-spacing: .1em; text-transform: uppercase; padding: 11px 16px; text-align: left; border-bottom: 1px solid var(--vm-border2); }
.vm-table td { padding: 13px 16px; border-bottom: 1px solid var(--vm-border); font-size: .875rem; vertical-align: middle; }
.vm-table tr:last-child td { border-bottom: none; }
.vm-table tr:hover td { background: rgba(255,255,255,.01); }

/* ── Status badges ── */
.vm-status { font-family: var(--vm-mono); font-size: .65rem; letter-spacing: .06em; text-transform: uppercase; padding: 3px 9px; border-radius: 99px; font-weight: 700; }
.s-pending    { background: rgba(251,191,36,.1);  color: #fbbf24;  border: 1px solid rgba(251,191,36,.25); }
.s-processing { background: rgba(61,139,255,.1);  color: var(--vm-blue); border: 1px solid rgba(61,139,255,.25); }
.s-shipped    { background: rgba(167,139,250,.1); color: #a78bfa; border: 1px solid rgba(167,139,250,.25); }
.s-delivered  { background: rgba(0,229,160,.1);   color: var(--vm-accent); border: 1px solid rgba(0,229,160,.25); }
.s-cancelled  { background: rgba(255,95,46,.1);   color: var(--vm-orange); border: 1px solid rgba(255,95,46,.25); }

/* ── Alerts ── */
.vm-alert { border-radius: var(--vm-radius); padding: 12px 16px; font-size: .875rem; display: flex; align-items: flex-start; gap: 10px; margin-bottom: 20px; }
.a-success { background: rgba(0,229,160,.08); border: 1px solid rgba(0,229,160,.25); color: var(--vm-accent); }
.a-danger   { background: rgba(255,95,46,.08);  border: 1px solid rgba(255,95,46,.3);  color: #ff7d54; }
.a-info     { background: rgba(61,139,255,.08); border: 1px solid rgba(61,139,255,.3);  color: var(--vm-blue); }
.vm-alert ul { margin: 4px 0 0 16px; padding: 0; }

/* ── Admin stat cards ── */
.vm-stat-card { background: var(--vm-card); border: 1px solid var(--vm-border); border-radius: var(--vm-radius-lg); padding: 20px; display: flex; align-items: center; gap: 14px; }
.vm-stat-icon { width: 44px; height: 44px; border-radius: var(--vm-radius); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
.ic-green { background: rgba(0,229,160,.12); color: var(--vm-accent); }
.ic-blue  { background: rgba(61,139,255,.12); color: var(--vm-blue); }
.ic-orange{ background: rgba(255,95,46,.12);  color: var(--vm-orange); }
.ic-purple{ background: rgba(167,139,250,.12);color: #a78bfa; }

/* ── Cart ── */
.vm-cart-item { background: var(--vm-card); border: 1px solid var(--vm-border); border-radius: var(--vm-radius-lg); padding: 14px 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 10px; }
.vm-cart-img { width: 68px; height: 68px; object-fit: cover; border-radius: var(--vm-radius); background: var(--vm-bg3); flex-shrink: 0; }
.vm-order-panel { background: var(--vm-card); border: 1px solid var(--vm-border); border-radius: var(--vm-radius-lg); padding: 22px; position: sticky; top: 80px; }
.vm-divider { border: none; border-top: 1px solid var(--vm-border); margin: 14px 0; }
.vm-qty { display: inline-flex; align-items: center; background: var(--vm-bg3); border: 1px solid var(--vm-border2); border-radius: var(--vm-radius); overflow: hidden; }
.vm-qty button { background: transparent; border: none; color: var(--vm-text2); padding: 5px 11px; cursor: pointer; font-size: .95rem; transition: color .2s, background .2s; }
.vm-qty button:hover { color: var(--vm-accent); background: rgba(0,229,160,.08); }
.vm-qty input { background: transparent; border: none; color: var(--vm-text); width: 38px; text-align: center; font-family: var(--vm-mono); font-size: .875rem; outline: none; padding: 5px 0; }

/* ── Auth ── */
.vm-auth-outer { min-height: calc(100vh - 64px); display: flex; align-items: center; justify-content: center; padding: 40px 16px; }
.vm-auth-card { background: var(--vm-card); border: 1px solid var(--vm-border); border-radius: var(--vm-radius-lg); padding: 40px; width: 100%; max-width: 440px; box-shadow: var(--vm-shadow); }

/* ── Misc ── */
.vm-empty { text-align: center; padding: 80px 20px; color: var(--vm-text3); }
.vm-empty i { font-size: 3rem; display: block; margin-bottom: 16px; opacity: .3; }
.vm-bc { display: flex; align-items: center; gap: 8px; font-size: .8rem; margin-bottom: 24px; color: var(--vm-text3); flex-wrap: wrap; }
.vm-bc a { color: var(--vm-text3) !important; }
.vm-bc a:hover { color: var(--vm-accent) !important; }
.vm-pwd-bar { height: 3px; background: var(--vm-border2); border-radius: 2px; margin-top: 8px; overflow: hidden; }
.vm-pwd-fill { height: 100%; width: 0; border-radius: 2px; transition: width .3s, background .3s; }

/* ── Footer ── */
.vm-footer { background: var(--vm-bg2); border-top: 1px solid var(--vm-border); padding: 56px 0 32px; margin-top: 80px; }
.vm-footer h6 { font-family: var(--vm-mono); font-size: .7rem; letter-spacing: .12em; text-transform: uppercase; color: var(--vm-text2); margin-bottom: 16px; }
.vm-footer ul { list-style: none; padding: 0; margin: 0; }
.vm-footer ul li { margin-bottom: 9px; }
.vm-footer ul li a { color: var(--vm-text3) !important; font-size: .85rem; transition: color .2s; }
.vm-footer ul li a:hover { color: var(--vm-accent) !important; }
.vm-footer-bottom { border-top: 1px solid var(--vm-border); margin-top: 40px; padding-top: 24px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; }

/* Responsive */
@media(max-width:991px){ .vm-search-form input{width:200px;} .vm-hero{padding:48px 0 40px;} .vm-hero-stats{gap:20px;} }
@media(max-width:575px){ .vm-auth-card{padding:28px 20px;} .vm-hero h1{font-size:1.9rem;} .vm-search-form{display:none;} }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg vm-navbar px-3">
  <div class="container-xl">
    <a class="navbar-brand vm-navbar-brand" href="<?= BASE_URL ?>/index.php">
      <span class="vm-bolt"></span>VoltMarket
    </a>
    <form class="vm-search-form d-none d-md-flex mx-3" action="<?= BASE_URL ?>/products.php" method="GET">
      <input type="search" name="q" placeholder="Search electronics…" value="<?= htmlspecialchars($_GET['q']??'') ?>">
      <button type="submit"><i class="bi bi-search"></i> Search</button>
    </form>
    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#vmNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="vmNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products.php"><i class="bi bi-grid-3x3-gap"></i> Browse</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/sell.php"><i class="bi bi-plus-circle"></i> Sell</a></li>
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/cart.php">
            <i class="bi bi-bag"></i> Cart
            <?php if($cartCount>0): ?><span class="cart-pill"><?= $cartCount ?></span><?php endif; ?>
          </a>
        </li>
        <?php if(isLoggedIn()): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i> <?= htmlspecialchars(getCurrentUserName()) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-bag me-2"></i>My Orders</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/sell.php"><i class="bi bi-tags me-2"></i>My Listings</a></li>
              <?php if(isAdmin()): ?>
                <li><hr style="border-color:var(--vm-border);margin:6px 0;"></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/index.php" style="color:#a78bfa;"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
              <?php endif; ?>
              <li><hr style="border-color:var(--vm-border);margin:6px 0;"></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout.php" style="color:var(--vm-orange);"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
          <li class="nav-item"><a class="nav-link vm-accent-btn ms-1" href="<?= BASE_URL ?>/register.php"><i class="bi bi-person-plus"></i> Register</a></li>
        <?php endif; ?>
      </ul>
      <!-- Mobile search -->
      <form class="d-flex d-md-none mt-2 mb-1 vm-search-form" action="<?= BASE_URL ?>/products.php" method="GET" style="width:100%;">
        <input type="search" name="q" placeholder="Search electronics…" value="<?= htmlspecialchars($_GET['q']??'') ?>" style="flex:1;">
        <button type="submit"><i class="bi bi-search"></i></button>
      </form>
    </div>
  </div>
</nav>

<div style="padding:32px 0 60px;">
<div class="container-xl px-3 px-md-4">
