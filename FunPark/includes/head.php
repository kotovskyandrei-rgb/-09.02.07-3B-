<?php
// head.php - DOCTYPE, head, мета-теги, подключение стилей
// Требуется установить $pageTitle перед подключением
if (!isset($pageTitle)) $pageTitle = 'FunPark';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FunPark — парк развлечений. Бронируйте билеты онлайн и получайте яркие эмоции!">
    <meta name="keywords" content="парк развлечений, аттракционы, билеты, FunPark">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <title><?php echo e($pageTitle); ?> — FunPark</title>
</head>
<body>