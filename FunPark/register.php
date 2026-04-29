<?php
/**
 * Страница регистрации
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
    $confirm = $_POST['confirm'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Проверка совпадения паролей
    if ($password !== $confirm) {
        $error = "Пароли не совпадают";
    } else {
        $error = registerUser($login, $password, $name, $email);
        
        if ($error === true) {
            redirect('login.php?registered=1');
        }
    }
}

$pageTitle = 'Регистрация';
include 'includes/head.php';
include 'includes/header.php';
?>

<div class="page-hero">
    <h1>Регистрация</h1>
    <p>Создайте аккаунт для бронирования билетов</p>
</div>

<section class="auth-section">
    <div class="container">
        <div class="auth-form-container">
            <?php if ($error): ?>
                <div class="alert error"><?php echo e($error); ?></div>
            <?php endif; ?>
            
            <form method="post" class="auth-form">
                <div class="form-group">
                    <label for="login">Логин *</label>
                    <input type="text" id="login" name="login" class="form-input" 
                           value="<?php echo e($_POST['login'] ?? ''); ?>" required
                           pattern="[a-zA-Z0-9_]{3,}" 
                           title="Минимум 3 символа: буквы, цифры, подчёркивание"
                           placeholder="От 3 символов (латиница, цифры, _)">
                    <span class="form-hint">Минимум 3 символа: буквы, цифры, подчёркивание</span>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль *</label>
                    <input type="password" id="password" name="password" class="form-input" 
                           required minlength="6"
                           placeholder="Минимум 6 символов">
                    <span class="form-hint">Минимум 6 символов</span>
                </div>
                
                <div class="form-group">
                    <label for="confirm">Подтверждение пароля *</label>
                    <input type="password" id="confirm" name="confirm" class="form-input" 
                           required minlength="6"
                           placeholder="Повторите пароль">
                </div>
                
                <div class="form-group">
                    <label for="name">Ваше имя *</label>
                    <input type="text" id="name" name="name" class="form-input" 
                           value="<?php echo e($_POST['name'] ?? ''); ?>" required
                           pattern="[a-zA-Zа-яА-ЯёЁ\s\-]{2,}"
                           title="Минимум 2 символа: буквы, пробел, дефис"
                           placeholder="Как к вам обращаться?">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           value="<?php echo e($_POST['email'] ?? ''); ?>" required
                           placeholder="example@mail.com">
                </div>
                
                <button type="submit" class="btn-primary btn-full">Зарегистрироваться</button>
            </form>
            
            <div class="auth-footer">
                <p>Уже есть аккаунт? <a href="login.php">Войдите</a></p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>