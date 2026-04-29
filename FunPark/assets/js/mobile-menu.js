/**
 * Управление мобильным меню (бургер-меню)
 */
(function() {
    'use strict';
    
    const burger = document.getElementById('burger');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuClose = document.getElementById('mobileMenuClose');
    const body = document.body;
    
    // Если элементы не найдены, выходим
    if (!burger || !mobileMenu) return;
    
    /**
     * Открывает мобильное меню
     */
    function openMenu() {
        mobileMenu.classList.add('active');
        burger.classList.add('active');
        body.style.overflow = 'hidden';
        
        // Фокус на кнопку закрытия для доступности
        if (mobileMenuClose) {
            mobileMenuClose.focus();
        }
    }
    
    /**
     * Закрывает мобильное меню
     */
    function closeMenu() {
        mobileMenu.classList.remove('active');
        burger.classList.remove('active');
        body.style.overflow = '';
        
        // Возвращаем фокус на бургер
        burger.focus();
    }
    
    /**
     * Переключает состояние меню
     */
    function toggleMenu() {
        if (mobileMenu.classList.contains('active')) {
            closeMenu();
        } else {
            openMenu();
        }
    }
    
    // Обработчик клика по бургеру
    burger.addEventListener('click', toggleMenu);
    
    // Обработчик кнопки закрытия
    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', closeMenu);
    }
    
    // Закрытие при клике на ссылку в меню
    const mobileLinks = mobileMenu.querySelectorAll('.mobile-nav-link');
    mobileLinks.forEach(function(link) {
        link.addEventListener('click', closeMenu);
    });
    
    // Закрытие при клике вне меню
    document.addEventListener('click', function(e) {
        if (mobileMenu.classList.contains('active')) {
            if (!mobileMenu.contains(e.target) && !burger.contains(e.target)) {
                closeMenu();
            }
        }
    });
    
    // Закрытие при нажатии Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
            closeMenu();
        }
    });
    
    // Закрытие при изменении размера окна
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && mobileMenu.classList.contains('active')) {
            closeMenu();
        }
    });
})();