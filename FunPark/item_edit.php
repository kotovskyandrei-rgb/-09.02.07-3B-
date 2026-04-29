<?php
/**
 * Редактирование бронирования
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

$error = null;
$ticketTypes = getTicketTypes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_type = trim($_POST['ticket_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $visit_date = $_POST['visit_date'] ?? '';
    
    $ticketInfo = getTicketTypeById($ticket_type);
    
    if (!$ticketInfo) {
        $error = "Выберите тип билета";
    } elseif (empty($visit_date)) {
        $error = "Укажите дату посещения";
    } else {
        updateItemWithPrice($id, $_SESSION['user_login'], [
            'title' => $ticketInfo['name'],
            'description' => $description,
            'visit_date' => $visit_date,
            'price' => $ticketInfo['price'],
            'ticket_type' => $ticket_type
        ]);
        redirect('items.php?updated=1');
    }
}

$pageTitle = 'Редактирование';
include 'includes/head.php';
include 'includes/header.php';
?>

<div class="page-hero">
    <h1>Редактирование брони</h1>
    <p>Измените данные бронирования</p>
</div>

<section class="auth-section">
    <div class="container">
        <div class="auth-form-container auth-form-container--wide">
            <?php if ($error): ?>
                <div class="alert error"><?php echo e($error); ?></div>
            <?php endif; ?>
            
            <form method="post" class="auth-form" id="editForm">
                <div class="form-group">
                    <label for="ticket_type">Тип билета *</label>
                    <select id="ticket_type" name="ticket_type" class="form-input form-select" required>
                        <?php foreach ($ticketTypes as $ticket): ?>
                            <option value="<?php echo e($ticket['id']); ?>" 
                                    data-price="<?php echo $ticket['price']; ?>"
                                    <?php echo ($item['ticket_type'] ?? '') === $ticket['id'] ? 'selected' : ''; ?>>
                                <?php echo e($ticket['name']); ?> — <?php echo number_format($ticket['price'], 0, '', ' '); ?> ₽
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="visit_date">Дата посещения *</label>
                    <input type="date" id="visit_date" name="visit_date" class="form-input" 
                           value="<?php echo e($_POST['visit_date'] ?? $item['visit_date']); ?>" required
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Комментарий</label>
                    <textarea id="description" name="description" class="form-input" 
                              rows="3"><?php echo e($_POST['description'] ?? $item['description']); ?></textarea>
                </div>
                
                <div class="booking-summary">
                    <h4>Текущая стоимость:</h4>
                    <div class="summary-total">
                        <span id="summaryPrice"><?php echo number_format($item['price'], 0, '', ' '); ?></span> ₽
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn-primary">Сохранить</button>
                    <a href="items.php" class="btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ticketSelect = document.getElementById('ticket_type');
    const summaryPrice = document.getElementById('summaryPrice');
    
    ticketSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.dataset.price;
        summaryPrice.textContent = price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    });
});
</script>

<?php include 'includes/footer.php'; ?>
