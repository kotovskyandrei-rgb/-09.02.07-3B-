<?php
/**
 * Просмотр бронирования
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

$status = $item['status'] ?? 'pending';
$ticketType = getTicketTypeById($item['ticket_type'] ?? '');

$pageTitle = 'Бронь #' . $item['id'];
include 'includes/head.php';
include 'includes/header.php';
?>

<div class="page-hero">
    <h1>Бронирование #<?php echo $item['id']; ?></h1>
    <p>Детали вашего бронирования</p>
</div>

<section class="auth-section">
    <div class="container">
        <div class="auth-form-container auth-form-container--wide">
            <div class="item-detail">
                <div class="item-detail-header">
                    <h2><?php echo e($item['title']); ?></h2>
                    <span class="item-status item-status--<?php echo e($status); ?> item-status--large">
                        <?php 
                            $statusLabels = [
                                'pending' => '⏳ Ожидает оплаты',
                                'paid' => '✅ Оплачено',
                                'cancelled' => '❌ Отменено'
                            ];
                            echo $statusLabels[$status] ?? $status;
                        ?>
                    </span>
                </div>
                
                <?php if ($ticketType): ?>
                    <div class="ticket-type-badge">
                        <span class="ticket-type-icon">🎫</span>
                        <span><?php echo e($ticketType['name']); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Дата посещения</label>
                        <p class="detail-value">📅 <?php echo e($item['visit_date']); ?></p>
                    </div>
                    
                    <div class="detail-item">
                        <label>Стоимость</label>
                        <p class="detail-value detail-value--price"><?php echo number_format($item['price'], 0, '', ' '); ?> ₽</p>
                    </div>
                    
                    <div class="detail-item">
                        <label>Дата создания брони</label>
                        <p class="detail-value">🕐 <?php echo e($item['created_at']); ?></p>
                    </div>
                    
                    <div class="detail-item">
                        <label>Номер брони</label>
                        <p class="detail-value">🆔 #<?php echo $item['id']; ?></p>
                    </div>
                </div>
                
                <?php if ($item['description']): ?>
                    <div class="detail-item detail-item--full">
                        <label>Комментарий</label>
                        <p class="detail-value"><?php echo nl2br(e($item['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($ticketType && !empty($ticketType['includes'])): ?>
                    <div class="detail-item detail-item--full">
                        <label>Включено в билет</label>
                        <ul class="ticket-includes-list">
                            <?php foreach ($ticketType['includes'] as $include): ?>
                                <li>✓ <?php echo e($include); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="detail-actions">
                    <?php if ($status === 'pending'): ?>
                        <a href="payment.php?id=<?php echo $item['id']; ?>" class="btn-primary">Оплатить</a>
                    <?php endif; ?>
                    <a href="item_edit.php?id=<?php echo $item['id']; ?>" class="btn-secondary">Редактировать</a>
                    <a href="item_delete.php?id=<?php echo $item['id']; ?>" class="btn-secondary btn-danger" 
                       onclick="return confirm('Удалить это бронирование?')">Удалить</a>
                </div>
                
                <div class="detail-footer">
                    <a href="items.php">← Назад к списку</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
