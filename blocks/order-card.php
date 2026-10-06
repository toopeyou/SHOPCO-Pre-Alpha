<?php
$order = $items[0];
?>

<article class="order-card p-4 p-md-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pb-4 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-light rounded-4 p-3">
                <i class="bi bi-bag-check fs-5"></i>
            </div>

            <div>
                <div class="fw-bold">
                    № SHP-<?= htmlspecialchars($order['order_id']) ?>

                    <span class="small text-secondary fw-normal">
                        • <?= htmlspecialchars($order['order_date']) ?>
                    </span>
                </div>

                <div class="small text-secondary">
                    Доставлено курьером по адресу
                </div>
            </div>
        </div>

        <span class="status-pill bg-success-subtle text-success">
            <i class="bi bi-check-circle-fill me-1"></i>
            <?= htmlspecialchars($translations[$order['status']] ?? $order['status']) ?>
        </span>
    </div>

    <div class="py-3">
        <?php foreach ($items as $item): ?>
            <div class="d-flex justify-content-between align-items-center gap-3 py-3">
                <div class="d-flex align-items-center gap-3">
                    <img
                        src="lib/api/private/upload/<?= htmlspecialchars($item['product_image']) ?>"
                        class="product-thumb"
                        alt="<?= htmlspecialchars($item['product_name']) ?>"
                    >

                    <div>
                        <h3 class="h6 fw-bold mb-1">
                            <?= htmlspecialchars($item['product_name']) ?>
                        </h3>

                        <div class="small text-secondary">
                            Размер:
                            <strong class="text-dark">
                                <?= htmlspecialchars($item['size']) ?>
                            </strong>

                            <span class="mx-1">•</span>

                            Цвет:
                            <strong class="text-dark">
                                <?= htmlspecialchars($item['color']) ?>
                            </strong>
                        </div>
                    </div>
                </div>

                <strong>
                    <?= htmlspecialchars($item['price']) ?>
                </strong>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="border-top pt-4 mt-2">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
                <span class="small text-secondary">
                    Итого с учетом скидки:
                </span>

                <strong class="fs-5 ms-1">
                    $<?= htmlspecialchars($order['order_total']) ?>
                </strong>
            </div>
        </div>
    </div>
</article>