<?php
$id = $_GET['id'] ?? null;
require __DIR__ . '/../../lib/db.php';
if ($id !== null) {
    $sql = 'SELECT * FROM product_images WHERE product_id = ?';
    $query = $pdo->prepare($sql);
    $query->execute([$id]);
    $images = $query->fetchAll(PDO::FETCH_OBJ);
}
?>


<article class="bg-white border rounded-4 p-4 p-sm-5 shadow-sm mb-4">
    <div
        class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between pb-3 mb-4 border-bottom gap-3">

        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="bg-danger text-warning rounded-3 p-3 d-flex align-items-center justify-content-center"
                style="width: 48px; height: 48px;">
                <i class="bi bi-camera"></i>
            </div>
            <div>
                <h2 class="h5 mb-0 fw-bold text-dark">Текущие варианты на складе</h2>
                <p class="text-secondary small mb-0">Управление цветами, размерами и остатками</p>
            </div>
        </div>

        <form id="newform" action="/lib/api/private/update_images.php" method="POST" enctype="multipart/form-data"
            class="d-flex align-items-center gap-2 align-self-start align-self-sm-auto">
            <label class="btn btn-sm btn-dark rounded-pill px-3 fw-semibold mb-0">
                Загрузить фото
                <input id="newPhotoInp" name="photo" type="file" accept="image/*" class="d-none">

            </label>
            <button id="addbtn" hidden name="action" value="add" type="submit"
                class="btn btn-sm btn-dark rounded-pill px-3 fw-semibold mb-0"> Загрузить фото
            </button>

        </form>
    </div>


    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
        <?php foreach ($images as $image): ?>
            <div class="col">
                <div
                    class="product-image-card main-image rounded-4 border border-2 border-dark bg-light overflow-hidden shadow-sm">
                    <div class="product-image-wrapper position-relative ratio ratio-1x1 bg-white overflow-hidden">
                        <img src="/lib/api/private/upload/<?= ($image->image_url) ?>" alt="Main Cover - Front"
                            class="w-100 h-100 object-fit-contain p-2 product-image">
                        <div class="image-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex
                            flex-column align-items-center justify-content-center gap-2 p-3">


                            <form class="myForm" action="/lib/api/private/update_images.php" method="POST"
                                enctype="multipart/form-data">
                                <label class="btn btn-light btn-sm rounded-pill fw-semibold w-100">
                                    Заменить
                                    <input name="photo" type="file" accept="image/*" class="fileInput d-none">
                                </label>
                                <button hidden name="action" value="change" type="submit"
                                    class="submitBtn">Отправить</button>

                                <input type="hidden" name="image_id" value="<?= ($image->id) ?>">

                                <input type="hidden" name="image_name" value="<?= ($image->image_url) ?>">
                                <button name="action" value="remove" type="submit"
                                    class="btn btn-sm btn-outline-light rounded-pill w-100">
                                    Удалить
                                </button>
                            </form>

                        </div>
                    </div>
                    <div class="p-2 bg-white border-top d-flex align-items-center justify-content-between">

                        <div class="text-truncate pe-2">
                            <span class="d-block fw-medium text-dark small text-truncate">
                                <?= ($image->image_url) ?>
                            </span>

                            <input type="hidden" name="ismain" value="<?= ($image->is_main) ?>">

                            <span class="d-block text-secondary" style="font-size: 10px;">
                                840 KB •
                                <?= ($image->is_main) ?>

                            </span>
                        </div>

                        <span class="status-dot bg-success flex-shrink-0"></span>

                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
</article>


<script>
    document.querySelectorAll('.myForm').forEach(form => {

        const fileInput = form.querySelector('.fileInput');
        const submitBtn = form.querySelector('.submitBtn');

        if (fileInput && submitBtn) {
            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) {
                    submitBtn.click();
                }
            });
        }
    });

    const fileInput = document.querySelector('.fileInput');
    const form = document.querySelector('.myForm');
    document.getElementById('newPhotoInp').addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            document.getElementById('addbtn').click();
        }
    });

</script>