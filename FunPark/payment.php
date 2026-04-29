<?php
/**
 * Страница оплаты бронирования
 */
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$id = (int)($_GET['id'] ?? 0);
$item = getItemByIdWithPrice($id, $_SESSION['user_login']);

if (!$item) {
    redirect('items.php');
}

// Если уже оплачено
if (isset($item['status']) && $item['status'] === 'paid') {
    redirect('items.php?paid=1');
}

$paymentMethods = getPaymentMethods();
$error = null;

$pageTitle = 'Оплата билета';
include 'includes/head.php';
include 'includes/header.php';
?>

<div class="page-hero">
    <h1>Оплата билета</h1>
    <p>Бронирование #<?php echo $item['id']; ?></p>
</div>

<section class="payment-section">
    <div class="container">
        <div class="payment-layout">
            <!-- Информация о брони -->
            <div class="payment-order-info">
                <h3>Детали заказа</h3>
                <div class="order-card">
                    <div class="order-item">
                        <span class="order-label">Билет:</span>
                        <span class="order-value"><?php echo e($item['title']); ?></span>
                    </div>
                    <div class="order-item">
                        <span class="order-label">Дата посещения:</span>
                        <span class="order-value">📅 <?php echo e($item['visit_date']); ?></span>
                    </div>
                    <div class="order-item">
                        <span class="order-label">Статус:</span>
                        <span class="order-value order-status order-status--pending">⏳ Ожидает оплаты</span>
                    </div>
                    <div class="order-total">
                        <span>К оплате:</span>
                        <span class="order-total-price"><?php echo number_format($item['price'], 0, '', ' '); ?> ₽</span>
                    </div>
                </div>
            </div>
            
            <!-- Форма оплаты -->
            <div class="payment-form-container">
                <?php if ($error): ?>
                    <div class="alert error"><?php echo e($error); ?></div>
                <?php endif; ?>
                
                <h3>Выберите способ оплаты</h3>
                
                <div class="payment-methods">
                    <?php foreach ($paymentMethods as $method): ?>
                        <label class="payment-method" data-method="<?php echo e($method['id']); ?>">
                            <input type="radio" name="payment_method" value="<?php echo e($method['id']); ?>" 
                                   <?php echo $method['id'] === 'card' ? 'checked' : ''; ?>>
                            <div class="payment-method-content">
                                <span class="payment-method-icon"><?php echo e($method['icon']); ?></span>
                                <div class="payment-method-info">
                                    <span class="payment-method-name"><?php echo e($method['name']); ?></span>
                                    <span class="payment-method-desc"><?php echo e($method['description']); ?></span>
                                </div>
                                <?php if ($method['commission'] > 0): ?>
                                    <span class="payment-method-commission">+<?php echo $method['commission']; ?>%</span>
                                <?php else: ?>
                                    <span class="payment-method-commission payment-method-commission--free">Без комиссии</span>
                                <?php endif; ?>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                
                <!-- Форма для карты -->
                <div id="cardForm" class="card-form">
                    <h4>Данные карты</h4>
                    <div class="form-group">
                        <label for="card_number">Номер карты</label>
                        <input type="text" id="card_number" class="form-input" 
                               placeholder="0000 0000 0000 0000" maxlength="19"
                               autocomplete="cc-number">
                        <span class="form-hint" id="cardType"></span>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="card_expiry">Срок действия</label>
                            <input type="text" id="card_expiry" class="form-input" 
                                   placeholder="MM/YY" maxlength="5"
                                   autocomplete="cc-exp">
                        </div>
                        <div class="form-group">
                            <label for="card_cvv">CVV</label>
                            <input type="text" id="card_cvv" class="form-input" 
                                   placeholder="000" maxlength="4"
                                   autocomplete="cc-csc">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="card_holder">Имя владельца</label>
                        <input type="text" id="card_holder" class="form-input" 
                               placeholder="IVAN IVANOV" style="text-transform: uppercase;"
                               autocomplete="cc-name">
                    </div>
                </div>
                
                <!-- Итоговая сумма -->
                <div class="payment-total">
                    <div class="payment-total-row">
                        <span>Стоимость билета:</span>
                        <span id="basePrice"><?php echo number_format($item['price'], 0, '', ' '); ?> ₽</span>
                    </div>
                    <div class="payment-total-row" id="commissionRow" style="display: none;">
                        <span>Комиссия:</span>
                        <span id="commissionAmount">0 ₽</span>
                    </div>
                    <div class="payment-total-row payment-total-row--final">
                        <span>Итого:</span>
                        <span id="totalPrice"><?php echo number_format($item['price'], 0, '', ' '); ?> ₽</span>
                    </div>
                </div>
                
                <button type="button" id="payBtn" class="btn-primary btn-full" data-id="<?php echo $item['id']; ?>">
                    Оплатить
                </button>
                
                <div class="payment-security">
                    <span>🔒 Платёж защищён SSL-шифрованием</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Модальное окно обработки оплаты -->
<div class="modal-overlay" id="paymentModal">
    <div class="modal-content modal-payment">
        <div class="modal-payment-icon">
            <div class="spinner"></div>
        </div>
        <h3 id="modalTitle">Обработка платежа...</h3>
        <p id="modalText">Пожалуйста, подождите</p>
        <div class="modal-progress">
            <div class="modal-progress-bar" id="progressBar"></div>
        </div>
        <div class="modal-timer" id="modalTimer">Осталось: <span id="timerValue">5</span> сек.</div>
    </div>
</div>

<script src="assets/js/payment.js"></script>
<script>
// Данные для JS
const bookingData = {
    id: <?php echo $item['id']; ?>,
    price: <?php echo $item['price']; ?>,
    paymentMethods: <?php echo json_encode($paymentMethods); ?>
};
</script>

<?php include 'includes/footer.php'; ?>
