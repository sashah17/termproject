<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

$cats = $pdo->query("SELECT category, COUNT(*) cnt FROM products WHERE stock>0 GROUP BY category ORDER BY cnt DESC")->fetchAll();
$featured = $pdo->query("SELECT p.*, COALESCE((SELECT AVG(rating) FROM reviews r WHERE r.product_id=p.id),0) AS avg_rating
  FROM products p WHERE p.stock>0 ORDER BY p.created_at DESC LIMIT 8")->fetchAll();

$catIcons = [
  'Phones & Tablets' => '📱','Laptops & PCs' => '💻','Audio' => '🎧',
  'Gaming' => '🎮','Cameras' => '📷','Wearables' => '⌚','Components & Parts' => '🔧',
];
$totalListings = $pdo->query("SELECT COUNT(*) FROM products WHERE stock>0")->fetchColumn();
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>

<!-- HERO -->
<div class="vm-hero" style="margin:-32px -24px 0;padding-left:24px;padding-right:24px;">
  <div style="max-width:1320px;margin:0 auto;" class="vm-hero-inner">
    <div style="max-width:640px;">
      <div class="vm-hero-tag"><i class="bi bi-lightning-charge-fill"></i> ELECTRONICS ONLY MARKETPLACE</div>
      <h1>Buy &amp; Sell <em>Pre-Owned</em><br>Tech Gear</h1>
      <p class="sub">From flagship phones to vintage audio — find quality second-hand electronics at unbeatable prices. Every listing is from a real seller.</p>
      <div class="vm-hero-ctas">
        <a href="<?= BASE_URL ?>/products.php" class="btn-volt"><i class="bi bi-grid-3x3-gap"></i> Browse Listings</a>
        <a href="<?= BASE_URL ?>/sell.php" class="btn-outline"><i class="bi bi-plus-circle"></i> List Your Device</a>
      </div>
      <div class="vm-hero-stats">
        <div class="vm-hero-stat"><div class="num"><?= number_format($totalListings) ?>+</div><div class="lbl">Active Listings</div></div>
        <div class="vm-hero-stat"><div class="num"><?= number_format($totalUsers) ?>+</div><div class="lbl">Registered Users</div></div>
        <div class="vm-hero-stat"><div class="num"><?= count($cats) ?></div><div class="lbl">Categories</div></div>
      </div>
    </div>
  </div>
</div>

<!-- CATEGORIES -->
<div class="vm-section" style="margin-top:40px;">
  <div class="vm-section-head">
    <h2 class="vm-section-title">Browse by Category</h2>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;">
    <?php foreach($cats as $c): ?>
    <a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($c['category']) ?>" class="vm-cat-chip">
      <span class="icon"><?= $catIcons[$c['category']] ?? '🔌' ?></span>
      <span class="name"><?= htmlspecialchars($c['category']) ?></span>
      <span class="cnt"><?= $c['cnt'] ?> listing<?= $c['cnt']!=1?'s':'' ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- LATEST LISTINGS -->
<div class="vm-section">
  <div class="vm-section-head">
    <h2 class="vm-section-title">Latest Listings</h2>
    <a href="<?= BASE_URL ?>/products.php" class="btn-outline btn-sm">View All <i class="bi bi-arrow-right"></i></a>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;">
    <?php foreach($featured as $p): ?>
    <?php $cond = strtolower(str_replace(' ','_',$p['condition'])); ?>
    <div class="vm-product-card">
      <div class="vm-card-img">
        <a href="<?= BASE_URL ?>/product.php?id=<?= $p['id'] ?>">
          <img src="<?= htmlspecialchars($p['image_url']?:'https://via.placeholder.com/400x300/13151e/00e5a0?text=No+Image') ?>"
            alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
        </a>
        <div class="vm-card-badges">
          <span class="vm-badge vm-badge-blue"><?= htmlspecialchars($p['category']) ?></span>
          <span class="vm-badge <?= in_array($p['condition'],['Like New','Good'])?'vm-badge-green':($p['condition']==='Fair'?'vm-badge-orange':'vm-badge-gray') ?>">
            <?= htmlspecialchars($p['condition']) ?>
          </span>
        </div>
      </div>
      <div class="vm-card-body">
        <a href="<?= BASE_URL ?>/product.php?id=<?= $p['id'] ?>" class="vm-card-title"><?= htmlspecialchars($p['name']) ?></a>
        <?php if($p['avg_rating']>0): ?>
          <div class="vm-stars"><?= str_repeat('★', round($p['avg_rating'])) ?><?= str_repeat('☆', 5-round($p['avg_rating'])) ?></div>
        <?php endif; ?>
        <div class="vm-card-footer">
          <span class="vm-price">$<?= number_format($p['price'],2) ?></span>
          <a href="<?= BASE_URL ?>/product.php?id=<?= $p['id'] ?>" class="btn-card">View →</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- WHY -->
<div class="vm-section" style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:40px;margin-top:0;">
  <h2 class="vm-section-title" style="margin-bottom:28px;">Why VoltMarket?</h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:28px;">
    <?php foreach([
      ['bi-shield-check','Secure Platform','Password hashing, SQL injection protection, and server-side validation on every form.'],
      ['bi-phone','Mobile Friendly','Fully responsive — browse and buy from any device, any screen size.'],
      ['bi-lightning-charge','Electronics Only','No clutter. Every listing is tech. Phones, audio, gaming, cameras &amp; more.'],
      ['bi-clock-history','Order Tracking','Every purchase is logged. View your full order history any time you log in.'],
    ] as [$icon,$title,$desc]): ?>
    <div>
      <div style="width:44px;height:44px;background:rgba(0,229,160,.1);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
        <i class="bi <?= $icon ?>" style="font-size:1.3rem;color:var(--accent);"></i>
      </div>
      <div style="font-weight:700;font-family:var(--display);margin-bottom:6px;"><?= $title ?></div>
      <div style="font-size:.85rem;color:var(--text2);line-height:1.6;"><?= $desc ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
