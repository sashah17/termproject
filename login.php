<?php
$pageTitle = 'Login';
require_once __DIR__ . '/includes/auth.php';
if(isLoggedIn()){ header("Location: ".BASE_URL."/index.php"); exit; }
require_once __DIR__ . '/includes/db.php';

$error = ''; $redirect = $_GET['redirect'] ?? BASE_URL.'/index.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = strtolower(trim($_POST['email']??'')); $password = $_POST['password']??'';
  if(empty($email)||empty($password)){ $error='Both fields are required.'; }
  elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){ $error='Enter a valid email address.'; }
  else {
    $stmt=$pdo->prepare("SELECT * FROM users WHERE email=? LIMIT 1"); $stmt->execute([$email]); $user=$stmt->fetch();
    if($user && password_verify($password,$user['password'])){
      session_regenerate_id(true);
      $_SESSION['user_id']=$user['id']; $_SESSION['user_name']=$user['name']; $_SESSION['is_admin']=$user['is_admin'];
      if(!empty($_SESSION['cart'])){ foreach($_SESSION['cart'] as $pid=>$qty){ $s=$pdo->prepare("INSERT INTO cart(user_id,product_id,quantity) VALUES(?,?,?) ON DUPLICATE KEY UPDATE quantity=quantity+VALUES(quantity)"); $s->execute([$user['id'],$pid,$qty]); } unset($_SESSION['cart']); }
      header("Location: ".($redirect ?: BASE_URL.'/index.php')); exit;
    } else { $error='Invalid email or password.'; }
  }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="vm-auth-outer">
  <div class="vm-auth-card">
    <div class="vm-auth-logo"><i class="bi bi-lightning-charge-fill"></i> VoltMarket</div>
    <h2 style="margin-bottom:4px;">Welcome back</h2>
    <p style="color:var(--text2);font-size:.875rem;margin-bottom:24px;">Sign in to your account</p>
    <?php if($error): ?><div class="vm-alert a-danger" data-dismiss><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST" class="vm-form" novalidate>
      <div class="vm-form-group">
        <label class="vm-sidebar-lbl">Email Address</label>
        <input type="email" name="email" class="vm-input" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email']??'') ?>" required autofocus>
      </div>
      <div class="vm-form-group">
        <label class="vm-sidebar-lbl">Password</label>
        <input type="password" name="password" id="password" class="vm-input" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-volt" style="width:100%;justify-content:center;margin-top:8px;padding:12px;">Login</button>
    </form>
    <hr style="border-color:var(--border);margin:24px 0;">
    <p style="text-align:center;font-size:.85rem;color:var(--text2);margin:0;">No account? <a href="<?= BASE_URL ?>/register.php" style="color:#fff;">Create one</a></p>
    <div style="margin-top:16px;padding:12px;background:var(--bg3);border:1px solid var(--border2);border-radius:var(--radius);font-size:.78rem;color:var(--text3);font-family:var(--mono);">
      <div style="color:var(--text2);margin-bottom:4px;">DEMO CREDENTIALS</div>
      Admin: admin@voltmarket.com / Admin1234!<br>User: john@example.com / Password1!
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
