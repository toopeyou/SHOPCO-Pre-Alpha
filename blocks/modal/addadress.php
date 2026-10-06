<?php
$checkout;
?>


<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <?= htmlspecialchars($checkout) ?>
            <div class="modal-header border-0 px-4 px-md-5 pt-4">
                <div>
                    <h2 class="modal-title fw-bold fs-4">Добавить адрес</h2>
                    <p class="text-secondary small mb-0 mt-1">Сохраните адрес, чтобы быстрее оформлять заказы</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>


            <form action="../../lib/api/publish/useradress.php/" method="POST">
                <div class="modal-body px-4 px-md-5 py-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Название адреса</label>
                        <input type="text" name="title" class="form-control form-control-lg rounded-3" placeholder="Например, Дом или Работа"maxlength="50">
                        <div class="form-text">Так вам будет проще выбрать адрес при оформлении заказа.</div>
                    </div>    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Получатель</label>
                            <input type="text" name="recipient_name" class="form-control form-control-lg rounded-3" placeholder="Имя и фамилия" maxlength="150"  required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold"> Телефон</label>
                            <input
                                type="tel"
                                name="recipient_phone"
                                class="form-control form-control-lg rounded-3"
                                placeholder="+994 50 000 00 00"
                                maxlength="30"
                                required
                            >
                        </div>


                    </div>


  
                    <div class="row g-3 mb-3">

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Адрес</label>
                            <input
                                type="text"
                                name="address"
                                class="form-control form-control-lg rounded-3"
                                placeholder="Улица, дом"
                                maxlength="255"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Квартира</label>
                            <input
                                type="text"
                                name="apartment"
                                class="form-control form-control-lg rounded-3"
                                placeholder="Например, 25"
                                maxlength="50"
                            >
                        </div>

                    </div>

                    <div class="row g-3 mb-4">

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Подъезд</label>

                            <input
                                type="text"
                                name="entrance"
                                class="form-control rounded-3"
                                placeholder="1"
                                maxlength="20"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Этаж
                            </label>

                            <input
                                type="text"
                                name="floor"
                                class="form-control rounded-3"
                                placeholder="5"
                                maxlength="20"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Индекс
                            </label>

                            <input
                                type="text"
                                name="postcode"
                                class="form-control rounded-3"
                                placeholder="AZ1000"
                                maxlength="20"
                            >
                        </div>

                    </div>


                    <div class="mb-4">

                        <label class="form-label fw-semibold">
                            Город
                        </label>

                        <input
                            type="text"
                            name="city"
                            class="form-control form-control-lg rounded-3"
                            placeholder="Baku"
                            maxlength="100"
                            required
                        >

                    </div>


                    <div class="mb-4">
                        <label class="form-label fw-semibold">Комментарий <span class="text-secondary fw-normal">(необязательно)</span></label>
                        <textarea
                            name="comment"
                            class="form-control rounded-3"
                            rows="3"
                            maxlength="500"
                            placeholder="Например: позвонить перед доставкой"
                        ></textarea>

                    </div>

                    <div class="bg-light rounded-4 p-3">

                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_default"
                                value="1"
                                id="defaultAddress"
                            > 

                            <?php if ($checkout): ?> 
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_default"
                                value="1"
                                id="defaultAddress"
                                checked
                                >
                            <?php endif; ?>
                            <?php if (!$checkout): ?> 
                           <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_default"
                                value="1"
                                id="defaultAddress"
                            > 
                            <?php endif; ?>

                            <label
                                class="form-check-label"
                                for="defaultAddress"
                            >
                                <strong>Использовать как основной адрес</strong>

                                <div class="small text-secondary mt-1">
                                    Этот адрес будет выбран автоматически при оформлении заказа.
                                </div>
                            </label>

                        </div>

                    </div>

                </div>
                <div class="modal-footer border-0 px-4 px-md-5 pb-4">

                    <button
                        type="button"
                        class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal"
                    >
                        Отмена
                    </button>

                    <button
                        type="submit"
                        class="btn btn-dark rounded-pill px-4"
                    >
                        <i class="bi bi-check2 me-2"></i>
                        Сохранить адрес
                    </button>
                </div>
                <?php if ($checkout): ?> 
                <input type="hidden" name="payment" value="payment"                               >
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>