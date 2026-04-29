/**
 * Управление темой (светлая/тёмная)
 * Сохраняет выбор в localStorage
 */
(function() {
    'use strict';
    
    const STORAGE_KEY = 'funpark_theme';
    const root = document.documentElement;
    const THEME_DARK = 'dark';
    const THEME_LIGHT = 'light';
    
    /**
     * Применяет тему к странице
     * @param {boolean} isDark - true для тёмной темы
     */
    function applyTheme(isDark) {
        root.setAttribute('data-theme', isDark ? THEME_DARK : THEME_LIGHT);
        localStorage.setItem(STORAGE_KEY, isDark ? THEME_DARK : THEME_LIGHT);
        updateToggleButton(isDark);
    }
    
    /**
     * Обновляет иконку кнопки переключения темы
     * @param {boolean} isDark - текущая тема тёмная
     */
    function updateToggleButton(isDark) {
        const btn = document.getElementById('themeToggle');
        if (btn) {
            btn.textContent = isDark ? '☀️' : '🌙';
            btn.setAttribute('title', isDark ? 'Светлая тема' : 'Тёмная тема');
        }
    }
    
    /**
     * Переключает тему
     */
    function toggleTheme() {
        const currentTheme = root.getAttribute('data-theme');
        applyTheme(currentTheme !== THEME_DARK);
    }
    
    /**
     * Инициализация темы
     */
    function init() {
        // Проверяем сохранённую тему или системные настройки
        const savedTheme = localStorage.getItem(STORAGE_KEY);
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        const isDark = savedTheme === THEME_DARK || (!savedTheme && prefersDark);
        applyTheme(isDark);
        
        // Обработчик кнопки
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('themeToggle');
            if (btn) {
                btn.addEventListener('click', toggleTheme);
            }
        });
        
        // Слушаем изменения системной темы (если пользователь не выбрал свою)
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem(STORAGE_KEY)) {
                    applyTheme(e.matches);
                }
            });
        }
    }
    
    // Запускаем инициализацию
    init();
})();