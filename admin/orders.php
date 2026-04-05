<?php
$pageTitle = 'Manage Orders';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/db.php';

$statuses=['Pending','Processing','Shipped','Delivered','Cancelled'];

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['order_id'],$_POST['status'])){
  $oid=(int)$_POST['order_id']; $status=$_POST['status'];
  if(in_array($status,$statuses)) $pdo->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$status,$oid]);
  header("Location: ".BASE_URL."/admin/orders.php?msg=updated"); exit;
}

$filterStatus=$_GET['status']??'';
$sql="SELECT o.*,u.name AS customer,u.email FROM orders o JOIN users u ON o.user_id=u.id";
$params=[];
if($filterStatus&&in_array($filterStatus,$statuses)){ $sql.=" WHERE o.status=?"; $params[]=$filterStatus; }
$sql.=" ORDER BY o.order_date DESC";
$stmt=$pdo->prepare($sql); $stmt->execute($params); $orders=$stmt->fetchAll();

$flashMsg=($_GET['msg']??'')==='updated'?'Order updated!':'';
require_once __DIR__ . '/../includes/header.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
  <h1 style="font-size:1.5rem;margin:0;">Manage Orders</h1>
  <a href="<?= BASE_URL ?>/admin/index.php" class="btn-outline btn-sm">← Dashboard</a>
</div>
<?php if($flashMsg): ?><div class="vm-alert a-success" data-dismiss><i class="bi bi-check-circle"></i> <?= htmlspecialchars($flashMsg) ?></div><?php endif; ?>

<!-- Status Tabs -->
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px;">
  <a href="<?= BASE_URL ?>/admin/orders.php" style="padding:6px 14px;border-radius:var(--radius);font-size:.8rem;font-family:var(--mono);text-decoration:none;border:1px solid <?= !$filterStatus?'var(--accent)':'var(--border2)' ?>;color:<?= !$filterStatus?'var(--accent)':'var(--text2)' ?>;background:<?= !$filterStatus?'rgba(0,229,160,.08)':'transparent' ?>;">All</a>
  <?php foreach($statuses as $s):
    $sc=match($s){'Processing'=>'var(--blue)','Shipped'=>'#a78bfa','Delivered'=>'var(--accent)','Cancelled'=>'var(--orange)',default=>'#fbbf24'};
  ?>
  <a href="<?= BASE_URL ?>/admin/orders.php?status=<?= urlencode($s) ?>" style="padding:6px 14px;border-radius:var(--radius);font-size:.8rem;font-family:var(--mono);text-decoration:none;border:1px solid <?= $filterStatus===$s?"$sc":'var(--border2)' ?>;color:<?= $filterStatus===$s?"$sc":'var(--text2)' ?>;background:<?= $filterStatus===$s?'rgba(255,255,255,.04)':'transparent' ?>;"><?= $s ?></a>
  <?php endforeach; ?>
</div>

<div class="vm-panel">
  <div style="overflow-x:auto;">
    <table class="vm-table">
      <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th>Update</th></tr></thead>
      <tbody>
        <?php foreach($orders as $o):
          $items=$pdo->prepare("SELECT oi.quantity,oi.price,p.name FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
          $items->execute([$o['id']]); $items=$items->fetchAll();
          $sc=match($o['status']){'Processing'=>'s-processing','Shipped'=>'s-shipped','Delivered'=>'s-delivered','Cancelled'=>'s-cancelled',default=>'s-pending'};
        ?>
        <tr>
          <td style="font-family:var(--mono);color:var(--accent);font-weight:700;">#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></td>
          <td><div style="font-weight:600;font-size:.875rem;"><?= htmlspecialchars($o['customer']) ?></div><div style="font-size:.75rem;color:var(--text3);"><?= htmlspecialchars($o['email']) ?></div><?php if($o['ship_address']): ?><div style="font-size:.72rem;color:var(--text3);max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($o['ship_address']) ?></div><?php endif; ?></td>
          <td style="font-size:.78rem;color:var(--text3);font-family:var(--mono);"><?= date('M j, Y',strtotime($o['order_date'])) ?></td>
          <td style="max-width:160px;"><?php foreach($items as $item): ?><div style="font-size:.78rem;color:var(--text2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars(substr($item['name'],0,22)) ?>… ×<?= $item['quantity'] ?></div><?php endforeach; ?></td>
          <td style="font-family:var(--mono);font-weight:700;color:var(--accent);">$<?= number_format($o['total_price'],2) ?></td>
          <td><span class="vm-status <?= $sc ?>"><?= htmlspecialchars($o['status']) ?></span></td>
          <td>
            <form method="POST" style="display:flex;gap:6px;align-items:center;">
              <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
              <select name="status" class="vm-input" style="width:130px;padding:6px 10px;font-size:.8rem;">
                <?php foreach($statuses as $s): ?><option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
              </select>
              <button type="submit" class="btn-volt btn-sm"><i class="bi bi-check-lg"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($orders)): ?><tr><td colspan="7" style="text-align:center;color:var(--text3);padding:40px;">No orders found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
