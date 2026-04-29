<?php
/**
 * Список бронирований пользователя
 */
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$items = getAllItemsWithPrice($_SESSION['user_login']);

$pageTitle = 'Мои билеты';
include 'includes/head.php';
include 'includes/header.php';
?>

<div class="page-hero">
    <h1>Мои бронирования</h1>
    <p>Управляйте своими билетами и бронированиями</p>
</div>

<section class="items-section">
    <div class="container">
        <?php if (isset($_GET['added'])): ?>
            <div class="alert success">Бронирование успешно создано!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['paid'])): ?>
            <div class="alert success">✅ Оплата успешно завершена! Билет активирован.</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert success">Бронирование обновлено!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert success">Бронирование удалено.</div>
        <?php endif; ?>
        
        <?php if (count($items) === 0): ?>
            <div class="empty-state">
                <h3>У вас пока нет бронирований</h3>
                <p>Забронируйте первый билет и начните своё приключение!</p>
                <a href="item_add.php" class="btn-primary">Забронировать билет</a>
            </div>
        <?php else: ?>
            <div style="margin-bottom: 24px; display: flex; gap: 16px; flex-wrap: wrap;">
                <a href="item_add.php" class="btn-primary">+ Новое бронирование</a>
                <a href="zones.php" class="btn-secondary">Зоны парка</a>
            </div>
            
            <div class="items-grid">
                <?php foreach ($items as $item): 
                    $status = $item['status'] ?? 'pending';
                    $statusClass = $status === 'paid' ? 'item-card--paid' : ($status === 'cancelled' ? 'item-card--cancelled' : '');
                ?>
                    <div class="item-card <?php echo $statusClass; ?>">
                        <div class="item-card-header">
                            <h3><?php echo e($item['title']); ?></h3>
                            <span class="item-status item-status--<?php echo e($status); ?>">
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
                        
                        <?php if ($item['description']): ?>
                            <p><?php echo e(mb_substr($item['description'], 0, 120)); ?><?php echo mb_strlen($item['description']) > 120 ? '...' : ''; ?></p>
                        <?php endif; ?>
                        
                        <div class="item-meta">
                            <span>📅 <?php echo e($item['visit_date']); ?></span>
                            <span>🆔 №<?php echo $item['id']; ?></span>
                        </div>
                        
                        <div class="item-price">
                            <span class="item-price-label">Стоимость:</span>
                            <span class="item-price-value"><?php echo number_format($item['price'], 0, '', ' '); ?> ₽</span>
                        </div>
                        
                        <div class="item-actions">
                            <a href="item_view.php?id=<?php echo $item['id']; ?>" class="btn-secondary">Просмотр</a>
                            <?php if ($status === 'pending'): ?>
                                <a href="payment.php?id=<?php echo $item['id']; ?>" class="btn-primary">Оплатить</a>
                            <?php endif; ?>
                            <a href="item_edit.php?id=<?php echo $item['id']; ?>" class="btn-secondary">Изменить</a>
                            <a href="item_delete.php?id=<?php echo $item['id']; ?>" class="btn-secondary btn-danger" 
                               onclick="return confirm('Удалить это бронирование?')">Удалить</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
