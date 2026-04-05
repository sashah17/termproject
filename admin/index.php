<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/db.php';

$stats=[
  'users'    => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
  'products' => $pdo->query("SELECT COUNT(*) FROM products WHERE stock>0")->fetchColumn(),
  'orders'   => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
  'revenue'  => $pdo->query("SELECT COALESCE(SUM(total_price),0) FROM orders WHERE status!='Cancelled'")->fetchColumn(),
];
$recentOrders=$pdo->query("SELECT o.*,u.name AS customer FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.order_date DESC LIMIT 8")->fetchAll();
$lowStock=$pdo->query("SELECT * FROM products WHERE stock<=1 ORDER BY stock ASC LIMIT 5")->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
  <h1 style="font-size:1.6rem;margin:0;">Admin Dashboard</h1>
  <div style="display:flex;gap:8px;">
    <a href="<?= BASE_URL ?>/admin/products.php?action=add" class="btn-volt btn-sm"><i class="bi bi-plus-lg"></i> Add Product</a>
    <a href="<?= BASE_URL ?>/index.php" class="btn-outline btn-sm"><i class="bi bi-house"></i> View Site</a>
  </div>
</div>

<!-- Stat Cards -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:32px;">
  <div class="vm-stat-card"><div class="vm-stat-icon ic-green"><i class="bi bi-tags"></i></div><div><div class="vm-stat-num"><?= number_format($stats['products']) ?></div><div class="vm-stat-lbl">Active Listings</div></div></div>
  <div class="vm-stat-card"><div class="vm-stat-icon ic-blue"><i class="bi bi-people"></i></div><div><div class="vm-stat-num"><?= number_format($stats['users']) ?></div><div class="vm-stat-lbl">Users</div></div></div>
  <div class="vm-stat-card"><div class="vm-stat-icon ic-orange"><i class="bi bi-bag"></i></div><div><div class="vm-stat-num"><?= number_format($stats['orders']) ?></div><div class="vm-stat-lbl">Orders</div></div></div>
  <div class="vm-stat-card"><div class="vm-stat-icon ic-purple"><i class="bi bi-currency-dollar"></i></div><div><div class="vm-stat-num">$<?= number_format($stats['revenue'],0) ?></div><div class="vm-stat-lbl">Revenue</div></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;">
  <!-- Recent Orders -->
  <div class="vm-panel">
    <div class="vm-panel-head">
      <h5>Recent Orders</h5>
      <a href="<?= BASE_URL ?>/admin/orders.php" class="btn-outline btn-sm">View All</a>
    </div>
    <table class="vm-table">
      <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach($recentOrders as $o):
          $sc=match($o['status']){'Processing'=>'s-processing','Shipped'=>'s-shipped','Delivered'=>'s-delivered','Cancelled'=>'s-cancelled',default=>'s-pending'};
        ?>
        <tr>
          <td style="font-family:var(--mono);color:var(--accent);">#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></td>
          <td><?= htmlspecialchars($o['customer']) ?></td>
          <td style="color:var(--text3);font-size:.78rem;"><?= date('M j, Y',strtotime($o['order_date'])) ?></td>
          <td style="font-family:var(--mono);font-weight:700;color:#fff;">$<?= number_format($o['total_price'],2) ?></td>
          <td><span class="vm-status <?= $sc ?>"><?= htmlspecialchars($o['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Low Stock -->
  <div class="vm-panel">
    <div class="vm-panel-head"><h5>⚡ Low Stock</h5></div>
    <div style="padding:12px 16px;">
      <?php if(empty($lowStock)): ?><div style="color:var(--text3);font-size:.85rem;">All products well stocked.</div><?php endif; ?>
      <?php foreach($lowStock as $p): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);">
        <div style="font-size:.82rem;font-weight:600;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars(substr($p['name'],0,28)) ?>…</div>
        <span class="vm-status <?= $p['stock']==0?'s-cancelled':'s-pending' ?>" style="margin-left:8px;"><?= $p['stock'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Quick links -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:20px;">
  <?php foreach([
    [BASE_URL.'/admin/products.php','bi-tags','ic-green','Manage Products','Add, edit, delete listings'],
    [BASE_URL.'/admin/orders.php','bi-bag-check','ic-blue','Manage Orders','Update statuses'],
    [BASE_URL.'/admin/users.php','bi-people','ic-purple','Manage Users','View accounts & roles'],
  ] as [$url,$icon,$ic,$title,$sub]): ?>
  <a href="<?= $url ?>" style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:22px;text-decoration:none;display:flex;align-items:center;gap:14px;transition:border-color .2s;" onmouseover="this.style.borderColor='rgba(0,229,160,.3)'" onmouseout="this.style.borderColor='var(--border)'">
    <div class="vm-stat-icon <?= $ic ?>"><i class="bi <?= $icon ?>"></i></div>
    <div><div style="font-weight:700;color:#fff;margin-bottom:3px;"><?= $title ?></div><div style="font-size:.78rem;color:var(--text3);"><?= $sub ?></div></div>
  </a>
  <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
