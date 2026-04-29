<?php
/**
 * Страница входа
 */
require_once 'includes/functions.php';

// Если уже авторизован — перенаправляем на главную
if (isLoggedIn()) {
    redirect('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    $error = loginUser($login, $password, $remember);
    
    if ($error === true) {
        redirect('index.php');
    }
}

$pageTitle = 'Вход';
include 'includes/head.php';
include 'includes/header.php';
?>

<div class="page-hero">
    <h1>Вход в аккаунт</h1>
    <p>Войдите, чтобы управлять своими бронированиями</p>
</div>

<section class="auth-section">
    <div class="container">
        <div class="auth-form-container">
            <?php if ($error): ?>
                <div class="alert error"><?php echo e($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
                <div class="alert success">Регистрация успешна! Войдите в свой аккаунт.</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['logout'])): ?>
                <div class="alert success">Вы успешно вышли из аккаунта.</div>
            <?php endif; ?>
            
            <form method="post" class="auth-form">
                <div class="form-group">
                    <label for="login">Логин</label>
                    <input type="text" id="login" name="login" class="form-input" 
                           value="<?php echo e($_POST['login'] ?? ''); ?>" required 
                           autocomplete="username" placeholder="Введите логин">
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" class="form-input" 
                           required autocomplete="current-password" placeholder="Введите пароль">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                        <span>Запомнить меня</span>
                    </label>
                </div>
                
                <button type="submit" class="btn-primary btn-full">Войти</button>
            </form>
            
            <div class="auth-footer">
                <p>Нет аккаунта? <a href="register.php">Зарегистрируйтесь</a></p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>