<?php
$pageTitle = 'Cart';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=(int)($_POST['product_id']??0);
  if($_POST['action']==='remove'){
    if(isLoggedIn()) $pdo->prepare("DELETE FROM cart WHERE user_id=? AND product_id=?")->execute([$_SESSION['user_id'],$_POST['product_id']]);
    else unset($_SESSION['cart'][$_POST['product_id']]);
  } elseif($_POST['action']==='update'){
    $qty=max(1,(int)($_POST['quantity']??1));
    if(isLoggedIn()) $pdo->prepare("UPDATE cart SET quantity=? WHERE user_id=? AND product_id=?")->execute([$qty,$_SESSION['user_id'],$_POST['product_id']]);
    else $_SESSION['cart'][$_POST['product_id']]=$qty;
  }
  header("Location: ".BASE_URL."/cart.php"); exit;
}

$items=[];
if(isLoggedIn()){
  $s=$pdo->prepare("SELECT c.quantity,p.* FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=?");
  $s->execute([$_SESSION['user_id']]); $items=$s->fetchAll();
} elseif(!empty($_SESSION['cart'])){
  $ids=array_keys($_SESSION['cart']); $in=implode(',',array_fill(0,count($ids),'?'));
  $s=$pdo->prepare("SELECT * FROM products WHERE id IN ($in)"); $s->execute($ids); $rows=$s->fetchAll();
  foreach($rows as $r){ $r['quantity']=$_SESSION['cart'][$r['id']]; $items[]=$r; }
}
$total=array_sum(array_map(fn($i)=>$i['price']*$i['quantity'],$items));
require_once __DIR__ . '/includes/header.php';
?>
<h1 style="font-size:1.6rem;margin-bottom:24px;">Shopping Cart</h1>
<?php if(empty($items)): ?>
<div class="vm-empty"><i class="bi bi-bag-x"></i><div>Your cart is empty</div><a href="<?= BASE_URL ?>/products.php" class="btn-volt" style="margin-top:20px;display:inline-flex;">Browse Electronics</a></div>
<?php else: ?>
<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">
  <div>
    <?php foreach($items as $item): ?>
    <div class="vm-cart-item">
      <img src="<?= htmlspecialchars($item['image_url']?:'https://via.placeholder.com/68/13151e/00e5a0?text=+') ?>" class="vm-cart-img" alt="">
      <div style="flex:1;">
        <a href="<?= BASE_URL ?>/product.php?id=<?= $item['id'] ?>" style="color:var(--text);font-weight:600;font-size:.9rem;"><?= htmlspecialchars($item['name']) ?></a>
        <div style="font-size:.78rem;color:var(--text3);font-family:var(--mono);margin-top:3px;"><?= htmlspecialchars($item['category']) ?> · <?= htmlspecialchars($item['condition']) ?></div>
        <div style="color:var(--accent);font-family:var(--mono);font-weight:700;margin-top:6px;">$<?= number_format($item['price'],2) ?></div>
      </div>
      <form method="POST" style="display:flex;align-items:center;">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
        <div class="vm-qty">
          <button type="button" data-qty="dn" data-target="#qty<?= $item['id'] ?>">−</button>
          <input type="number" id="qty<?= $item['id'] ?>" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" onchange="this.form.submit()">
          <button type="button" data-qty="up" data-target="#qty<?= $item['id'] ?>">+</button>
        </div>
      </form>
      <div style="font-family:var(--mono);font-weight:700;color:#fff;min-width:70px;text-align:right;">$<?= number_format($item['price']*$item['quantity'],2) ?></div>
      <form method="POST"><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= $item['id'] ?>">
        <button type="submit" class="btn-ghost" style="color:var(--orange);" data-confirm="Remove this item?"><i class="bi bi-trash"></i></button>
      </form>
    </div>
    <?php endforeach; ?>
    <a href="<?= BASE_URL ?>/products.php" class="btn-outline btn-sm" style="margin-top:8px;display:inline-flex;"><i class="bi bi-arrow-left"></i> Continue Shopping</a>
  </div>
  <div class="vm-order-panel">
    <h5>Order Summary</h5>
    <?php foreach($items as $item): ?>
    <div style="display:flex;justify-content:space-between;font-size:.82rem;color:var(--text2);margin-bottom:8px;">
      <span><?= htmlspecialchars(substr($item['name'],0,26)) ?>… ×<?= $item['quantity'] ?></span>
      <span>$<?= number_format($item['price']*$item['quantity'],2) ?></span>
    </div>
    <?php endforeach; ?>
    <hr class="vm-divider">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
      <span style="font-weight:700;font-size:1rem;">Total</span>
      <span class="vm-price" style="font-size:1.3rem;">$<?= number_format($total,2) ?></span>
    </div>
    <?php if(isLoggedIn()): ?>
      <a href="<?= BASE_URL ?>/checkout.php" class="btn-volt" style="width:100%;justify-content:center;padding:12px;"><i class="bi bi-credit-card"></i> Checkout</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/login.php?redirect=<?= urlencode(BASE_URL.'/checkout.php') ?>" class="btn-volt" style="width:100%;justify-content:center;padding:12px;">Login to Checkout</a>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
