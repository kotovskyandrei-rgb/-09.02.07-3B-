<?php
/**
 * Выход из аккаунта
 */
require_once 'includes/functions.php';

logoutUser();

redirect('login.php?logout=1');