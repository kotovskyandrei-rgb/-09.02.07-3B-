<?php
/**
 * Главная страница FunPark
 */
require_once 'includes/functions.php';

$zones = getZones();
$news = getNews(3);
$reviews = getReviews(3);

$pageTitle = 'Главная';
include 'includes/head.php';
include 'includes/header.php';
?>

<section class="hero">
    <h1>FunPark — парк развлечений</h1>
    <p>Бронируйте билеты онлайн и получайте яркие эмоции!</p>
    
    <?php if (!isLoggedIn()): ?>
        <div class="hero-buttons">
            <a href="register.php" class="btn-primary">Регистрация</a>
            <a href="login.php" class="btn-secondary">Вход</a>
        </div>
    <?php else: ?>
        <div class="hero-buttons">
            <a href="item_add.php" class="btn-primary">Забронировать билет</a>
            <a href="zones.php" class="btn-secondary">Зоны парка</a>
        </div>
    <?php endif; ?>
</section>

<?php if (isLoggedIn()): 
    $user = getCurrentUser();
    $stats = getUserStats($_SESSION['user_login']);
    $items = getAllItemsWithPrice($_SESSION['user_login']);
?>
    <!-- Статистика для авторизованных -->
    <section class="stats-preview">
        <div class="container">
            <h2>Добро пожаловать, <?php echo e($user['name']); ?>!</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🎟️</div>
                    <div class="stat-info">
                        <h4>Ваших билетов</h4>
                        <span><?php echo $stats['total_tickets']; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <h4>Оплачено</h4>
                        <span><?php echo $stats['paid_tickets']; ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h4>Потрачено</h4>
                        <span><?php echo number_format($stats['total_spent'], 0, '', ' '); ?> ₽</span>
                    </div>
                </div>
            </div>
            
            <div class="quick-actions">
                <a href="item_add.php" class="btn-primary">🎫 Забронировать билет</a>
                <a href="items.php" class="btn-secondary">📋 Мои билеты</a>
                <a href="settings.php?tab=achievements" class="btn-secondary">🏆 Достижения</a>
            </div>
        </div>
    </section>
<?php else: ?>
    <!-- Преимущества для неавторизованных -->
    <section class="features">
        <div class="container">
            <h2>Почему FunPark?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🎢</div>
                    <h3>50+ аттракционов</h3>
                    <p>Более 50 современных аттракционов для всей семьи в трёх уникальных зонах</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🎫</div>
                    <h3>Онлайн-бронь</h3>
                    <p>Покупайте билеты без очередей и ожиданий. Выберите удобный способ оплаты</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👨‍👩‍👧‍👦</div>
                    <h3>Для всей семьи</h3>
                    <p>Зоны для детей, семейного отдыха и любителей экстрима</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Выгодные цены</h3>
                    <p>Семейные билеты со скидкой до 30%, сезонные абонементы и спецпредложения</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Призыв к регистрации -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-form-container">
                <h2>Начните приключение</h2>
                <p>Зарегистрируйтесь и бронируйте билеты в несколько кликов</p>
                <div class="auth-buttons">
                    <a href="register.php" class="btn-primary">Создать аккаунт</a>
                    <a href="login.php" class="btn-secondary">Уже есть аккаунт?</a>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Превью зон парка -->
<section class="zones-preview">
    <div class="container">
        <h2>Зоны парка</h2>
        <p class="section-subtitle">Три уникальные зоны для незабываемых приключений</p>
        
        <div class="zones-preview-grid">
            <?php foreach ($zones as $zone): ?>
                <div class="zone-preview-card zone-preview-card--<?php echo e($zone['id']); ?>">
                    <div class="zone-preview-icon"><?php echo e($zone['icon']); ?></div>
                    <h3><?php echo e($zone['name']); ?></h3>
                    <p><?php echo e(mb_substr($zone['description'], 0, 100)); ?>...</p>
                    <div class="zone-preview-stats">
                        <span><?php echo count($zone['attractions']); ?> аттракционов</span>
                        <span><?php echo number_format($zone['visitors_count'], 0, '', ' '); ?> посетителей</span>
                    </div>
                    <a href="zones.php" class="btn-secondary">Подробнее</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Последние новости -->
<?php if (!empty($news)): ?>
<section class="news-section">
    <div class="container">
        <h2>Последние новости</h2>
        <div class="news-grid">
            <?php foreach ($news as $item): ?>
                <div class="news-card">
                    <div class="news-icon"><?php echo e($item['image']); ?></div>
                    <div class="news-content">
                        <span class="news-date"><?php echo e($item['date']); ?></span>
                        <h3><?php echo e($item['title']); ?></h3>
                        <p><?php echo e($item['content']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Отзывы -->
<?php if (!empty($reviews)): ?>
<section class="reviews-section">
    <div class="container">
        <h2>Отзывы посетителей</h2>
        <div class="reviews-grid">
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <span class="review-avatar"><?php echo e($review['avatar']); ?></span>
                        <div>
                            <h4><?php echo e($review['author']); ?></h4>
                            <div class="review-rating">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <span class="star <?php echo $i < $review['rating'] ? 'star--filled' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <p class="review-text">"<?php echo e($review['text']); ?>"</p>
                    <span class="review-date"><?php echo e($review['date']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA для неавторизованных -->
<?php if (!isLoggedIn()): ?>
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Готовы к приключениям?</h2>
            <p>Зарегистрируйтесь прямо сейчас и получите скидку 10% на первый билет!</p>
            <a href="register.php" class="btn-primary btn-large">Регистрация</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
