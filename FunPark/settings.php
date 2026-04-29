<?php
/**
 * Личный кабинет пользователя
 */
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();
$error = null;
$success = null;

// Обработка форм профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        $err1 = validateName($name);
        $err2 = validateEmail($email);
        
        if ($err1) $error = $err1;
        elseif ($err2) $error = $err2;
        else {
            updateUser($_SESSION['user_login'], ['name' => $name, 'email' => $email]);
            $_SESSION['user_name'] = $name;
            $success = "Профиль успешно обновлён";
            $user = getCurrentUser();
        }
    } elseif (isset($_POST['change_password'])) {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (!password_verify($old, $user['password'])) {
            $error = "Неверный текущий пароль";
        } elseif ($new !== $confirm) {
            $error = "Новый пароль и подтверждение не совпадают";
        } else {
            $err = validatePassword($new);
            if ($err) $error = $err;
            else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                updateUser($_SESSION['user_login'], ['password' => $hash]);
                $success = "Пароль успешно изменён";
            }
        }
    }
}

// Получаем данные для вкладок
$stats = getUserStats($_SESSION['user_login']);
$items = getAllItemsWithPrice($_SESSION['user_login']);
$achievements = getUserAchievements($_SESSION['user_login']);
$zones = getZones();

// Определяем активную вкладку
$activeTab = $_GET['tab'] ?? 'profile';

$pageTitle = 'Личный кабинет';
include 'includes/head.php';
include 'includes/header.php';
?>

<div class="page-hero">
    <h1>Личный кабинет</h1>
    <p>Управляйте своим аккаунтом и просматривайте статистику</p>
</div>

<section class="settings-section">
    <div class="container">
        <?php if ($error): ?>
            <div class="alert error"><?php echo e($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?php echo e($success); ?></div>
        <?php endif; ?>
        
        <!-- Табы -->
        <div class="tabs">
            <a href="?tab=profile" class="tab <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                👤 Профиль
            </a>
            <a href="?tab=tickets" class="tab <?php echo $activeTab === 'tickets' ? 'active' : ''; ?>">
                🎫 Мои билеты
            </a>
            <a href="?tab=stats" class="tab <?php echo $activeTab === 'stats' ? 'active' : ''; ?>">
                📊 Статистика
            </a>
            <a href="?tab=achievements" class="tab <?php echo $activeTab === 'achievements' ? 'active' : ''; ?>">
                🏆 Достижения
            </a>
        </div>
        
        <!-- Контент вкладок -->
        <div class="tab-content">
            <!-- Профиль -->
            <?php if ($activeTab === 'profile'): ?>
                <div class="settings-grid">
                    <div class="settings-card">
                        <h3>Личные данные</h3>
                        <form method="post" class="auth-form">
                            <div class="form-group">
                                <label for="name">Имя</label>
                                <input type="text" id="name" name="name" class="form-input" 
                                       value="<?php echo e($user['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-input" 
                                       value="<?php echo e($user['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Логин</label>
                                <input type="text" class="form-input" value="<?php echo e($user['login']); ?>" disabled>
                            </div>
                            
                            <div class="form-group">
                                <label>Дата регистрации</label>
                                <input type="text" class="form-input" value="<?php echo e($user['registered']); ?>" disabled>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn-primary">Обновить профиль</button>
                        </form>
                    </div>
                    
                    <div class="settings-card">
                        <h3>Смена пароля</h3>
                        <form method="post" class="auth-form">
                            <div class="form-group">
                                <label for="old_password">Текущий пароль</label>
                                <input type="password" id="old_password" name="old_password" class="form-input">
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Новый пароль</label>
                                <input type="password" id="new_password" name="new_password" class="form-input" 
                                       minlength="6" placeholder="Минимум 6 символов">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Подтверждение нового пароля</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-input">
                            </div>
                            
                            <button type="submit" name="change_password" class="btn-primary">Сменить пароль</button>
                        </form>
                    </div>
                </div>
            
            <!-- Мои билеты -->
            <?php elseif ($activeTab === 'tickets'): ?>
                <div class="tickets-list">
                    <?php if (empty($items)): ?>
                        <div class="empty-state">
                            <h3>У вас пока нет билетов</h3>
                            <p>Забронируйте первый билет!</p>
                            <a href="item_add.php" class="btn-primary">Забронировать</a>
                        </div>
                    <?php else: ?>
                        <div class="tickets-table-wrap">
                            <table class="tickets-table">
                                <thead>
                                    <tr>
                                        <th>№</th>
                                        <th>Билет</th>
                                        <th>Дата посещения</th>
                                        <th>Стоимость</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): 
                                        $status = $item['status'] ?? 'pending';
                                    ?>
                                        <tr>
                                            <td><?php echo $item['id']; ?></td>
                                            <td><?php echo e($item['title']); ?></td>
                                            <td><?php echo e($item['visit_date']); ?></td>
                                            <td><?php echo number_format($item['price'], 0, '', ' '); ?> ₽</td>
                                            <td>
                                                <span class="item-status item-status--<?php echo e($status); ?>">
                                                    <?php 
                                                        $statusLabels = [
                                                            'pending' => '⏳ Ожидает',
                                                            'paid' => '✅ Оплачено',
                                                            'cancelled' => '❌ Отменено'
                                                        ];
                                                        echo $statusLabels[$status] ?? $status;
                                                    ?>
                                                </span>
                                            </td>
                                            <td class="tickets-actions">
                                                <a href="item_view.php?id=<?php echo $item['id']; ?>" class="btn-sm">Просмотр</a>
                                                <?php if ($status === 'pending'): ?>
                                                    <a href="payment.php?id=<?php echo $item['id']; ?>" class="btn-sm btn-sm--primary">Оплатить</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            
            <!-- Статистика -->
            <?php elseif ($activeTab === 'stats'): ?>
                <div class="stats-dashboard">
                    <div class="stats-cards">
                        <div class="stat-card stat-card--large">
                            <div class="stat-icon">🎫</div>
                            <div class="stat-info">
                                <h4>Всего билетов</h4>
                                <span class="stat-value"><?php echo $stats['total_tickets']; ?></span>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-card--large">
                            <div class="stat-icon">✅</div>
                            <div class="stat-info">
                                <h4>Оплачено</h4>
                                <span class="stat-value"><?php echo $stats['paid_tickets']; ?></span>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-card--large stat-card--highlight">
                            <div class="stat-icon">💰</div>
                            <div class="stat-info">
                                <h4>Потрачено</h4>
                                <span class="stat-value"><?php echo number_format($stats['total_spent'], 0, '', ' '); ?> ₽</span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($stats['favorite_zone']): ?>
                        <div class="favorite-zone">
                            <h3>🎯 Любимая зона</h3>
                            <?php 
                                $favZone = getZoneById($stats['favorite_zone']);
                                if ($favZone):
                            ?>
                                <div class="favorite-zone-card">
                                    <span class="zone-icon-large"><?php echo e($favZone['icon']); ?></span>
                                    <div>
                                        <h4><?php echo e($favZone['name']); ?></h4>
                                        <p><?php echo e($favZone['description']); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($stats['zone_visits'])): ?>
                        <div class="zone-visits">
                            <h3>📍 Посещения по зонам</h3>
                            <div class="zone-visits-chart">
                                <?php foreach ($zones as $zone): 
                                    $visits = $stats['zone_visits'][$zone['id']] ?? 0;
                                    $percent = $stats['total_tickets'] > 0 ? round($visits / $stats['total_tickets'] * 100) : 0;
                                ?>
                                    <div class="zone-visits-item">
                                        <div class="zone-visits-header">
                                            <span><?php echo e($zone['icon']); ?> <?php echo e($zone['name']); ?></span>
                                            <span><?php echo $visits; ?> билетов</span>
                                        </div>
                                        <div class="zone-visits-bar">
                                            <div class="zone-visits-fill" style="width: <?php echo $percent; ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            
            <!-- Достижения -->
            <?php elseif ($activeTab === 'achievements'): ?>
                <div class="achievements-section">
                    <h3>Ваши достижения</h3>
                    <div class="achievements-grid">
                        <?php 
                            $achievementIds = ['first_visit', 'five_tickets', 'ten_tickets', 'family_fan', 'extreme_lover', 'big_spender', 'season_pass'];
                            foreach ($achievementIds as $achId):
                                $info = getAchievementInfo($achId);
                                $unlocked = $achievements[$achId] ?? false;
                        ?>
                            <div class="achievement-card <?php echo $unlocked ? 'achievement-card--unlocked' : 'achievement-card--locked'; ?>">
                                <div class="achievement-icon"><?php echo e($info['icon']); ?></div>
                                <div class="achievement-info">
                                    <h4><?php echo e($info['name']); ?></h4>
                                    <p><?php echo e($info['description']); ?></p>
                                </div>
                                <div class="achievement-status">
                                    <?php if ($unlocked): ?>
                                        <span class="achievement-unlocked">✅ Получено</span>
                                    <?php else: ?>
                                        <span class="achievement-locked">🔒 Закрыто</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="achievements-progress">
                        <h4>Прогресс</h4>
                        <div class="progress-bar">
                            <?php 
                                $unlockedCount = count(array_filter($achievements));
                                $totalCount = count($achievementIds);
                                $progressPercent = round($unlockedCount / $totalCount * 100);
                            ?>
                            <div class="progress-fill" style="width: <?php echo $progressPercent; ?>%"></div>
                        </div>
                        <p><?php echo $unlockedCount; ?> из <?php echo $totalCount; ?> достижений (<?php echo $progressPercent; ?>%)</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
