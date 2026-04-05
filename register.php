<?php
$pageTitle = 'Register';
require_once __DIR__ . '/includes/auth.php';
if(isLoggedIn()){ header("Location: ".BASE_URL."/index.php"); exit; }
require_once __DIR__ . '/includes/db.php';

$errors=[]; $data=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  $data['name']=trim($_POST['name']??''); $data['email']=strtolower(trim($_POST['email']??''));
  $password=$_POST['password']??''; $confirm=$_POST['confirm_password']??'';
  if(strlen($data['name'])<2) $errors[]='Name must be at least 2 characters.';
  if(!filter_var($data['email'],FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid email address.';
  if(strlen($password)<8) $errors[]='Password must be at least 8 characters.';
  if($password!==$confirm) $errors[]='Passwords do not match.';
  if(empty($errors)){ $s=$pdo->prepare("SELECT id FROM users WHERE email=?"); $s->execute([$data['email']]); if($s->fetch()) $errors[]='Email already registered.'; }
  if(empty($errors)){
    $hash=password_hash($password,PASSWORD_DEFAULT);
    $s=$pdo->prepare("INSERT INTO users(name,email,password) VALUES(?,?,?)"); $s->execute([$data['name'],$data['email'],$hash]);
    $uid=$pdo->lastInsertId(); session_regenerate_id(true);
    $_SESSION['user_id']=$uid; $_SESSION['user_name']=$data['name']; $_SESSION['is_admin']=0;
    header("Location: ".BASE_URL."/index.php"); exit;
  }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="vm-auth-outer">
  <div class="vm-auth-card">
    <div class="vm-auth-logo"><i class="bi bi-lightning-charge-fill"></i> VoltMarket</div>
    <h2 style="margin-bottom:4px;">Create account</h2>
    <p style="color:var(--text2);font-size:.875rem;margin-bottom:24px;">Join VoltMarket and start buying &amp; selling</p>
    <?php if($errors): ?><div class="vm-alert a-danger"><i class="bi bi-exclamation-circle"></i><ul style="margin:0 0 0 8px;padding:0 0 0 12px;"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="POST" class="vm-form" novalidate>
      <div class="vm-form-group"><label class="vm-sidebar-lbl">Full Name</label><input type="text" name="name" class="vm-input" placeholder="Jane Smith" value="<?= htmlspecialchars($data['name']??'') ?>" required minlength="2" autofocus></div>
      <div class="vm-form-group"><label class="vm-sidebar-lbl">Email Address</label><input type="email" name="email" class="vm-input" placeholder="you@example.com" value="<?= htmlspecialchars($data['email']??'') ?>" required></div>
      <div class="vm-form-group">
        <label class="vm-sidebar-lbl">Password</label>
        <input type="password" name="password" id="password" class="vm-input" placeholder="Min. 8 characters" required minlength="8">
        <div class="vm-pwd-bar"><div class="vm-pwd-fill" id="pwdFill"></div></div>
      </div>
      <div class="vm-form-group"><label class="vm-sidebar-lbl">Confirm Password</label><input type="password" name="confirm_password" id="confirm_password" class="vm-input" placeholder="Repeat password" required></div>
      <button type="submit" class="btn-volt" style="width:100%;justify-content:center;margin-top:8px;padding:12px;"><i class="bi bi-person-check"></i> Create Account</button>
    </form>
    <hr style="border-color:var(--border);margin:24px 0;">
    <p style="text-align:center;font-size:.85rem;color:var(--text2);margin:0;">Already registered? <a href="<?= BASE_URL ?>/login.php" style="color:#fff;">Login</a></p>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
