<?php
/**
 * Страница зон парка FunPark
 */
require_once 'includes/functions.php';

$zones = getZones();

$pageTitle = 'Зоны парка';
include 'includes/head.php';
include 'includes/header.php';
?>

<div class="page-hero">
    <h1>Зоны парка</h1>
    <p>Выберите зону для незабываемых приключений!</p>
</div>

<section class="zones-section">
    <div class="container">
        <?php if (empty($zones)): ?>
            <div class="empty-state">
                <h3>Информация о зонах временно недоступна</h3>
                <p>Попробуйте позже или свяжитесь с администрацией парка.</p>
            </div>
        <?php else: ?>
            <div class="zones-grid">
                <?php foreach ($zones as $zone): ?>
                    <div class="zone-card zone-card--<?php echo e($zone['id']); ?>">
                        <div class="zone-header">
                            <span class="zone-icon"><?php echo e($zone['icon']); ?></span>
                            <div class="zone-title-wrap">
                                <h2 class="zone-name"><?php echo e($zone['name']); ?></h2>
                                <span class="zone-status zone-status--<?php echo e($zone['status']); ?>">
                                    <?php echo $zone['status'] === 'open' ? '✅ Открыта' : '🔴 Закрыта'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <p class="zone-description"><?php echo e($zone['description']); ?></p>
                        
                        <div class="zone-stats">
                            <div class="zone-stat">
                                <span class="zone-stat-value"><?php echo number_format($zone['visitors_count'], 0, '', ' '); ?></span>
                                <span class="zone-stat-label">посетителей</span>
                            </div>
                            <div class="zone-stat">
                                <span class="zone-stat-value"><?php echo count($zone['attractions']); ?></span>
                                <span class="zone-stat-label">аттракционов</span>
                            </div>
                        </div>
                        
                        <div class="attractions-list">
                            <h3 class="attractions-title">🎢 Аттракционы:</h3>
                            <ul class="attractions-items">
                                <?php foreach ($zone['attractions'] as $attraction): ?>
                                    <li class="attraction-item">
                                        <span class="attraction-name"><?php echo e($attraction['name']); ?></span>
                                        <?php if (isset($attraction['height'])): ?>
                                            <span class="attraction-detail">Высота: <?php echo e($attraction['height']); ?></span>
                                        <?php endif; ?>
                                        <?php if (isset($attraction['speed'])): ?>
                                            <span class="attraction-detail">Скорость: <?php echo e($attraction['speed']); ?></span>
                                        <?php endif; ?>
                                        <?php if (isset($attraction['type'])): ?>
                                            <span class="attraction-detail">Тип: <?php echo e($attraction['type']); ?></span>
                                        <?php endif; ?>
                                        <?php if (isset($attraction['capacity'])): ?>
                                            <span class="attraction-detail">Вместимость: <?php echo e($attraction['capacity']); ?></span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <?php if (isLoggedIn()): ?>
                            <div class="zone-action">
                                <a href="item_add.php?zone=<?php echo e($zone['id']); ?>" class="btn-primary">
                                    Забронировать билет
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="zone-action">
                                <a href="login.php" class="btn-secondary">Войдите для бронирования</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
