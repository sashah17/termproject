<?php
$pageTitle = 'Manage Products';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/db.php';

$action=$_GET['action']??'list';
$editId=isset($_GET['id'])?(int)$_GET['id']:null;
$categories=['Phones & Tablets','Laptops & PCs','Audio','Gaming','Cameras','Wearables','Components & Parts'];
$conditions=['Like New','Good','Fair','For Parts'];
$errors=[];

if($action==='delete'&&$editId){ $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$editId]); header("Location: ".BASE_URL."/admin/products.php?msg=deleted"); exit; }

if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']??''); $description=trim($_POST['description']??'');
  $price=(float)($_POST['price']??0); $image_url=trim($_POST['image_url']??'');
  $category=$_POST['category']??''; $condition=$_POST['condition']??'';
  $stock=(int)($_POST['stock']??0); $seller_id=(int)($_POST['seller_id']??$_SESSION['user_id']);
  if(strlen($name)<2) $errors[]='Product name required.';
  if($price<=0) $errors[]='Price must be > 0.';
  if(!in_array($category,$categories)) $errors[]='Invalid category.';
  if(!in_array($condition,$conditions)) $errors[]='Invalid condition.';
  if(empty($errors)){
    $postId=(int)($_POST['product_id']??0);
    if($postId){ $s=$pdo->prepare("UPDATE products SET name=?,description=?,price=?,image_url=?,category=?,`condition`=?,stock=?,seller_id=? WHERE id=?"); $s->execute([$name,$description,$price,$image_url,$category,$condition,$stock,$seller_id,$postId]); header("Location: ".BASE_URL."/admin/products.php?msg=updated"); }
    else { $s=$pdo->prepare("INSERT INTO products(name,description,price,image_url,category,`condition`,stock,seller_id) VALUES(?,?,?,?,?,?,?,?)"); $s->execute([$name,$description,$price,$image_url,$category,$condition,$stock,$seller_id]); header("Location: ".BASE_URL."/admin/products.php?msg=added"); }
    exit;
  }
}

$editProduct=null;
if(($action==='edit'||!empty($_POST))&&$editId){ $s=$pdo->prepare("SELECT * FROM products WHERE id=?"); $s->execute([$editId]); $editProduct=$s->fetch(); }
$products=$pdo->query("SELECT p.*,u.name AS seller_name FROM products p JOIN users u ON p.seller_id=u.id ORDER BY p.created_at DESC")->fetchAll();
$sellers=$pdo->query("SELECT id,name FROM users ORDER BY name")->fetchAll();
$msgs=['added'=>'Product added!','updated'=>'Product updated!','deleted'=>'Product deleted.'];
$flashMsg=$msgs[$_GET['msg']??'']??'';
require_once __DIR__ . '/../includes/header.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
  <h1 style="font-size:1.5rem;margin:0;">Manage Products</h1>
  <div style="display:flex;gap:8px;">
    <a href="<?= BASE_URL ?>/admin/products.php?action=add" class="btn-volt btn-sm"><i class="bi bi-plus-lg"></i> Add Product</a>
    <a href="<?= BASE_URL ?>/admin/index.php" class="btn-outline btn-sm">← Dashboard</a>
  </div>
</div>
<?php if($flashMsg): ?><div class="vm-alert a-success" data-dismiss><i class="bi bi-check-circle"></i> <?= htmlspecialchars($flashMsg) ?></div><?php endif; ?>

<?php if($action==='add'||$action==='edit'): ?>
<div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;margin-bottom:28px;">
  <h3 style="font-size:1rem;margin-bottom:20px;font-family:var(--mono);letter-spacing:.06em;text-transform:uppercase;color:var(--text2);"><?= $action==='edit'?'Edit Product':'Add New Product' ?></h3>
  <?php if($errors): ?><div class="vm-alert a-danger"><ul style="margin:0;padding-left:16px;"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form method="POST" class="vm-form" novalidate>
    <?php if($editProduct): ?><input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>"><?php endif; ?>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:14px;margin-bottom:14px;">
      <div style="grid-column:1/3;"><label class="vm-sidebar-lbl">Product Name *</label><input type="text" name="name" class="vm-input" required minlength="2" value="<?= htmlspecialchars($editProduct['name']??$_POST['name']??'') ?>"></div>
      <div><label class="vm-sidebar-lbl">Price ($) *</label><input type="number" name="price" class="vm-input" step="0.01" min="0.01" required value="<?= htmlspecialchars($editProduct['price']??$_POST['price']??'') ?>"></div>
      <div><label class="vm-sidebar-lbl">Stock *</label><input type="number" name="stock" class="vm-input" min="0" required value="<?= htmlspecialchars($editProduct['stock']??$_POST['stock']??'1') ?>"></div>
      <div><label class="vm-sidebar-lbl">Category *</label><select name="category" class="vm-input" required><?php foreach($categories as $c): ?><option value="<?= $c ?>" <?= ($editProduct['category']??'')===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select></div>
      <div><label class="vm-sidebar-lbl">Condition *</label><select name="condition" class="vm-input" required><?php foreach($conditions as $c): ?><option value="<?= $c ?>" <?= ($editProduct['condition']??'')===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select></div>
      <div><label class="vm-sidebar-lbl">Seller</label><select name="seller_id" class="vm-input"><?php foreach($sellers as $s): ?><option value="<?= $s['id'] ?>" <?= ($editProduct['seller_id']??$_SESSION['user_id'])==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?></select></div>
      <div style="grid-column:4;"></div>
      <div style="grid-column:1/-1;"><label class="vm-sidebar-lbl">Image URL</label><input type="url" name="image_url" class="vm-input" placeholder="https://…" value="<?= htmlspecialchars($editProduct['image_url']??$_POST['image_url']??'') ?>"></div>
      <div style="grid-column:1/-1;"><label class="vm-sidebar-lbl">Description</label><textarea name="description" class="vm-input" style="min-height:80px;"><?= htmlspecialchars($editProduct['description']??$_POST['description']??'') ?></textarea></div>
    </div>
    <div style="display:flex;gap:10px;">
      <button type="submit" class="btn-volt"><i class="bi bi-save"></i> <?= $action==='edit'?'Save Changes':'Add Product' ?></button>
      <a href="<?= BASE_URL ?>/admin/products.php" class="btn-outline">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="vm-panel">
  <div class="vm-panel-head"><h5>All Products (<?= count($products) ?>)</h5></div>
  <div style="overflow-x:auto;">
    <table class="vm-table">
      <thead><tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Condition</th><th>Price</th><th>Stock</th><th>Seller</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($products as $p): ?>
        <tr>
          <td style="font-family:var(--mono);color:var(--text3);font-size:.75rem;"><?= $p['id'] ?></td>
          <td><img src="<?= htmlspecialchars($p['image_url']?:'https://via.placeholder.com/44/13151e/00e5a0?text=+') ?>" style="width:44px;height:44px;object-fit:cover;border-radius:var(--radius);"></td>
          <td><a href="<?= BASE_URL ?>/product.php?id=<?= $p['id'] ?>" target="_blank" style="color:var(--text);font-weight:600;font-size:.875rem;"><?= htmlspecialchars(substr($p['name'],0,36)) ?>…</a></td>
          <td><span class="vm-badge vm-badge-blue"><?= htmlspecialchars($p['category']) ?></span></td>
          <td style="font-size:.8rem;color:var(--text2);"><?= htmlspecialchars($p['condition']) ?></td>
          <td style="font-family:var(--mono);font-weight:700;color:var(--accent);">$<?= number_format($p['price'],2) ?></td>
          <td><span class="vm-status <?= $p['stock']==0?'s-cancelled':($p['stock']<=1?'s-pending':'s-delivered') ?>"><?= $p['stock'] ?></span></td>
          <td style="font-size:.8rem;color:var(--text3);"><?= htmlspecialchars($p['seller_name']) ?></td>
          <td>
            <div style="display:flex;gap:6px;">
              <a href="<?= BASE_URL ?>/admin/products.php?action=edit&id=<?= $p['id'] ?>" style="background:rgba(61,139,255,.1);border:1px solid rgba(61,139,255,.2);color:var(--blue);padding:5px 10px;border-radius:var(--radius);font-size:.78rem;text-decoration:none;"><i class="bi bi-pencil"></i></a>
              <a href="<?= BASE_URL ?>/admin/products.php?action=delete&id=<?= $p['id'] ?>" style="background:rgba(255,95,46,.1);border:1px solid rgba(255,95,46,.2);color:var(--orange);padding:5px 10px;border-radius:var(--radius);font-size:.78rem;text-decoration:none;" data-confirm="Delete this product?"><i class="bi bi-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
