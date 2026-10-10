<?php
$id = $_GET['id'] ?? null;
$_SESSION['csrf_token'];
?>
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form  method='POST' action="/lib/api/publish/review.php" class="modal-content review-modal">
            <div class="review-header">
                <input type="hidden" name="product_id" value="<?= (int) $id ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
   
                <div>
                    <h3>Оставить отзыв</h3>
                    <p>Расскажите, что вы думаете о товаре</p>
                </div>
                <button type="button" class="review-close" data-bs-dismiss="modal">
                      &times;
                </button>
            </div>
            <div class="rating-block">
                <label>Ваша оценка</label>
                <input type="hidden" class="rating-form" name="rating" value="">
                <div class="stars">
                    <input class="rate-star" type="radio" id="star5" name="rating" value="5">
                    <label for="star5">★</label>
                    <input class="rate-star" type="radio" id="star4" name="rating" value="4">
                    <label for="star4">★</label>
                    <input class="rate-star" type="radio" id="star3" name="rating" value="3">
                    <label class="rate-star" for="star3">★</label>
                    <input class="rate-star" type="radio" id="star2" name="rating" value="2">
                    <label for="star2">★</label>
                    <input class="rate-star" type="radio" id="star1" name="rating" value="1">
                    <label for="star1">★</label>
                </div>
            </div>
            <div class="review-field">
                <label>Ваш отзыв</label>
                <textarea name="review" class="review-input review-textarea" placeholder="Поделитесь впечатлениями о товаре..." rows="5"></textarea>
            </div>
            <div onclick="alert('Привет! Это сообщение об ошибке.')"  class="review-field">
                <label>Фото <span>(необязательно)</span></label>

                <label class="upload-photo">
                    <div class="upload-icon">+</div>
                    <div><strong>Добавить фото</strong> <small>JPG, PNG до 5 MB</small></div>
                </label>
            </div>
            <button name="action" value="add" type="submit" class="review-submit">
                Опубликовать отзыв
            </button>

            </div>
        </form>
    </div>
</div>

<style>
.btn-review {
    border: none;
    background: #111;
    color: #fff;
    padding: 14px 28px;
    border-radius: 30px;
    font-size: 14px;
    cursor: pointer;
    transition: .25s;
}
.modal-content {
    left: 0;
    padding: 15px;
}

.btn-review:hover {
    background: #333;
    transform: translateY(-1px);
}
#reviewModal .modal-dialog {
    max-width: 540px;
}

.review-modal {
    border: none;
    border-radius: 22px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 30px 80px rgba(0,0,0,.25);
}


.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 28px 30px 20px;
}

.review-header h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #111;
}

.review-header p {
    margin: 7px 0 0;
    color: #888;
    font-size: 13px;
}

.review-close {
    border: 0;
    background: #f4f4f4;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    font-size: 25px;
    line-height: 30px;
    color: #333;
    cursor: pointer;
    transition: .2s;
}

.review-close:hover {
    background: #111;
    color: #fff;
}



.rating-block {
    margin-bottom: 22px;
}

.rating-block label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

.stars {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 3px;
}

.stars input {
    display: none;
}

.stars label {
    font-size: 34px;
    color: #ddd;
    cursor: pointer;
    transition: .15s;
}

.stars label:hover,
.stars label:hover ~ label,
.stars input:checked ~ label {
    color: #f5b301;
}


.review-field {
    margin-bottom: 20px;
}

.review-field > label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #222;
    margin-bottom: 8px;
}

.review-field > label span {
    color: #aaa;
    font-weight: 400;
}

.review-input {
    width: 100%;
    border: 1px solid #e5e5e5;
    border-radius: 12px;
    padding: 13px 15px;
    font-size: 14px;
    outline: none;

    background: #fafafa;

    transition: .2s;
}

.review-input:focus {
    border-color: #111;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(0,0,0,.05);
}

.review-textarea {
    resize: vertical;
    min-height: 110px;
}


/* =========================
   UPLOAD
========================= */

.upload-photo {
    border: 1.5px dashed #ddd;
    border-radius: 13px;

    padding: 14px;

    display: flex;
    align-items: center;
    gap: 12px;

    cursor: pointer;

    transition: .2s;
}

.upload-photo:hover {
    border-color: #111;
    background: #fafafa;
}

.upload-photo input {
    display: none;
}

.upload-icon {
    width: 42px;
    height: 42px;

    border-radius: 10px;

    background: #f1f1f1;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 25px;
    color: #555;
}

.upload-photo strong {
    display: block;
    font-size: 13px;
}

.upload-photo small {
    display: block;
    color: #999;
    font-size: 11px;
    margin-top: 3px;
}


/* =========================
   SUBMIT
========================= */

.review-submit {
    width: 100%;

    border: none;
    border-radius: 13px;

    background: #111;
    color: #fff;

    padding: 15px;

    font-size: 14px;
    font-weight: 500;

    cursor: pointer;

    transition: .25s;
}

.review-submit:hover {
    background: #292929;
    transform: translateY(-1px);
}

@media (max-width: 576px) {

    #reviewModal .modal-dialog {
        margin: 10px;
    }

    .review-header {
        padding: 22px 20px 15px;
    }

    .review-header h3 {
        font-size: 21px;
    }

    #reviewModal .modal-body {
        padding: 0 20px 20px;
    }

    .stars label {
        font-size: 30px;
    }
}

</style>