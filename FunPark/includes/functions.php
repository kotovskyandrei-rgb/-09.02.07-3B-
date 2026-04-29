<?php
// functions.php - основные функции приложения
session_start();

// ------------------- ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ -------------------

/**
 * Возвращает CSS-класс 'active' для активного пункта меню
 * @param string $page Имя текущей страницы
 * @return string 'active' или пустая строка
 */
function isActive($page) {
    $current = basename($_SERVER['PHP_SELF'], '.php');
    return $current === $page ? 'active' : '';
}

/**
 * Перенаправление с завершением скрипта
 * @param string $url URL для перенаправления
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Безопасный вывод HTML
 * @param string $text Текст для экранирования
 * @return string Экранированный текст
 */
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// ------------------- РАБОТА С ПОЛЬЗОВАТЕЛЯМИ -------------------

function ensureDataDir() {
    if (!is_dir(__DIR__ . '/../data')) {
        mkdir(__DIR__ . '/../data', 0755, true);
    }
}

function getAllUsers() {
    ensureDataDir();
    $users = [];
    $file = __DIR__ . '/../data/users.txt';
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 5) {
                $users[$parts[0]] = [
                    'login' => $parts[0],
                    'password' => $parts[1],
                    'name' => $parts[2],
                    'email' => $parts[3],
                    'registered' => $parts[4]
                ];
            }
        }
    }
    return $users;
}

function findUserByLogin($login) {
    $users = getAllUsers();
    return isset($users[$login]) ? $users[$login] : null;
}

function saveUser($user) {
    ensureDataDir();
    $line = $user['login'] . '|' . $user['password'] . '|' . $user['name'] . '|' . $user['email'] . '|' . $user['registered'] . "\n";
    file_put_contents(__DIR__ . '/../data/users.txt', $line, FILE_APPEND | LOCK_EX);
}

function updateUser($login, $newData) {
    $users = getAllUsers();
    if (!isset($users[$login])) return false;
    $users[$login] = array_merge($users[$login], $newData);
    $lines = [];
    foreach ($users as $user) {
        $lines[] = $user['login'] . '|' . $user['password'] . '|' . $user['name'] . '|' . $user['email'] . '|' . $user['registered'];
    }
    file_put_contents(__DIR__ . '/../data/users.txt', implode("\n", $lines) . "\n");
    return true;
}

// ------------------- ВАЛИДАЦИЯ -------------------

function validateLogin($login) {
    if (strlen($login) < 3) return "Логин должен быть не менее 3 символов";
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $login)) return "Логин может содержать только буквы, цифры и _";
    return null;
}

function validatePassword($password) {
    if (strlen($password) < 6) return "Пароль должен быть не менее 6 символов";
    return null;
}

function validateName($name) {
    if (strlen($name) < 2) return "Имя должно быть не менее 2 символов";
    if (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s-]+$/u', $name)) return "Имя может содержать только буквы, пробел и дефис";
    return null;
}

function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Некорректный email";
    return null;
}

// ------------------- АУТЕНТИФИКАЦИЯ -------------------

function registerUser($login, $password, $name, $email) {
    $err = validateLogin($login);
    if ($err) return $err;
    $err = validatePassword($password);
    if ($err) return $err;
    $err = validateName($name);
    if ($err) return $err;
    $err = validateEmail($email);
    if ($err) return $err;

    if (findUserByLogin($login)) {
        return "Пользователь с таким логином уже существует";
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $user = [
        'login' => $login,
        'password' => $hash,
        'name' => $name,
        'email' => $email,
        'registered' => date('Y-m-d H:i:s')
    ];
    saveUser($user);
    return true;
}

function loginUser($login, $password, $remember = false) {
    $user = findUserByLogin($login);
    if (!$user) return "Неверный логин или пароль";
    if (!password_verify($password, $user['password'])) {
        return "Неверный логин или пароль";
    }
    $_SESSION['user_login'] = $login;
    $_SESSION['user_name'] = $user['name'];

    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + 86400 * 30, '/');
        file_put_contents(__DIR__ . '/../data/token_' . $login, $token);
    }
    return true;
}

function logoutUser() {
    if (isset($_SESSION['user_login'])) {
        $login = $_SESSION['user_login'];
        $tokenFile = __DIR__ . '/../data/token_' . $login;
        if (file_exists($tokenFile)) unlink($tokenFile);
    }
    $_SESSION = [];
    session_destroy();
    setcookie('remember_token', '', time() - 3600, '/');
}

function isLoggedIn() {
    if (isset($_SESSION['user_login'])) return true;
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        foreach (glob(__DIR__ . '/../data/token_*') as $file) {
            if (file_get_contents($file) === $token) {
                $login = str_replace('token_', '', basename($file));
                $user = findUserByLogin($login);
                if ($user) {
                    $_SESSION['user_login'] = $login;
                    $_SESSION['user_name'] = $user['name'];
                    return true;
                }
            }
        }
    }
    return false;
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return findUserByLogin($_SESSION['user_login']);
}

// ------------------- РАБОТА С БРОНИРОВАНИЯМИ -------------------

function getAllItems($user_login) {
    ensureDataDir();
    $items = [];
    $file = __DIR__ . '/../data/items.txt';
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 6 && $parts[1] === $user_login) {
                $items[] = [
                    'id' => $parts[0],
                    'user_login' => $parts[1],
                    'title' => $parts[2],
                    'description' => $parts[3],
                    'visit_date' => $parts[4],
                    'created_at' => $parts[5]
                ];
            }
        }
        usort($items, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
    }
    return $items;
}

function getNextId() {
    ensureDataDir();
    $max = 0;
    $file = __DIR__ . '/../data/items.txt';
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) && (int)$parts[0] > $max) $max = (int)$parts[0];
        }
    }
    return $max + 1;
}

function getItemById($id, $user_login) {
    $file = __DIR__ . '/../data/items.txt';
    if (!file_exists($file)) return null;
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if ($parts[0] == $id && $parts[1] === $user_login) {
            return [
                'id' => $parts[0],
                'user_login' => $parts[1],
                'title' => $parts[2],
                'description' => $parts[3],
                'visit_date' => $parts[4],
                'created_at' => $parts[5]
            ];
        }
    }
    return null;
}

function saveItem($user_login, $data) {
    ensureDataDir();
    $id = getNextId();
    $line = $id . '|' . $user_login . '|' . $data['title'] . '|' . $data['description'] . '|' . $data['visit_date'] . '|' . date('Y-m-d H:i:s') . "\n";
    file_put_contents(__DIR__ . '/../data/items.txt', $line, FILE_APPEND | LOCK_EX);
    return $id;
}

function updateItem($id, $user_login, $data) {
    $file = __DIR__ . '/../data/items.txt';
    $lines = file($file);
    $fp = fopen($file, 'w');
    flock($fp, LOCK_EX);
    $updated = false;
    foreach ($lines as $line) {
        $parts = explode('|', trim($line));
        if ($parts[0] == $id && $parts[1] === $user_login) {
            $newLine = $id . '|' . $user_login . '|' . $data['title'] . '|' . $data['description'] . '|' . $data['visit_date'] . '|' . $parts[5] . "\n";
            fwrite($fp, $newLine);
            $updated = true;
        } else {
            fwrite($fp, $line);
        }
    }
    flock($fp, LOCK_UN);
    fclose($fp);
    return $updated;
}

function deleteItem($id, $user_login) {
    $file = __DIR__ . '/../data/items.txt';
    $lines = file($file);
    $fp = fopen($file, 'w');
    flock($fp, LOCK_EX);
    $deleted = false;
    foreach ($lines as $line) {
        $parts = explode('|', trim($line));
        if ($parts[0] == $id && $parts[1] === $user_login) {
            $deleted = true;
            continue;
        }
        fwrite($fp, $line);
    }
    flock($fp, LOCK_UN);
    fclose($fp);
    return $deleted;
}

// ------------------- РАБОТА С ЗОНАМИ -------------------

/**
 * Получить все зоны парка из JSON
 * @return array Массив зон
 */
function getZones() {
    ensureDataDir();
    $file = __DIR__ . '/../data/zones.json';
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    return $data['zones'] ?? [];
}

/**
 * Получить зону по ID
 * @param string $id ID зоны
 * @return array|null Данные зоны или null
 */
function getZoneById($id) {
    $zones = getZones();
    foreach ($zones as $zone) {
        if ($zone['id'] === $id) {
            return $zone;
        }
    }
    return null;
}

// ------------------- РАБОТА С ТИПАМИ БИЛЕТОВ -------------------

/**
 * Получить все типы билетов из JSON
 * @return array Массив типов билетов
 */
function getTicketTypes() {
    ensureDataDir();
    $file = __DIR__ . '/../data/tickets.json';
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    return $data['tickets'] ?? [];
}

/**
 * Получить тип билета по ID
 * @param string $id ID типа билета
 * @return array|null Данные билета или null
 */
function getTicketTypeById($id) {
    $tickets = getTicketTypes();
    foreach ($tickets as $ticket) {
        if ($ticket['id'] === $id) {
            return $ticket;
        }
    }
    return null;
}

// ------------------- РАБОТА С ОПЛАТОЙ -------------------

/**
 * Получить способы оплаты из JSON
 * @return array Массив способов оплаты
 */
function getPaymentMethods() {
    ensureDataDir();
    $file = __DIR__ . '/../data/payment_methods.json';
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    return $data['payment_methods'] ?? [];
}

/**
 * Валидация номера карты по алгоритму Луна
 * @param string $number Номер карты
 * @return bool true если валиден
 */
function validateCardNumber($number) {
    // Удаляем пробелы и дефисы
    $number = preg_replace('/[\s-]/', '', $number);
    
    // Проверяем что только цифры и длина от 13 до 19
    if (!preg_match('/^\d{13,19}$/', $number)) {
        return false;
    }
    
    // Алгоритм Луна
    $sum = 0;
    $length = strlen($number);
    $parity = $length % 2;
    
    for ($i = 0; $i < $length; $i++) {
        $digit = (int)$number[$i];
        
        if ($i % 2 === $parity) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        
        $sum += $digit;
    }
    
    return ($sum % 10) === 0;
}

/**
 * Валидация срока действия карты
 * @param string $expiry Срок в формате MM/YY или MM/YYYY
 * @return bool true если валиден
 */
function validateCardExpiry($expiry) {
    if (!preg_match('/^(\d{1,2})\/(\d{2}|\d{4})$/', $expiry, $matches)) {
        return false;
    }
    
    $month = (int)$matches[1];
    $year = (int)$matches[2];
    
    // Если год в формате YY, добавляем 2000
    if ($year < 100) {
        $year += 2000;
    }
    
    // Проверяем месяц
    if ($month < 1 || $month > 12) {
        return false;
    }
    
    // Проверяем что карта не просрочена
    $currentYear = (int)date('Y');
    $currentMonth = (int)date('m');
    
    if ($year < $currentYear || ($year === $currentYear && $month < $currentMonth)) {
        return false;
    }
    
    return true;
}

/**
 * Валидация CVV кода
 * @param string $cvv CVV код
 * @return bool true если валиден
 */
function validateCardCVV($cvv) {
    return preg_match('/^\d{3,4}$/', $cvv);
}

/**
 * Обновить статус бронирования
 * @param int $id ID бронирования
 * @param string $user_login Логин пользователя
 * @param string $status Новый статус (pending, paid, cancelled)
 * @return bool true если успешно
 */
function updateItemStatus($id, $user_login, $status) {
    $file = __DIR__ . '/../data/items.txt';
    if (!file_exists($file)) return false;
    
    $lines = file($file);
    $fp = fopen($file, 'w');
    flock($fp, LOCK_EX);
    $updated = false;
    
    foreach ($lines as $line) {
        $parts = explode('|', trim($line));
        if ($parts[0] == $id && $parts[1] === $user_login) {
            // Формат: id|user_login|title|description|visit_date|created_at|price|status|ticket_type
            $newLine = $parts[0] . '|' . $parts[1] . '|' . $parts[2] . '|' . 
                       ($parts[3] ?? '') . '|' . ($parts[4] ?? '') . '|' . 
                       ($parts[5] ?? '') . '|' . ($parts[6] ?? '0') . '|' . 
                       $status . '|' . ($parts[8] ?? '') . "\n";
            fwrite($fp, $newLine);
            $updated = true;
        } else {
            fwrite($fp, $line);
        }
    }
    
    flock($fp, LOCK_UN);
    fclose($fp);
    return $updated;
}

// ------------------- СТАТИСТИКА ПОЛЬЗОВАТЕЛЯ -------------------

/**
 * Получить статистику пользователя для личного кабинета
 * @param string $user_login Логин пользователя
 * @return array Массив со статистикой
 */
function getUserStats($user_login) {
    $items = getAllItemsWithPrice($user_login);
    
    $totalTickets = count($items);
    $totalSpent = 0;
    $paidTickets = 0;
    $zoneVisits = [];
    
    foreach ($items as $item) {
        if (isset($item['status']) && $item['status'] === 'paid') {
            $totalSpent += (int)($item['price'] ?? 0);
            $paidTickets++;
        }
        
        // Анализ любимой зоны по типу билета
        if (isset($item['ticket_type'])) {
            $ticketType = $item['ticket_type'];
            if (!isset($zoneVisits[$ticketType])) {
                $zoneVisits[$ticketType] = 0;
            }
            $zoneVisits[$ticketType]++;
        }
    }
    
    // Определяем любимую зону
    $favoriteZone = null;
    $maxVisits = 0;
    foreach ($zoneVisits as $zone => $count) {
        if ($count > $maxVisits) {
            $maxVisits = $count;
            $favoriteZone = $zone;
        }
    }
    
    return [
        'total_tickets' => $totalTickets,
        'paid_tickets' => $paidTickets,
        'total_spent' => $totalSpent,
        'favorite_zone' => $favoriteZone,
        'zone_visits' => $zoneVisits
    ];
}

/**
 * Получить все бронирования пользователя с ценой
 * @param string $user_login Логин пользователя
 * @return array Массив бронирований
 */
function getAllItemsWithPrice($user_login) {
    ensureDataDir();
    $items = [];
    $file = __DIR__ . '/../data/items.txt';
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 6 && $parts[1] === $user_login) {
                $items[] = [
                    'id' => $parts[0],
                    'user_login' => $parts[1],
                    'title' => $parts[2],
                    'description' => $parts[3] ?? '',
                    'visit_date' => $parts[4] ?? '',
                    'created_at' => $parts[5] ?? '',
                    'price' => $parts[6] ?? '0',
                    'status' => $parts[7] ?? 'pending',
                    'ticket_type' => $parts[8] ?? ''
                ];
            }
        }
        usort($items, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
    }
    return $items;
}

/**
 * Получить бронирование по ID с ценой
 * @param int $id ID бронирования
 * @param string $user_login Логин пользователя
 * @return array|null Данные бронирования или null
 */
function getItemByIdWithPrice($id, $user_login) {
    $file = __DIR__ . '/../data/items.txt';
    if (!file_exists($file)) return null;
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if ($parts[0] == $id && $parts[1] === $user_login) {
            return [
                'id' => $parts[0],
                'user_login' => $parts[1],
                'title' => $parts[2],
                'description' => $parts[3] ?? '',
                'visit_date' => $parts[4] ?? '',
                'created_at' => $parts[5] ?? '',
                'price' => $parts[6] ?? '0',
                'status' => $parts[7] ?? 'pending',
                'ticket_type' => $parts[8] ?? ''
            ];
        }
    }
    return null;
}

/**
 * Сохранить бронирование с ценой
 * @param string $user_login Логин пользователя
 * @param array $data Данные бронирования
 * @return int ID созданного бронирования
 */
function saveItemWithPrice($user_login, $data) {
    ensureDataDir();
    $id = getNextId();
    $line = $id . '|' . $user_login . '|' . $data['title'] . '|' . 
            $data['description'] . '|' . $data['visit_date'] . '|' . 
            date('Y-m-d H:i:s') . '|' . ($data['price'] ?? '0') . '|' . 
            ($data['status'] ?? 'pending') . '|' . ($data['ticket_type'] ?? '') . "\n";
    file_put_contents(__DIR__ . '/../data/items.txt', $line, FILE_APPEND | LOCK_EX);
    return $id;
}

/**
 * Обновить бронирование с ценой
 * @param int $id ID бронирования
 * @param string $user_login Логин пользователя
 * @param array $data Данные для обновления
 * @return bool true если успешно
 */
function updateItemWithPrice($id, $user_login, $data) {
    $file = __DIR__ . '/../data/items.txt';
    $lines = file($file);
    $fp = fopen($file, 'w');
    flock($fp, LOCK_EX);
    $updated = false;
    foreach ($lines as $line) {
        $parts = explode('|', trim($line));
        if ($parts[0] == $id && $parts[1] === $user_login) {
            $newLine = $id . '|' . $user_login . '|' . $data['title'] . '|' . 
                       $data['description'] . '|' . $data['visit_date'] . '|' . 
                       $parts[5] . '|' . ($data['price'] ?? $parts[6] ?? '0') . '|' . 
                       ($data['status'] ?? $parts[7] ?? 'pending') . '|' . 
                       ($data['ticket_type'] ?? $parts[8] ?? '') . "\n";
            fwrite($fp, $newLine);
            $updated = true;
        } else {
            fwrite($fp, $line);
        }
    }
    flock($fp, LOCK_UN);
    fclose($fp);
    return $updated;
}

// ------------------- РАБОТА С НОВОСТЯМИ И ОТЗЫВАМИ -------------------

/**
 * Получить новости из JSON
 * @param int $limit Ограничение количества
 * @return array Массив новостей
 */
function getNews($limit = null) {
    ensureDataDir();
    $file = __DIR__ . '/../data/news.json';
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    $news = $data['news'] ?? [];
    
    // Сортируем по дате (новые первыми)
    usort($news, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    if ($limit !== null) {
        $news = array_slice($news, 0, $limit);
    }
    
    return $news;
}

/**
 * Получить отзывы из JSON
 * @param int $limit Ограничение количества
 * @return array Массив отзывов
 */
function getReviews($limit = null) {
    ensureDataDir();
    $file = __DIR__ . '/../data/reviews.json';
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    $reviews = $data['reviews'] ?? [];
    
    // Сортируем по дате (новые первыми)
    usort($reviews, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    if ($limit !== null) {
        $reviews = array_slice($reviews, 0, $limit);
    }
    
    return $reviews;
}

// ------------------- ДОСТИЖЕНИЯ ПОЛЬЗОВАТЕЛЯ -------------------

/**
 * Получить достижения пользователя
 * @param string $user_login Логин пользователя
 * @return array Массив достижений
 */
function getUserAchievements($user_login) {
    ensureDataDir();
    $file = __DIR__ . '/../data/achievements_' . $user_login . '.txt';
    
    $achievements = [
        'first_visit' => false,
        'five_tickets' => false,
        'ten_tickets' => false,
        'family_fan' => false,
        'extreme_lover' => false,
        'big_spender' => false,
        'season_pass' => false
    ];
    
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 2) {
                $achievements[$parts[0]] = $parts[1] === 'true';
            }
        }
    }
    
    return $achievements;
}

/**
 * Сохранить достижение пользователя
 * @param string $user_login Логин пользователя
 * @param string $achievement ID достижения
 * @param bool $value Значение
 */
function saveUserAchievement($user_login, $achievement, $value = true) {
    ensureDataDir();
    $achievements = getUserAchievements($user_login);
    $achievements[$achievement] = $value;
    
    $lines = [];
    foreach ($achievements as $key => $val) {
        $lines[] = $key . '|' . ($val ? 'true' : 'false');
    }
    
    file_put_contents(
        __DIR__ . '/../data/achievements_' . $user_login . '.txt', 
        implode("\n", $lines) . "\n"
    );
}

/**
 * Проверить и обновить достижения пользователя
 * @param string $user_login Логин пользователя
 */
function checkAndUpdateAchievements($user_login) {
    $stats = getUserStats($user_login);
    $items = getAllItemsWithPrice($user_login);
    
    // Первое бронирование
    if (count($items) >= 1) {
        saveUserAchievement($user_login, 'first_visit', true);
    }
    
    // 5 билетов
    if (count($items) >= 5) {
        saveUserAchievement($user_login, 'five_tickets', true);
    }
    
    // 10 билетов
    if (count($items) >= 10) {
        saveUserAchievement($user_login, 'ten_tickets', true);
    }
    
    // Потратил более 10000
    if ($stats['total_spent'] >= 10000) {
        saveUserAchievement($user_login, 'big_spender', true);
    }
    
    // Сезонный абонемент
    foreach ($items as $item) {
        if ($item['ticket_type'] === 'season') {
            saveUserAchievement($user_login, 'season_pass', true);
            break;
        }
    }
    
    // Любитель семейной зоны
    if (isset($stats['zone_visits']['family']) && $stats['zone_visits']['family'] >= 3) {
        saveUserAchievement($user_login, 'family_fan', true);
    }
    
    // Любитель экстрима
    if (isset($stats['zone_visits']['extreme']) && $stats['zone_visits']['extreme'] >= 3) {
        saveUserAchievement($user_login, 'extreme_lover', true);
    }
}

/**
 * Получить информацию о достижении
 * @param string $id ID достижения
 * @return array Информация о достижении
 */
function getAchievementInfo($id) {
    $achievements = [
        'first_visit' => ['name' => 'Первый визит', 'icon' => '🎉', 'description' => 'Забронировали первый билет'],
        'five_tickets' => ['name' => 'Постоянный гость', 'icon' => '⭐', 'description' => 'Купили 5 билетов'],
        'ten_tickets' => ['name' => 'Супер-гость', 'icon' => '🌟', 'description' => 'Купили 10 билетов'],
        'family_fan' => ['name' => 'Семейный фанат', 'icon' => '👨‍👩‍👧‍👦', 'description' => '3+ посещения семейной зоны'],
        'extreme_lover' => ['name' => 'Любитель экстрима', 'icon' => '🎢', 'description' => '3+ посещения зоны экстрим'],
        'big_spender' => ['name' => 'Щедрый гость', 'icon' => '💎', 'description' => 'Потратили более 10 000 ₽'],
        'season_pass' => ['name' => 'Сезонный гость', 'icon' => '🏆', 'description' => 'Купили сезонный абонемент']
    ];
    
    return $achievements[$id] ?? ['name' => $id, 'icon' => '🏅', 'description' => ''];
}
?>