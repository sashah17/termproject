<?php
$pageTitle = 'Sell a Device';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';

$categories = ['Phones & Tablets','Laptops & PCs','Audio','Gaming','Cameras','Wearables','Components & Parts'];
$conditions = ['Like New','Good','Fair','For Parts'];
$errors=[]; $success=false; $newId=null; $data=[];

if($_SERVER['REQUEST_METHOD']==='POST'){
  $data['name']=trim($_POST['name']??''); $data['description']=trim($_POST['description']??'');
  $data['price']=$_POST['price']??''; $data['image_url']=trim($_POST['image_url']??'');
  $data['category']=$_POST['category']??''; $data['condition']=$_POST['condition']??''; $data['stock']=$_POST['stock']??'1';
  if(strlen($data['name'])<2) $errors[]='Title must be at least 2 characters.';
  if(!is_numeric($data['price'])||(float)$data['price']<=0) $errors[]='Price must be greater than $0.';
  if(!in_array($data['category'],$categories)) $errors[]='Select a valid category.';
  if(!in_array($data['condition'],$conditions)) $errors[]='Select a valid condition.';
  if(!is_numeric($data['stock'])||(int)$data['stock']<1) $errors[]='Quantity must be at least 1.';
  if(!empty($data['image_url'])&&!filter_var($data['image_url'],FILTER_VALIDATE_URL)) $errors[]='Image URL must be a valid URL.';
  if(empty($errors)){
    $s=$pdo->prepare("INSERT INTO products(name,description,price,image_url,category,`condition`,stock,seller_id) VALUES(?,?,?,?,?,?,?,?)");
    $s->execute([$data['name'],$data['description'],(float)$data['price'],$data['image_url'],$data['category'],$data['condition'],(int)$data['stock'],$_SESSION['user_id']]);
    $newId=$pdo->lastInsertId(); $success=true; $data=[];
  }
}

$myListings=$pdo->prepare("SELECT * FROM products WHERE seller_id=? ORDER BY created_at DESC");
$myListings->execute([$_SESSION['user_id']]); $myListings=$myListings->fetchAll();

$flash=$_GET['updated']??''===1?'Listing updated!':(($_GET['deleted']??'')==='1'?'Listing deleted.':'');
require_once __DIR__ . '/includes/header.php';
?>
<div style="display:grid;grid-template-columns:1fr 380px;gap:32px;align-items:start;">

  <!-- Form -->
  <div>
    <h1 style="font-size:1.6rem;margin-bottom:6px;">List a Device for Sale</h1>
    <p style="color:var(--text2);font-size:.875rem;margin-bottom:24px;">Fill in the details below. Your listing goes live immediately.</p>

    <?php if($success): ?>
    <div class="vm-alert a-success" data-dismiss><i class="bi bi-check-circle"></i>
      <div>Listing live! <a href="<?= BASE_URL ?>/product.php?id=<?= $newId ?>" style="color:#fff;font-weight:600;">View it →</a></div>
    </div>
    <?php endif; ?>
    <?php if($flash): ?><div class="vm-alert a-success" data-dismiss><i class="bi bi-check-circle"></i> <?= htmlspecialchars($flash) ?></div><?php endif; ?>
    <?php if($errors): ?><div class="vm-alert a-danger"><div><i class="bi bi-exclamation-circle"></i></div><ul style="margin:0 0 0 4px;padding:0 0 0 14px;"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

    <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;">
      <form method="POST" class="vm-form" novalidate>
        <div class="vm-form-group"><label class="vm-sidebar-lbl">Device Title *</label><input type="text" name="name" class="vm-input" placeholder="e.g. iPhone 15 Pro 256GB – Natural Titanium" value="<?= htmlspecialchars($data['name']??'') ?>" required minlength="2"></div>
        <div class="vm-form-group"><label class="vm-sidebar-lbl">Description *</label><textarea name="description" class="vm-input" style="min-height:110px;" placeholder="Describe condition, what's included, battery health, any defects…" required><?= htmlspecialchars($data['description']??'') ?></textarea></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="vm-form-group"><label class="vm-sidebar-lbl">Category *</label>
            <select name="category" class="vm-input" required>
              <option value="" disabled <?= empty($data['category'])?'selected':'' ?>>Select…</option>
              <?php foreach($categories as $c): ?><option value="<?= $c ?>" <?= ($data['category']??'')===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="vm-form-group"><label class="vm-sidebar-lbl">Condition *</label>
            <select name="condition" class="vm-input" required>
              <option value="" disabled <?= empty($data['condition'])?'selected':'' ?>>Select…</option>
              <?php foreach($conditions as $c): ?><option value="<?= $c ?>" <?= ($data['condition']??'')===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="vm-form-group"><label class="vm-sidebar-lbl">Asking Price ($) *</label>
            <div style="display:flex;background:var(--bg3);border:1px solid var(--border2);border-radius:var(--radius);overflow:hidden;transition:border-color .2s;">
              <span style="padding:9px 12px;color:var(--text3);font-family:var(--mono);">$</span>
              <input type="number" name="price" style="flex:1;background:transparent;border:none;outline:none;color:var(--text);padding:9px 12px 9px 0;font-family:var(--sans);font-size:.875rem;" placeholder="0.00" step="0.01" min="0.01" value="<?= htmlspecialchars($data['price']??'') ?>" required>
            </div>
          </div>
          <div class="vm-form-group"><label class="vm-sidebar-lbl">Quantity *</label><input type="number" name="stock" class="vm-input" min="1" max="99" value="<?= htmlspecialchars($data['stock']??'1') ?>" required></div>
        </div>
        <div class="vm-form-group"><label class="vm-sidebar-lbl">Photo URL <span style="color:var(--text3);font-weight:400;">(optional)</span></label>
          <input type="url" name="image_url" class="vm-input" placeholder="https://example.com/your-photo.jpg" value="<?= htmlspecialchars($data['image_url']??'') ?>">
          <div style="font-size:.75rem;color:var(--text3);margin-top:6px;">Upload to <a href="https://imgur.com" target="_blank">Imgur</a> and paste the direct image link here.</div>
        </div>
        <button type="submit" class="btn-volt" style="width:100%;justify-content:center;padding:12px;margin-top:4px;"><i class="bi bi-bag-plus"></i> Publish Listing</button>
      </form>
    </div>

    <!-- Tips -->
    <div style="background:rgba(0,229,160,.04);border:1px solid rgba(0,229,160,.15);border-radius:var(--radius);padding:16px;margin-top:16px;">
      <div style="font-weight:700;font-size:.85rem;color:var(--accent);margin-bottom:10px;"><i class="bi bi-lightbulb me-1"></i>Tips for a fast sale</div>
      <ul style="list-style:none;padding:0;margin:0;font-size:.82rem;color:var(--text2);">
        <li style="margin-bottom:6px;">📸 Real photos get 3× more views than no image</li>
        <li style="margin-bottom:6px;">📝 Include battery health %, serial status, and what's in the box</li>
        <li style="margin-bottom:6px;">💰 Price 10–20% below retail to sell fast</li>
        <li>✅ Be upfront about any scratches or faults</li>
      </ul>
    </div>
  </div>

  <!-- My Listings -->
  <div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
      <h2 style="font-size:1.1rem;margin:0;display:flex;align-items:center;gap:8px;"><span style="width:3px;height:18px;background:var(--accent);display:inline-block;border-radius:2px;"></span>My Listings</h2>
      <span style="font-size:.78rem;color:var(--text3);font-family:var(--mono);"><?= count($myListings) ?> total</span>
    </div>
    <?php if(empty($myListings)): ?>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:32px;text-align:center;color:var(--text3);">
      <i class="bi bi-tags" style="font-size:2rem;display:block;margin-bottom:12px;opacity:.3;"></i>
      <div style="font-size:.875rem;">No listings yet. Post your first device!</div>
    </div>
    <?php else: ?>
    <?php foreach($myListings as $p): ?>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:12px;margin-bottom:8px;display:flex;gap:12px;align-items:center;">
      <img src="<?= htmlspecialchars($p['image_url']?:'https://via.placeholder.com/56/13151e/00e5a0?text=+') ?>"
        style="width:56px;height:56px;object-fit:cover;border-radius:var(--radius);background:var(--bg3);flex-shrink:0;" alt="">
      <div style="flex:1;min-width:0;">
        <div style="font-size:.85rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($p['name']) ?></div>
        <div style="font-size:.75rem;font-family:var(--mono);margin-top:3px;">
          <span style="color:var(--accent);">$<?= number_format($p['price'],2) ?></span>
          <span style="color:var(--text3);margin:0 6px;">·</span>
          <span class="<?= $p['stock']>0?'':''; ?>" style="color:<?= $p['stock']>0?'var(--text2)':'var(--orange)'; ?>">
            <?= $p['stock']>0?$p['stock'].' in stock':'Out of stock' ?>
          </span>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:5px;flex-shrink:0;">
        <a href="<?= BASE_URL ?>/product.php?id=<?= $p['id'] ?>" style="background:var(--bg3);border:1px solid var(--border2);color:var(--text2);padding:4px 10px;border-radius:var(--radius);font-size:.75rem;text-decoration:none;text-align:center;transition:color .2s;" title="View">
          <i class="bi bi-eye"></i>
        </a>
        <a href="<?= BASE_URL ?>/edit_listing.php?id=<?= $p['id'] ?>" style="background:rgba(61,139,255,.08);border:1px solid rgba(61,139,255,.2);color:var(--blue);padding:4px 10px;border-radius:var(--radius);font-size:.75rem;text-decoration:none;text-align:center;" title="Edit">
          <i class="bi bi-pencil"></i>
        </a>
        <a href="<?= BASE_URL ?>/delete_listing.php?id=<?= $p['id'] ?>" style="background:rgba(255,95,46,.08);border:1px solid rgba(255,95,46,.2);color:var(--orange);padding:4px 10px;border-radius:var(--radius);font-size:.75rem;text-decoration:none;text-align:center;"
          data-confirm="Delete this listing? This cannot be undone." title="Delete">
          <i class="bi bi-trash"></i>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
