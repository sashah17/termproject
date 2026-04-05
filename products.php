<?php
$pageTitle = 'Browse';
require_once __DIR__ . '/includes/header.php';

$q         = trim($_GET['q']        ?? '');
$category  = trim($_GET['category'] ?? '');
$condition = trim($_GET['condition'] ?? '');
$maxPrice  = isset($_GET['max_price'])&&is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$sort      = $_GET['sort'] ?? 'newest';

$conditions = ['Like New','Good','Fair','For Parts'];
$sorts = ['newest'=>'Newest First','price_asc'=>'Price: Low → High','price_desc'=>'Price: High → Low'];

$where = ['p.stock > 0']; $params = [];
if($q){ $where[] = "(p.name LIKE ? OR p.description LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; }
if($category){ $where[] = "p.category = ?"; $params[] = $category; }
if($condition){ $where[] = "p.condition = ?"; $params[] = $condition; }
if($maxPrice !== null){ $where[] = "p.price <= ?"; $params[] = $maxPrice; }

$orderBy = match($sort){ 'price_asc'=>'p.price ASC','price_desc'=>'p.price DESC',default=>'p.created_at DESC' };
$sql = "SELECT p.*,u.name AS seller_name,COALESCE((SELECT AVG(rating) FROM reviews r WHERE r.product_id=p.id),0) AS avg_rating
  FROM products p JOIN users u ON p.seller_id=u.id WHERE ".implode(' AND ',$where)." ORDER BY $orderBy";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $products = $stmt->fetchAll();

$cats = $pdo->query("SELECT DISTINCT category FROM products WHERE stock>0 ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$maxDb = (int)ceil($pdo->query("SELECT MAX(price) FROM products")->fetchColumn());
$catIcons = ['Phones & Tablets'=>'📱','Laptops & PCs'=>'💻','Audio'=>'🎧','Gaming'=>'🎮','Cameras'=>'📷','Wearables'=>'⌚','Components & Parts'=>'🔧'];
?>

<div style="display:grid;grid-template-columns:240px 1fr;gap:24px;align-items:start;">

  <!-- Sidebar -->
  <div class="vm-sidebar">
    <div class="vm-sidebar-lbl"><i class="bi bi-funnel me-1"></i>Filters</div>
    <form method="GET" action="<?= BASE_URL ?>/products.php">
      <?php if($q): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
      <div class="vm-form-group">
        <label class="vm-sidebar-lbl">Category</label>
        <select name="category" class="vm-input" onchange="this.form.submit()">
          <option value="">All Categories</option>
          <?php foreach($cats as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= $category===$c?'selected':'' ?>>
              <?= ($catIcons[$c]??'🔌').' '.htmlspecialchars($c) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="vm-form-group">
        <label class="vm-sidebar-lbl">Condition</label>
        <select name="condition" class="vm-input" onchange="this.form.submit()">
          <option value="">Any Condition</option>
          <?php foreach($conditions as $c): ?>
            <option value="<?= $c ?>" <?= $condition===$c?'selected':'' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="vm-form-group">
        <label class="vm-sidebar-lbl">Max Price: <span id="priceLabel">$<?= $maxPrice ?? $maxDb ?></span></label>
        <input type="range" class="vm-range" id="priceRange" name="max_price"
          min="0" max="<?= $maxDb ?>" step="5" value="<?= $maxPrice ?? $maxDb ?>">
      </div>
      <div class="vm-form-group">
        <label class="vm-sidebar-lbl">Sort By</label>
        <select name="sort" class="vm-input" onchange="this.form.submit()">
          <?php foreach($sorts as $v=>$l): ?>
            <option value="<?= $v ?>" <?= $sort===$v?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn-volt" style="width:100%;justify-content:center;">Apply</button>
      <a href="<?= BASE_URL ?>/products.php" class="btn-outline" style="width:100%;justify-content:center;margin-top:8px;">Clear</a>
    </form>
  </div>

  <!-- Grid -->
  <div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
      <div>
        <h2 style="font-size:1.3rem;margin:0;">
          <?= $q ? 'Results for "<em>'.htmlspecialchars($q).'</em>"' : ($category ? htmlspecialchars($category) : 'All Listings') ?>
        </h2>
        <div style="font-size:.8rem;color:var(--text3);font-family:var(--mono);margin-top:4px;"><?= count($products) ?> listing<?= count($products)!=1?'s':'' ?> found</div>
      </div>
    </div>

    <?php if(empty($products)): ?>
      <div class="vm-empty">
        <i class="bi bi-search"></i>
        <div>No listings match your search.</div>
        <a href="<?= BASE_URL ?>/products.php" class="btn-outline" style="margin-top:16px;display:inline-flex;">Clear Filters</a>
      </div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px;">
      <?php foreach($products as $p): ?>
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
          <?php if($p['avg_rating']>0): ?><div class="vm-stars"><?= str_repeat('★',round($p['avg_rating'])) ?><?= str_repeat('☆',5-round($p['avg_rating'])) ?></div><?php endif; ?>
          <div class="vm-card-footer">
            <span class="vm-price">$<?= number_format($p['price'],2) ?></span>
            <a href="<?= BASE_URL ?>/product.php?id=<?= $p['id'] ?>" class="btn-card">View</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
