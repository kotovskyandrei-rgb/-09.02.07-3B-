<?php
/**
 * Создание нового бронирования с выбором типа билета
 */
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$error = null;
$ticketTypes = getTicketTypes();
$zones = getZones();

// Получаем зону из URL если есть
$selectedZone = $_GET['zone'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_type = trim($_POST['ticket_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $visit_date = $_POST['visit_date'] ?? '';
    
    // Получаем информацию о типе билета
    $ticketInfo = getTicketTypeById($ticket_type);
    
    if (!$ticketInfo) {
        $error = "Выберите тип билета";
    } elseif (empty($visit_date)) {
        $error = "Укажите дату посещения";
    } else {
        $id = saveItemWithPrice($_SESSION['user_login'], [
            'title' => $ticketInfo['name'],
            'description' => $description,
            'visit_date' => $visit_date,
            'price' => $ticketInfo['price'],
            'status' => 'pending',
            'ticket_type' => $ticket_type
        ]);
        
        // Проверяем достижения
        checkAndUpdateAchievements($_SESSION['user_login']);
        
        // Перенаправляем на страницу оплаты
        redirect('payment.php?id=' . $id);
    }
}

$pageTitle = 'Новое бронирование';
include 'includes/head.php';
include 'includes/header.php';
?>

<div class="page-hero">
    <h1>Новое бронирование</h1>
    <p>Выберите тип билета и забронируйте посещение парка</p>
</div>

<section class="auth-section">
    <div class="container">
        <div class="auth-form-container auth-form-container--wide">
            <?php if ($error): ?>
                <div class="alert error"><?php echo e($error); ?></div>
            <?php endif; ?>
            
            <form method="post" class="auth-form" id="bookingForm">
                <div class="form-group">
                    <label for="ticket_type">Тип билета *</label>
                    <select id="ticket_type" name="ticket_type" class="form-input form-select" required>
                        <option value="">-- Выберите тип билета --</option>
                        <?php foreach ($ticketTypes as $ticket): ?>
                            <option value="<?php echo e($ticket['id']); ?>" 
                                    data-price="<?php echo $ticket['price']; ?>"
                                    data-name="<?php echo e($ticket['name']); ?>">
                                <?php echo e($ticket['name']); ?> — <?php echo number_format($ticket['price'], 0, '', ' '); ?> ₽
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="ticketInfo" class="ticket-info" style="display: none;">
                    <div class="ticket-info-header">
                        <span id="ticketName"></span>
                        <span id="ticketPrice" class="ticket-price"></span>
                    </div>
                    <p id="ticketDescription"></p>
                    <div id="ticketIncludes" class="ticket-includes"></div>
                </div>
                
                <div class="form-group">
                    <label for="visit_date">Дата посещения *</label>
                    <input type="date" id="visit_date" name="visit_date" class="form-input" 
                           value="<?php echo e($_POST['visit_date'] ?? ''); ?>" required
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Комментарий (необязательно)</label>
                    <textarea id="description" name="description" class="form-input" 
                              rows="3" placeholder="Дополнительная информация"><?php echo e($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="booking-summary" id="bookingSummary" style="display: none;">
                    <h4>Итого к оплате:</h4>
                    <div class="summary-total">
                        <span id="summaryPrice">0</span> ₽
                    </div>
                </div>
                
                <button type="submit" class="btn-primary btn-full">Перейти к оплате</button>
            </form>
            
            <div class="auth-footer">
                <a href="zones.php">← Посмотреть зоны парка</a> | 
                <a href="items.php">Мои билеты</a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ticketSelect = document.getElementById('ticket_type');
    const ticketInfo = document.getElementById('ticketInfo');
    const ticketName = document.getElementById('ticketName');
    const ticketPrice = document.getElementById('ticketPrice');
    const ticketDescription = document.getElementById('ticketDescription');
    const ticketIncludes = document.getElementById('ticketIncludes');
    const bookingSummary = document.getElementById('bookingSummary');
    const summaryPrice = document.getElementById('summaryPrice');
    
    const ticketData = <?php echo json_encode($ticketTypes); ?>;
    
    ticketSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const ticketId = this.value;
        
        if (ticketId) {
            const ticket = ticketData.find(t => t.id === ticketId);
            
            if (ticket) {
                ticketName.textContent = ticket.name;
                ticketPrice.textContent = formatPrice(ticket.price) + ' ₽';
                ticketDescription.textContent = ticket.description || '';
                
                if (ticket.includes && ticket.includes.length > 0) {
                    ticketIncludes.innerHTML = '<strong>Включает:</strong><ul>' + 
                        ticket.includes.map(i => '<li>' + i + '</li>').join('') + '</ul>';
                } else {
                    ticketIncludes.innerHTML = '';
                }
                
                summaryPrice.textContent = formatPrice(ticket.price);
                ticketInfo.style.display = 'block';
                bookingSummary.style.display = 'block';
            }
        } else {
            ticketInfo.style.display = 'none';
            bookingSummary.style.display = 'none';
        }
    });
    
    function formatPrice(price) {
        return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
