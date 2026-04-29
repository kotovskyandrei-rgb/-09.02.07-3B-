<?php
/**
 * Обработка оплаты (AJAX)
 */
require_once 'includes/functions.php';

header('Content-Type: application/json');

// Проверяем авторизацию
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необходима авторизация']);
    exit;
}

// Проверяем метод
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Неверный метод запроса']);
    exit;
}

// Получаем данные
$input = json_decode(file_get_contents('php://input'), true);

$id = (int)($input['booking_id'] ?? 0);
$paymentMethod = $input['payment_method'] ?? '';
$cardData = $input['card_data'] ?? [];

// Валидация
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID бронирования']);
    exit;
}

$item = getItemByIdWithPrice($id, $_SESSION['user_login']);
if (!$item) {
    echo json_encode(['success' => false, 'error' => 'Бронирование не найдено']);
    exit;
}

if (isset($item['status']) && $item['status'] === 'paid') {
    echo json_encode(['success' => false, 'error' => 'Билет уже оплачен']);
    exit;
}

// Валидация способа оплаты
$paymentMethods = getPaymentMethods();
$methodValid = false;
$commission = 0;

foreach ($paymentMethods as $method) {
    if ($method['id'] === $paymentMethod) {
        $methodValid = true;
        $commission = $method['commission'];
        break;
    }
}

if (!$methodValid) {
    echo json_encode(['success' => false, 'error' => 'Неверный способ оплаты']);
    exit;
}

// Валидация данных карты (только для карт)
if ($paymentMethod === 'card') {
    $cardNumber = preg_replace('/[\s-]/', '', $cardData['number'] ?? '');
    $cardExpiry = $cardData['expiry'] ?? '';
    $cardCvv = $cardData['cvv'] ?? '';
    
    // Проверка номера карты (алгоритм Луна)
    if (!validateCardNumber($cardNumber)) {
        echo json_encode(['success' => false, 'error' => 'Неверный номер карты']);
        exit;
    }
    
    // Проверка срока действия
    if (!validateCardExpiry($cardExpiry)) {
        echo json_encode(['success' => false, 'error' => 'Неверный срок действия карты или карта просрочена']);
        exit;
    }
    
    // Проверка CVV
    if (!validateCardCVV($cardCvv)) {
        echo json_encode(['success' => false, 'error' => 'Неверный CVV код']);
        exit;
    }
}

// Имитация обработки платежа (в реальном проекте здесь был бы запрос к платёжному шлюзу)
$success = true; // Имитируем успешную оплату

if ($success) {
    // Обновляем статус бронирования
    $updated = updateItemStatus($id, $_SESSION['user_login'], 'paid');
    
    if ($updated) {
        // Проверяем достижения
        checkAndUpdateAchievements($_SESSION['user_login']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Оплата успешно завершена!',
            'booking_id' => $id,
            'amount' => $item['price'] + ($item['price'] * $commission / 100)
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Ошибка обновления статуса']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка обработки платежа']);
}
