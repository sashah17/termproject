<?php
$pageTitle = 'Edit Listing';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';

$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if(!$id){ header("Location: ".BASE_URL."/sell.php"); exit; }
$stmt=$pdo->prepare("SELECT * FROM products WHERE id=?"); $stmt->execute([$id]); $product=$stmt->fetch();
if(!$product||($product['seller_id']!=$_SESSION['user_id']&&!isAdmin())){ header("Location: ".BASE_URL."/sell.php"); exit; }

$categories=['Phones & Tablets','Laptops & PCs','Audio','Gaming','Cameras','Wearables','Components & Parts'];
$conditions=['Like New','Good','Fair','For Parts'];
$errors=[];

if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']??''); $description=trim($_POST['description']??'');
  $price=$_POST['price']??''; $image_url=trim($_POST['image_url']??'');
  $category=$_POST['category']??''; $condition=$_POST['condition']??''; $stock=$_POST['stock']??'1';
  if(strlen($name)<2) $errors[]='Title must be at least 2 characters.';
  if(!is_numeric($price)||(float)$price<=0) $errors[]='Price must be greater than $0.';
  if(!in_array($category,$categories)) $errors[]='Select a valid category.';
  if(!in_array($condition,$conditions)) $errors[]='Select a valid condition.';
  if(!is_numeric($stock)||(int)$stock<0) $errors[]='Quantity cannot be negative.';
  if(!empty($image_url)&&!filter_var($image_url,FILTER_VALIDATE_URL)) $errors[]='Image URL must be a valid URL.';
  if(empty($errors)){
    $s=$pdo->prepare("UPDATE products SET name=?,description=?,price=?,image_url=?,category=?,`condition`=?,stock=? WHERE id=?");
    $s->execute([$name,$description,(float)$price,$image_url,$category,$condition,(int)$stock,$id]);
    header("Location: ".BASE_URL."/sell.php?updated=1"); exit;
  }
  $product=array_merge($product,compact('name','description','price','image_url','category','condition','stock'));
}
require_once __DIR__ . '/includes/header.php';
?>
<div style="max-width:640px;margin:0 auto;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <h1 style="font-size:1.5rem;margin:0;">Edit Listing</h1>
    <a href="<?= BASE_URL ?>/sell.php" class="btn-outline btn-sm"><i class="bi bi-arrow-left"></i> My Listings</a>
  </div>
  <?php if($errors): ?><div class="vm-alert a-danger"><div><i class="bi bi-exclamation-circle"></i></div><ul style="margin:0 0 0 4px;padding:0 0 0 14px;"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;">
    <form method="POST" class="vm-form" novalidate>
      <div class="vm-form-group"><label class="vm-sidebar-lbl">Device Title *</label><input type="text" name="name" class="vm-input" required minlength="2" value="<?= htmlspecialchars($product['name']) ?>"></div>
      <div class="vm-form-group"><label class="vm-sidebar-lbl">Description *</label><textarea name="description" class="vm-input" style="min-height:110px;" required><?= htmlspecialchars($product['description']) ?></textarea></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="vm-form-group"><label class="vm-sidebar-lbl">Category *</label>
          <select name="category" class="vm-input" required><?php foreach($categories as $c): ?><option value="<?= $c ?>" <?= $product['category']===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select>
        </div>
        <div class="vm-form-group"><label class="vm-sidebar-lbl">Condition *</label>
          <select name="condition" class="vm-input" required><?php foreach($conditions as $c): ?><option value="<?= $c ?>" <?= $product['condition']===$c?'selected':'' ?>><?= $c ?></option><?php endforeach; ?></select>
        </div>
        <div class="vm-form-group"><label class="vm-sidebar-lbl">Price ($) *</label>
          <div style="display:flex;background:var(--bg3);border:1px solid var(--border2);border-radius:var(--radius);overflow:hidden;">
            <span style="padding:9px 12px;color:var(--text3);font-family:var(--mono);">$</span>
            <input type="number" name="price" style="flex:1;background:transparent;border:none;outline:none;color:var(--text);padding:9px 12px 9px 0;font-family:var(--sans);font-size:.875rem;" step="0.01" min="0.01" required value="<?= htmlspecialchars($product['price']) ?>">
          </div>
        </div>
        <div class="vm-form-group"><label class="vm-sidebar-lbl">Quantity *</label><input type="number" name="stock" class="vm-input" min="0" required value="<?= htmlspecialchars($product['stock']) ?>"></div>
      </div>
      <div class="vm-form-group"><label class="vm-sidebar-lbl">Photo URL</label><input type="url" name="image_url" class="vm-input" value="<?= htmlspecialchars($product['image_url']??'') ?>"></div>
      <div style="display:flex;gap:10px;margin-top:4px;">
        <button type="submit" class="btn-volt" style="flex:1;justify-content:center;padding:11px;"><i class="bi bi-save"></i> Save Changes</button>
        <a href="<?= BASE_URL ?>/sell.php" class="btn-outline" style="padding:10px 18px;">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
