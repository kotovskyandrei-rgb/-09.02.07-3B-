<?php
/**
 * Удаление бронирования
 */
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$id = (int)($_GET['id'] ?? 0);
deleteItem($id, $_SESSION['user_login']);

redirect('items.php?deleted=1');