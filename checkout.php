<?php
$pageTitle = 'Checkout';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';

$stmt=$pdo->prepare("SELECT c.quantity,p.* FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=?");
$stmt->execute([$_SESSION['user_id']]); $items=$stmt->fetchAll();
if(empty($items)){ header("Location: ".BASE_URL."/cart.php"); exit; }
$total=array_sum(array_map(fn($i)=>$i['price']*$i['quantity'],$items));

$errors=[]; $success=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['ship_name']??''); $email=trim($_POST['ship_email']??''); $address=trim($_POST['ship_address']??'');
  if(strlen($name)<2) $errors[]='Full name is required.';
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='A valid email address is required.';
  if(strlen($address)<5) $errors[]='Shipping address is required.';
  foreach($items as $item){ if($item['stock']<$item['quantity']) $errors[]="\"".htmlspecialchars($item['name'])."\" only has {$item['stock']} in stock."; }
  if(empty($errors)){
    try {
      $pdo->beginTransaction();
      $s=$pdo->prepare("INSERT INTO orders(user_id,total_price,status,ship_name,ship_email,ship_address) VALUES(?,?,'Processing',?,?,?)");
      $s->execute([$_SESSION['user_id'],$total,$name,$email,$address]); $orderId=$pdo->lastInsertId();
      $si=$pdo->prepare("INSERT INTO order_items(order_id,product_id,quantity,price) VALUES(?,?,?,?)");
      $ss=$pdo->prepare("UPDATE products SET stock=stock-? WHERE id=?");
      foreach($items as $item){ $si->execute([$orderId,$item['id'],$item['quantity'],$item['price']]); $ss->execute([$item['quantity'],$item['id']]); }
      $pdo->prepare("DELETE FROM cart WHERE user_id=?")->execute([$_SESSION['user_id']]);
      $pdo->commit(); $success=true; $items=[];
    } catch(Exception $e){ $pdo->rollBack(); $errors[]='Error processing order. Please try again.'; }
  }
}
require_once __DIR__ . '/includes/header.php';
?>
<h1 style="font-size:1.6rem;margin-bottom:28px;">Checkout</h1>
<?php if($success): ?>
<div style="text-align:center;padding:80px 20px;">
  <div style="width:72px;height:72px;background:rgba(0,229,160,.1);border:2px solid var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:1.8rem;">✓</div>
  <h2 style="color:#fff;margin-bottom:10px;">Order Confirmed!</h2>
  <p style="color:var(--text2);max-width:360px;margin:0 auto 28px;">Your order is being processed. You'll receive a confirmation shortly.</p>
  <div style="display:flex;gap:12px;justify-content:center;">
    <a href="<?= BASE_URL ?>/orders.php" class="btn-volt"><i class="bi bi-bag-check"></i> View Orders</a>
    <a href="<?= BASE_URL ?>/products.php" class="btn-outline">Keep Shopping</a>
  </div>
</div>
<?php else: ?>
<?php if($errors): ?><div class="vm-alert a-danger"><div><i class="bi bi-exclamation-circle"></i></div><ul style="margin:0 0 0 4px;padding:0 0 0 14px;"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div style="display:grid;grid-template-columns:1fr 320px;gap:28px;align-items:start;">
  <div>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;margin-bottom:20px;">
      <h3 style="font-size:1rem;margin-bottom:20px;font-family:var(--mono);letter-spacing:.06em;text-transform:uppercase;color:var(--text2);">Shipping Info</h3>
      <form method="POST" id="checkoutForm" novalidate>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="vm-form-group" style="grid-column:1/-1;"><label class="vm-sidebar-lbl">Full Name *</label><input type="text" name="ship_name" class="vm-input" placeholder="Jane Smith" value="<?= htmlspecialchars($_SESSION['user_name']??'') ?>" required minlength="2"></div>
          <div class="vm-form-group" style="grid-column:1/-1;"><label class="vm-sidebar-lbl">Email *</label><input type="email" name="ship_email" class="vm-input" placeholder="you@example.com" required></div>
          <div class="vm-form-group" style="grid-column:1/-1;"><label class="vm-sidebar-lbl">Shipping Address *</label><textarea name="ship_address" class="vm-input" placeholder="123 Main St, City, Province, Postal Code" required minlength="5" style="min-height:80px;"></textarea></div>
        </div>
      </form>
    </div>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;">
      <h3 style="font-size:1rem;margin-bottom:16px;font-family:var(--mono);letter-spacing:.06em;text-transform:uppercase;color:var(--text2);">Payment <span style="color:var(--text3);font-size:.75rem;">(Demo Only)</span></h3>
      <div class="vm-alert a-info" style="margin-bottom:16px;"><i class="bi bi-info-circle"></i> This is a demo. No real payment is processed.</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" form="checkoutForm">
        <div class="vm-form-group" style="grid-column:1/-1;"><label class="vm-sidebar-lbl">Card Number</label><input type="text" class="vm-input" placeholder="4242 4242 4242 4242" maxlength="19" form="checkoutForm" required pattern="\d{4}[\s]?\d{4}[\s]?\d{4}[\s]?\d{4}" oninput="this.value=this.value.replace(/[^0-9\s]/g,'')"></div>
        <div class="vm-form-group"><label class="vm-sidebar-lbl">Expiry</label><input type="text" class="vm-input" placeholder="MM/YY" maxlength="5" form="checkoutForm" required pattern="(0[1-9]|1[0-2])\/\d{2}"></div>
        <div class="vm-form-group"><label class="vm-sidebar-lbl">CVV</label><input type="text" class="vm-input" placeholder="123" maxlength="4" form="checkoutForm" required pattern="\d{3,4}"></div>
      </div>
    </div>
  </div>
  <div class="vm-order-panel">
    <h5>Your Order</h5>
    <?php foreach($items as $item): ?>
    <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;">
      <img src="<?= htmlspecialchars($item['image_url']?:'https://via.placeholder.com/44/13151e/00e5a0?text=+') ?>" style="width:44px;height:44px;object-fit:cover;border-radius:var(--radius);background:var(--bg3);flex-shrink:0;" alt="">
      <div style="flex:1;"><div style="font-size:.82rem;font-weight:600;color:var(--text);"><?= htmlspecialchars(substr($item['name'],0,28)) ?>…</div><div style="font-size:.75rem;color:var(--text3);">Qty: <?= $item['quantity'] ?></div></div>
      <span style="font-family:var(--mono);font-size:.85rem;font-weight:700;color:#fff;">$<?= number_format($item['price']*$item['quantity'],2) ?></span>
    </div>
    <?php endforeach; ?>
    <hr class="vm-divider">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
      <span style="font-weight:700;">Total</span>
      <span class="vm-price" style="font-size:1.3rem;">$<?= number_format($total,2) ?></span>
    </div>
    <button type="submit" form="checkoutForm" class="btn-volt" style="width:100%;justify-content:center;padding:12px;">
      <i class="bi bi-bag-check"></i> Place Order — $<?= number_format($total,2) ?>
    </button>
  </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
