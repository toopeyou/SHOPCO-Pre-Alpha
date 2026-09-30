<?php
$id = $_GET['id'] ?? null;

require __DIR__ . '/../../lib/db.php';
$sql = 'SELECT * FROM products ';
$query = $pdo->prepare($sql);
$query->execute();
$products = $query->fetchAll(PDO::FETCH_OBJ);


?>

<aside class="col-12 col-lg-4">
    <div class="bg-white border rounded-4 p-4 shadow-sm mb-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fs-6 fw-bold text-uppercase mb-0">Список</h2>
            <span class="badge bg-light text-secondary">Продуктов </span>
        </div>
        <div class="position-relative mb-3">
            <input class="form-control form-control-sm rounded-3 ps-5 bg-light border-0" placeholder="Поиск продукта."
                type="text">
        </div>

        <div class="d-flex flex-column gap-2 overflow-auto pe-1" style="max-height: 560px;">
            <?php foreach ($products as $product): ?>

                <a href="productsedit.php?id=<?= (int) $product->id ?>"
                    class=" p-3 rounded-4 border bg-white d-flex align-items-center gap-3" data-="<?= $product->id ?>">

                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3 bg-light border"
                        style="width:56px;height:56px;">
                        <i class="bi bi-bag fs-5 text-secondary"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h4 class="small fw-semibold text-dark text-truncate mb-1">
                            <?= htmlspecialchars($product->name) ?>
                        </h4>

                        <p class="small text-secondary font-monospace mb-1">
                            ID: #SHP-004
                        </p>

                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-semibold">
                                <?= htmlspecialchars($product->price) ?>Рублей
                            </span>
                            <span class="badge bg-light text-secondary">
                                <?= htmlspecialchars($product->discount) ?>%
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-outline-secondary w-100 mt-3 rounded-4 border-dashed" type="button">
            Добавить продукт
        </button>
    </div>

</aside>