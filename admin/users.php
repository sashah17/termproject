<?php
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../includes/db.php';

if(isset($_GET['toggle_admin'])&&(int)$_GET['toggle_admin']!==$_SESSION['user_id']){
  $pdo->prepare("UPDATE users SET is_admin=1-is_admin WHERE id=?")->execute([(int)$_GET['toggle_admin']]);
  header("Location: ".BASE_URL."/admin/users.php"); exit;
}

$users=$pdo->query("SELECT u.*,
  (SELECT COUNT(*) FROM orders WHERE user_id=u.id) AS order_count,
  (SELECT COALESCE(SUM(total_price),0) FROM orders WHERE user_id=u.id) AS total_spent
  FROM users u ORDER BY u.created_at DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
  <h1 style="font-size:1.5rem;margin:0;">Manage Users</h1>
  <a href="<?= BASE_URL ?>/admin/index.php" class="btn-outline btn-sm">← Dashboard</a>
</div>
<div class="vm-panel">
  <div class="vm-panel-head"><h5>All Users (<?= count($users) ?>)</h5></div>
  <div style="overflow-x:auto;">
    <table class="vm-table">
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Registered</th><th>Orders</th><th>Spent</th><th>Role</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach($users as $u): ?>
        <tr>
          <td style="font-family:var(--mono);color:var(--text3);font-size:.75rem;"><?= $u['id'] ?></td>
          <td style="font-weight:600;"><?= htmlspecialchars($u['name']) ?></td>
          <td style="font-size:.82rem;color:var(--text2);"><?= htmlspecialchars($u['email']) ?></td>
          <td style="font-size:.78rem;color:var(--text3);font-family:var(--mono);"><?= date('M j, Y',strtotime($u['created_at'])) ?></td>
          <td style="font-family:var(--mono);"><?= $u['order_count'] ?></td>
          <td style="font-family:var(--mono);color:var(--accent);">$<?= number_format($u['total_spent'],2) ?></td>
          <td>
            <span class="vm-status <?= $u['is_admin']?'s-shipped':'s-pending' ?>">
              <?= $u['is_admin']?'Admin':'User' ?>
            </span>
          </td>
          <td>
            <?php if($u['id']!=$_SESSION['user_id']): ?>
            <a href="<?= BASE_URL ?>/admin/users.php?toggle_admin=<?= $u['id'] ?>"
              style="background:<?= $u['is_admin']?'rgba(255,95,46,.1)':'rgba(0,229,160,.1)' ?>;border:1px solid <?= $u['is_admin']?'rgba(255,95,46,.25)':'rgba(0,229,160,.25)' ?>;color:<?= $u['is_admin']?'var(--orange)':'var(--accent)' ?>;padding:5px 12px;border-radius:var(--radius);font-size:.78rem;text-decoration:none;font-family:var(--mono);"
              data-confirm="Change role for <?= htmlspecialchars($u['name']) ?>?">
              <?= $u['is_admin']?'Revoke':'Make Admin' ?>
            </a>
            <?php else: ?><span style="font-size:.78rem;color:var(--text3);font-family:var(--mono);">You</span><?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
