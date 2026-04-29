<?php
// header.php - шапка сайта с навигацией
// Не содержит открывающих тегов html/body (они в head.php)
?>
<header>
    <nav class="container">
        <a href="index.php" class="logo">FunPark</a>
        
        <ul class="nav-links">
            <li><a href="index.php" class="<?php echo isActive('index'); ?>">Главная</a></li>
            <li><a href="zones.php" class="<?php echo isActive('zones'); ?>">Зоны парка</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="items.php" class="<?php echo isActive('items'); ?>">Мои билеты</a></li>
                <li><a href="item_add.php" class="<?php echo isActive('item_add'); ?>">Забронировать</a></li>
                <li><a href="settings.php" class="<?php echo isActive('settings'); ?>">Настройки</a></li>
                <li><a href="logout.php" class="nav-link-logout">Выйти</a></li>
            <?php else: ?>
                <li><a href="login.php" class="<?php echo isActive('login'); ?>">Вход</a></li>
                <li><a href="register.php" class="<?php echo isActive('register'); ?>">Регистрация</a></li>
            <?php endif; ?>
        </ul>
        
        <button class="theme-toggle" id="themeToggle" aria-label="Сменить тему" title="Сменить тему">
            🌙
        </button>
        
        <?php if (isLoggedIn()): ?>
            <span class="user-greeting">Привет, <?php echo e($_SESSION['user_name']); ?>!</span>
        <?php endif; ?>
        
        <button class="burger" id="burger" aria-label="Открыть меню">
            <span class="burger-line"></span>
            <span class="burger-line"></span>
            <span class="burger-line"></span>
        </button>
    </nav>
</header>

<!-- Мобильное меню -->
<div class="mobile-menu" id="mobileMenu">
    <button class="mobile-menu-close" id="mobileMenuClose" aria-label="Закрыть меню">×</button>
    <nav class="mobile-nav">
        <a href="index.php" class="mobile-nav-link <?php echo isActive('index'); ?>">Главная</a>
        <a href="zones.php" class="mobile-nav-link <?php echo isActive('zones'); ?>">Зоны парка</a>
        <?php if (isLoggedIn()): ?>
            <a href="items.php" class="mobile-nav-link <?php echo isActive('items'); ?>">Мои билеты</a>
            <a href="item_add.php" class="mobile-nav-link <?php echo isActive('item_add'); ?>">Забронировать</a>
            <a href="settings.php" class="mobile-nav-link <?php echo isActive('settings'); ?>">Настройки</a>
            <a href="logout.php" class="mobile-nav-link mobile-nav-link--logout">Выйти</a>
        <?php else: ?>
            <a href="login.php" class="mobile-nav-link <?php echo isActive('login'); ?>">Вход</a>
            <a href="register.php" class="mobile-nav-link <?php echo isActive('register'); ?>">Регистрация</a>
        <?php endif; ?>
    </nav>
</div>

<main class="container">