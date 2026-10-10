<?php
session_start();
require('lib/db.php');

if (!isset($_SESSION['user_id'])) {header('Location: singup.php'); exit('Необходимо авторизоваться');}
$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_address'])) {

    $addressId = (int) $_POST['delivery_address'];
    $stmt = $pdo->prepare("
        UPDATE user_addresses
        SET is_default = 0
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $stmt = $pdo->prepare("
        UPDATE user_addresses
        SET is_default = 1
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$addressId, $userId]);
}

$sql = 'SELECT * FROM user_addresses WHERE user_id = ?';
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$address = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<div class="modal fade" id="deliveryAddressModal" tabindex="-1" aria-labelledby="deliveryAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deliveryAddressModalLabel">Выберите адрес доставки</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <?php foreach ($address as $item): ?> 
                        <label class="list-group-item list-group-item-action d-flex gap-3"> 
                            <input  class="form-check-input flex-shrink-0" type="radio" name="delivery_address" value="<?= (int)$item['id'] ?>"
                                <?= $item['is_default'] == 1 ? 'checked' : '' ?>
                            > 
                            <span> 
                                <strong class="d-block"><?= htmlspecialchars($item['title']) ?></strong> 
                                <span class="d-block">
                                    Г. <?= htmlspecialchars($item['city']) ?>,
                                    ул. <?= htmlspecialchars($item['title']) ?>,
                                    д. <?= htmlspecialchars($item['address']) ?>,
                                    кв. <?= htmlspecialchars($item['apartment']) ?>
                                </span> 
                                <small class="text-body-secondary"> <?= htmlspecialchars($item['recipient_phone']) ?> <?= htmlspecialchars($item['recipient_name']) ?></small> 
                            </span> 
                        </label> 
                    <?php endforeach; ?>
             
                   
                </div>
                <a href="cart.php?error=no_address" class="btn btn-outline-primary w-100 mt-3">+ Добавить новый адрес</a>
            </div>
            <div class="modal-footer">
                <button type="button"class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" name="change_address" class="btn btn-primary mt-3">Выбрать адрес</button>
            </div>

        </form>
    </div>
</div>