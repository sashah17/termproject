<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if(!$id){ header("Location: ".BASE_URL."/products.php"); exit; }
$stmt=$pdo->prepare("SELECT p.*,u.name AS seller_name FROM products p JOIN users u ON p.seller_id=u.id WHERE p.id=?");
$stmt->execute([$id]); $product=$stmt->fetch();
if(!$product){ header("Location: ".BASE_URL."/products.php"); exit; }
$pageTitle=$product['name'];
$success=$error='';
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='add_cart'){
  $qty=max(1,min((int)($_POST['quantity']??1),$product['stock']));
  if(isLoggedIn()){ $s=$pdo->prepare("INSERT INTO cart(user_id,product_id,quantity) VALUES(?,?,?) ON DUPLICATE KEY UPDATE quantity=LEAST(quantity+VALUES(quantity),?)"); $s->execute([$_SESSION['user_id'],$id,$qty,$product['stock']]); }
  else { $_SESSION['cart'][$id]=min(($_SESSION['cart'][$id]??0)+$qty,$product['stock']); }
  $success='Added to cart!';
}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='review'){
  if(!isLoggedIn()){ $error='Log in to leave a review.'; }
  else {
    $rating=(int)($_POST['rating']??0); $comment=trim($_POST['comment']??'');
    if($rating<1||$rating>5){ $error='Select a star rating.'; }
    else { $s=$pdo->prepare("INSERT INTO reviews(product_id,user_id,rating,comment) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating),comment=VALUES(comment)"); $s->execute([$id,$_SESSION['user_id'],$rating,$comment]); $success='Review saved!'; }
  }
}
$reviews=$pdo->prepare("SELECT r.*,u.name AS reviewer FROM reviews r JOIN users u ON r.user_id=u.id WHERE r.product_id=? ORDER BY r.created_at DESC");
$reviews->execute([$id]); $reviews=$reviews->fetchAll();
$avgRating=$reviews?round(array_sum(array_column($reviews,'rating'))/count($reviews),1):0;
$related=$pdo->prepare("SELECT * FROM products WHERE category=? AND id!=? AND stock>0 LIMIT 4");
$related->execute([$product['category'],$id]); $related=$related->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<div class="vm-bc"><a href="<?= BASE_URL ?>/index.php">Home</a><i class="bi bi-chevron-right"></i><a href="<?= BASE_URL ?>/products.php">Browse</a><i class="bi bi-chevron-right"></i><a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($product['category']) ?>"><?= htmlspecialchars($product['category']) ?></a><i class="bi bi-chevron-right"></i><span class="current"><?= htmlspecialchars(substr($product['name'],0,40)) ?>…</span></div>
<?php if($success): ?><div class="vm-alert a-success" data-dismiss><i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if($error): ?><div class="vm-alert a-danger"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-bottom:48px;">
  <div>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">
      <img id="mainImg" src="<?= htmlspecialchars($product['image_url']?:'https://via.placeholder.com/600x450/13151e/00e5a0?text=No+Image') ?>"
        style="width:100%;height:380px;object-fit:cover;" alt="<?= htmlspecialchars($product['name']) ?>">
    </div>
  </div>
  <div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
      <span class="vm-badge vm-badge-blue"><?= htmlspecialchars($product['category']) ?></span>
      <span class="vm-badge <?= in_array($product['condition'],['Like New','Good'])?'vm-badge-green':($product['condition']==='Fair'?'vm-badge-orange':'vm-badge-gray') ?>"><?= htmlspecialchars($product['condition']) ?></span>
    </div>
    <h1 style="font-size:1.6rem;margin-bottom:10px;"><?= htmlspecialchars($product['name']) ?></h1>
    <?php if($avgRating>0): ?><div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;"><div class="vm-stars"><?= str_repeat('★',round($avgRating)) ?><?= str_repeat('☆',5-round($avgRating)) ?></div><span style="font-size:.82rem;color:var(--text3);font-family:var(--mono);"><?= $avgRating ?>/5 (<?= count($reviews) ?> review<?= count($reviews)!=1?'s':'' ?>)</span></div><?php endif; ?>
    <div class="vm-price" style="font-size:2rem;margin-bottom:20px;">$<?= number_format($product['price'],2) ?></div>
    <p style="color:var(--text2);font-size:.9rem;line-height:1.7;margin-bottom:20px;"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:24px;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:14px;">
      <div style="font-size:.8rem;color:var(--text3);font-family:var(--mono);">STOCK<div style="color:var(--text);margin-top:2px;"><?= $product['stock'] ?> available</div></div>
      <div style="font-size:.8rem;color:var(--text3);font-family:var(--mono);">SELLER<div style="color:var(--text);margin-top:2px;"><?= htmlspecialchars($product['seller_name']) ?></div></div>
      <div style="font-size:.8rem;color:var(--text3);font-family:var(--mono);">LISTED<div style="color:var(--text);margin-top:2px;"><?= date('M j, Y',strtotime($product['created_at'])) ?></div></div>
      <div style="font-size:.8rem;color:var(--text3);font-family:var(--mono);">CONDITION<div style="color:var(--accent);margin-top:2px;"><?= htmlspecialchars($product['condition']) ?></div></div>
    </div>
    <?php if($product['stock']>0): ?>
    <form method="POST" style="display:flex;gap:10px;flex-direction:column;">
      <input type="hidden" name="action" value="add_cart">
      <div style="display:flex;align-items:center;gap:10px;">
        <div class="vm-qty">
          <button type="button" data-qty="dn" data-target="#qtyInput">−</button>
          <input type="number" id="qtyInput" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
          <button type="button" data-qty="up" data-target="#qtyInput">+</button>
        </div>
        <button type="submit" class="btn-volt" style="flex:1;justify-content:center;padding:11px;"><i class="bi bi-bag-plus"></i> Add to Cart</button>
      </div>
    </form>
    <?php else: ?><div class="vm-alert a-danger"><i class="bi bi-x-circle"></i> Out of stock</div><?php endif; ?>
  </div>
</div>

<!-- Reviews -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-bottom:48px;">
  <div>
    <h3 style="font-size:1.1rem;margin-bottom:20px;display:flex;align-items:center;gap:8px;"><span style="width:3px;height:18px;background:var(--accent);display:inline-block;border-radius:2px;"></span>Reviews (<?= count($reviews) ?>)</h3>
    <?php if(empty($reviews)): ?><div style="color:var(--text3);font-size:.875rem;">No reviews yet. Be the first!</div><?php endif; ?>
    <?php foreach($reviews as $rev): ?>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:14px;margin-bottom:10px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
        <span style="font-weight:600;font-size:.875rem;"><?= htmlspecialchars($rev['reviewer']) ?></span>
        <span style="font-size:.75rem;color:var(--text3);font-family:var(--mono);"><?= date('M j, Y',strtotime($rev['created_at'])) ?></span>
      </div>
      <div class="vm-stars"><?= str_repeat('★',$rev['rating']) ?><?= str_repeat('☆',5-$rev['rating']) ?></div>
      <?php if($rev['comment']): ?><p style="margin:8px 0 0;font-size:.85rem;color:var(--text2);"><?= htmlspecialchars($rev['comment']) ?></p><?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <div>
    <h3 style="font-size:1.1rem;margin-bottom:20px;display:flex;align-items:center;gap:8px;"><span style="width:3px;height:18px;background:var(--accent);display:inline-block;border-radius:2px;"></span>Leave a Review</h3>
    <?php if(isLoggedIn()): ?>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px;">
      <form method="POST">
        <input type="hidden" name="action" value="review">
        <input type="hidden" name="rating" id="ratingValue" value="0">
        <div style="margin-bottom:16px;">
          <label class="vm-sidebar-lbl">Rating</label>
          <div class="stars-row" style="font-size:1.8rem;cursor:pointer;letter-spacing:4px;color:var(--border2);">
            <?php for($i=1;$i<=5;$i++): ?><span class="rating-star" data-v="<?= $i ?>">★</span><?php endfor; ?>
          </div>
        </div>
        <div class="vm-form-group"><label class="vm-sidebar-lbl">Comment (optional)</label><textarea name="comment" class="vm-input" style="min-height:80px;" placeholder="Share your experience…"></textarea></div>
        <button type="submit" class="btn-volt" style="width:100%;justify-content:center;">Submit Review</button>
      </form>
    </div>
    <?php else: ?><div class="vm-alert a-info"><i class="bi bi-info-circle"></i> <a href="<?= BASE_URL ?>/login.php">Log in</a> to leave a review.</div><?php endif; ?>
  </div>
</div>

<?php if($related): ?>
<h3 style="font-size:1.1rem;margin-bottom:16px;display:flex;align-items:center;gap:8px;"><span style="width:3px;height:18px;background:var(--accent);display:inline-block;border-radius:2px;"></span>More in <?= htmlspecialchars($product['category']) ?></h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;">
  <?php foreach($related as $r): ?>
  <div class="vm-product-card">
    <div class="vm-card-img"><a href="<?= BASE_URL ?>/product.php?id=<?= $r['id'] ?>"><img src="<?= htmlspecialchars($r['image_url']?:'https://via.placeholder.com/400x300/13151e/00e5a0?text=+') ?>" alt="" loading="lazy"></a></div>
    <div class="vm-card-body"><a href="<?= BASE_URL ?>/product.php?id=<?= $r['id'] ?>" class="vm-card-title"><?= htmlspecialchars($r['name']) ?></a><div class="vm-card-footer"><span class="vm-price">$<?= number_format($r['price'],2) ?></span><a href="<?= BASE_URL ?>/product.php?id=<?= $r['id'] ?>" class="btn-card">View</a></div></div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
