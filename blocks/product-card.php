
<div class="col-12 col-sm-6 col-lg-3">
    <a href="product.php?id=<?= htmlspecialchars($product->id)?>"  class="product-card">
    <div class="product-image bg-product rounded-20 overflow-hidden mb-3 d-flex align-items-center justify-content-center p-4">
    <img src="/lib/api/private/upload/<?= htmlspecialchars($product->image )?>" alt="<?= htmlspecialchars($product->name) ?>">
    </div>
    <h3 class="fw-bold fs-6 text-black mb-1 text-truncate"><?= htmlspecialchars($product->name) ?></h3>
    <div class="d-flex align-items-center gap-2 mb-2">
    <span class="text-body-tertiary">
    <?php
    for ($i = 1; $i <= $product->rate; $i++) {
    echo '<i class="text-warning bi bi-star-fill"></i>';}
    for ($i = $product->rate; $i <= 4; $i++) {
        echo '<i class="bi bi-star-fill"></i>';
    }
    ?>
    </span>
    </div>
    <div class="d-flex align-items-center gap-2">
    <span class="fw-bold product-price text-black">₽<?= htmlspecialchars($product->price - ($product->price / 100 * $product->discount)) ?></span>
    <?php if ($product->discount > 0): ?>
    <span class="fw-bold product-price text-secondary text-decoration-line-through">₽<?= htmlspecialchars($product->price) ?></span>
    <span class="badge discount-badge">-<?= htmlspecialchars($product->discount) ?> %</span>
    <?php endif; ?>
    </div>
    </a>
</div>