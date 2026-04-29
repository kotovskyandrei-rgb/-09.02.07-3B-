/**
 * Payment.js - скрипты для страницы оплаты
 */

document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('.payment-method');
    const cardForm = document.getElementById('cardForm');
    const payBtn = document.getElementById('payBtn');
    const paymentModal = document.getElementById('paymentModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalText = document.getElementById('modalText');
    const progressBar = document.getElementById('progressBar');
    const timerValue = document.getElementById('timerValue');
    const commissionRow = document.getElementById('commissionRow');
    const commissionAmount = document.getElementById('commissionAmount');
    const totalPrice = document.getElementById('totalPrice');
    
    const cardNumber = document.getElementById('card_number');
    const cardExpiry = document.getElementById('card_expiry');
    const cardCvv = document.getElementById('card_cvv');
    const cardHolder = document.getElementById('card_holder');
    const cardType = document.getElementById('cardType');
    
    let selectedMethod = 'card';
    let basePrice = bookingData.price;
    
    // Выбор способа оплаты
    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            paymentMethods.forEach(m => m.classList.remove('payment-method--active'));
            this.classList.add('payment-method--active');
            
            selectedMethod = this.dataset.method;
            
            // Показываем/скрываем форму карты
            if (selectedMethod === 'card') {
                cardForm.style.display = 'block';
            } else {
                cardForm.style.display = 'none';
            }
            
            // Обновляем комиссию
            updateTotal();
        });
    });
    
    // Устанавливаем начальное состояние
    document.querySelector('.payment-method[data-method="card"]').classList.add('payment-method--active');
    
    // Форматирование номера карты
    cardNumber.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        let formatted = '';
        
        for (let i = 0; i < value.length && i < 16; i++) {
            if (i > 0 && i % 4 === 0) {
                formatted += ' ';
            }
            formatted += value[i];
        }
        
        e.target.value = formatted;
        
        // Определяем тип карты
        const firstDigits = value.substring(0, 4);
        if (value.startsWith('4')) {
            cardType.textContent = 'Visa';
            cardType.style.color = '#1a1f71';
        } else if (value.startsWith('5') || value.startsWith('2')) {
            cardType.textContent = 'MasterCard';
            cardType.style.color = '#eb001b';
        } else if (value.startsWith('220')) {
            cardType.textContent = 'МИР';
            cardType.style.color = '#00a0df';
        } else {
            cardType.textContent = '';
        }
    });
    
    // Форматирование срока действия
    cardExpiry.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        
        e.target.value = value;
    });
    
    // Только цифры для CVV
    cardCvv.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
    });
    
    // Имя заглавными буквами
    cardHolder.addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });
    
    // Обновление итоговой суммы
    function updateTotal() {
        const method = bookingData.paymentMethods.find(m => m.id === selectedMethod);
        const commissionPercent = method ? method.commission : 0;
        const commissionValue = Math.round(basePrice * commissionPercent / 100);
        const total = basePrice + commissionValue;
        
        if (commissionValue > 0) {
            commissionRow.style.display = 'flex';
            commissionAmount.textContent = formatPrice(commissionValue) + ' ₽';
        } else {
            commissionRow.style.display = 'none';
        }
        
        totalPrice.textContent = formatPrice(total) + ' ₽';
    }
    
    // Форматирование цены
    function formatPrice(price) {
        return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }
    
    // Валидация карты (алгоритм Луна)
    function luhnCheck(cardNum) {
        const digits = cardNum.replace(/\s/g, '');
        if (!/^\d{13,19}$/.test(digits)) return false;
        
        let sum = 0;
        const parity = digits.length % 2;
        
        for (let i = 0; i < digits.length; i++) {
            let digit = parseInt(digits[i]);
            
            if (i % 2 === parity) {
                digit *= 2;
                if (digit > 9) digit -= 9;
            }
            
            sum += digit;
        }
        
        return sum % 10 === 0;
    }
    
    // Валидация срока действия
    function validateExpiry(expiry) {
        const match = expiry.match(/^(\d{2})\/(\d{2})$/);
        if (!match) return false;
        
        const month = parseInt(match[1]);
        const year = parseInt('20' + match[2]);
        
        if (month < 1 || month > 12) return false;
        
        const now = new Date();
        const currentYear = now.getFullYear();
        const currentMonth = now.getMonth() + 1;
        
        if (year < currentYear || (year === currentYear && month < currentMonth)) {
            return false;
        }
        
        return true;
    }
    
    // Обработка оплаты
    payBtn.addEventListener('click', async function() {
        // Валидация для карты
        if (selectedMethod === 'card') {
            const cardNum = cardNumber.value;
            const expiry = cardExpiry.value;
            const cvv = cardCvv.value;
            const holder = cardHolder.value.trim();
            
            if (!luhnCheck(cardNum)) {
                showError('Неверный номер карты');
                return;
            }
            
            if (!validateExpiry(expiry)) {
                showError('Неверный срок действия или карта просрочена');
                return;
            }
            
            if (!/^\d{3,4}$/.test(cvv)) {
                showError('Неверный CVV код');
                return;
            }
            
            if (holder.length < 3) {
                showError('Введите имя владельца карты');
                return;
            }
        }
        
        // Показываем модальное окно
        showPaymentModal();
        
        try {
            // Подготовка данных
            const requestData = {
                booking_id: bookingData.id,
                payment_method: selectedMethod,
                card_data: selectedMethod === 'card' ? {
                    number: cardNumber.value.replace(/\s/g, ''),
                    expiry: cardExpiry.value,
                    cvv: cardCvv.value,
                    holder: cardHolder.value
                } : null
            };
            
            // Отправка запроса
            const response = await fetch('payment_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            });
            
            const result = await response.json();
            
            // Имитация задержки обработки
            await simulateProgress(5);
            
            if (result.success) {
                showSuccess(result.message);
                setTimeout(() => {
                    window.location.href = 'items.php?paid=1';
                }, 2000);
            } else {
                hideModal();
                showError(result.error || 'Ошибка оплаты');
            }
            
        } catch (error) {
            hideModal();
            showError('Ошибка соединения. Попробуйте позже.');
        }
    });
    
    // Показать модальное окно
    function showPaymentModal() {
        paymentModal.classList.add('active');
        modalTitle.textContent = 'Обработка платежа...';
        modalText.textContent = 'Пожалуйста, подождите';
        progressBar.style.width = '0%';
    }
    
    // Скрыть модальное окно
    function hideModal() {
        paymentModal.classList.remove('active');
    }
    
    // Имитация прогресса
    function simulateProgress(seconds) {
        return new Promise(resolve => {
            let elapsed = 0;
            const interval = 100;
            
            const timer = setInterval(() => {
                elapsed += interval / 1000;
                const progress = Math.min((elapsed / seconds) * 100, 100);
                progressBar.style.width = progress + '%';
                timerValue.textContent = Math.max(0, Math.ceil(seconds - elapsed));
                
                if (elapsed >= seconds) {
                    clearInterval(timer);
                    resolve();
                }
            }, interval);
        });
    }
    
    // Показать успех
    function showSuccess(message) {
        modalTitle.textContent = '✅ Оплата успешна!';
        modalText.textContent = message;
        progressBar.style.width = '100%';
        document.querySelector('.modal-timer').style.display = 'none';
    }
    
    // Показать ошибку
    function showError(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert error';
        alertDiv.textContent = message;
        
        const container = document.querySelector('.payment-form-container');
        const existingAlert = container.querySelector('.alert.error');
        
        if (existingAlert) {
            existingAlert.remove();
        }
        
        container.insertBefore(alertDiv, container.firstChild);
        
        // Автоскрытие через 5 секунд
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
});
