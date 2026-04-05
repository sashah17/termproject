<?php
$pageTitle = 'My Orders';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';

$stmt=$pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY order_date DESC");
$stmt->execute([$_SESSION['user_id']]); $orders=$stmt->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
  <h1 style="font-size:1.6rem;margin:0;">My Orders</h1>
  <a href="<?= BASE_URL ?>/products.php" class="btn-outline btn-sm"><i class="bi bi-grid-3x3-gap"></i> Browse More</a>
</div>
<?php if(empty($orders)): ?>
<div class="vm-empty"><i class="bi bi-bag-x"></i><div>No orders yet</div><a href="<?= BASE_URL ?>/products.php" class="btn-volt" style="margin-top:20px;display:inline-flex;"><i class="bi bi-lightning-charge"></i> Start Shopping</a></div>
<?php else: ?>
<?php foreach($orders as $order):
  $items=$pdo->prepare("SELECT oi.*,p.name,p.image_url FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
  $items->execute([$order['id']]); $items=$items->fetchAll();
  $sc=match($order['status']){'Pending'=>'s-pending','Processing'=>'s-processing','Shipped'=>'s-shipped','Delivered'=>'s-delivered','Cancelled'=>'s-cancelled',default=>'s-pending'};
?>
<div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);margin-bottom:16px;overflow:hidden;">
  <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;background:var(--bg3);border-bottom:1px solid var(--border);">
    <div style="display:flex;align-items:center;gap:16px;">
      <div style="font-family:var(--mono);font-size:.82rem;color:var(--text2);">ORDER <span style="color:#fff;">#<?= str_pad($order['id'],5,'0',STR_PAD_LEFT) ?></span></div>
      <div style="font-size:.78rem;color:var(--text3);"><?= date('M j, Y · g:i a',strtotime($order['order_date'])) ?></div>
    </div>
    <div style="display:flex;align-items:center;gap:12px;">
      <span class="vm-status <?= $sc ?>"><?= htmlspecialchars($order['status']) ?></span>
      <span style="font-family:var(--mono);font-weight:700;color:var(--accent);">$<?= number_format($order['total_price'],2) ?></span>
    </div>
  </div>
  <div style="padding:16px 20px;">
    <?php foreach($items as $item): ?>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
      <img src="<?= htmlspecialchars($item['image_url']?:'https://via.placeholder.com/48/13151e/00e5a0?text=+') ?>"
        style="width:48px;height:48px;object-fit:cover;border-radius:var(--radius);background:var(--bg3);flex-shrink:0;" alt="">
      <div style="flex:1;">
        <div style="font-size:.875rem;font-weight:600;color:var(--text);"><?= htmlspecialchars($item['name']) ?></div>
        <div style="font-size:.78rem;color:var(--text3);font-family:var(--mono);">Qty: <?= $item['quantity'] ?> · $<?= number_format($item['price'],2) ?> each</div>
      </div>
      <div style="font-family:var(--mono);font-weight:700;font-size:.875rem;color:#fff;">$<?= number_format($item['price']*$item['quantity'],2) ?></div>
    </div>
    <?php endforeach; ?>
    <?php if($order['ship_address']): ?>
    <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);font-size:.8rem;color:var(--text3);">
      <i class="bi bi-truck me-1"></i> <strong style="color:var(--text2);">Ship to:</strong>
      <?= htmlspecialchars($order['ship_name']) ?> · <?= htmlspecialchars($order['ship_address']) ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
