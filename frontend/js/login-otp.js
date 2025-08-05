// JS para login con OTP

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const loginBtn = document.getElementById('login-btn');
    const otpBtn = document.getElementById('otp-btn');
    const otpGroup = document.getElementById('otp-group');
    const errorMessage = document.getElementById('error-message');
    const otpDisplay = document.getElementById('otp-display');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const otpInput = document.getElementById('otp');

    let isOtpRequested = false;

    // Función para mostrar errores
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.add('show');
        setTimeout(() => {
            errorMessage.classList.remove('show');
        }, 5000);
    }

    // Función para mostrar OTP
    function showOTP(otp) {
        otpDisplay.textContent = `Tu código OTP es: ${otp}`;
        otpDisplay.style.display = 'block';
        setTimeout(() => {
            otpDisplay.style.display = 'none';
        }, 30000); // Ocultar después de 30 segundos
    }

    // Función para mostrar loading
    function setLoading(button, isLoading) {
        if (isLoading) {
            button.classList.add('loading');
            button.disabled = true;
        } else {
            button.classList.remove('loading');
            button.disabled = false;
        }
    }

    // Función para validar email
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Manejar envío del formulario
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();
        const otp = otpInput.value.trim();

        // Validaciones básicas
        if (!email || !password) {
            showError('Por favor, complete todos los campos.');
            return;
        }

        if (!isValidEmail(email)) {
            showError('Por favor, ingrese un email válido.');
            return;
        }

        if (!isOtpRequested) {
            // Solicitar OTP
            await requestOTP(email, password);
        } else {
            // Validar OTP
            if (!otp || otp.length !== 6) {
                showError('Por favor, ingrese el código OTP de 6 dígitos.');
                return;
            }
            await validateOTP(email, password, otp);
        }
    });

    // Función para solicitar OTP
    async function requestOTP(email, password) {
        setLoading(loginBtn, true);
        
        try {
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('action', 'request_otp');

            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Mostrar campo OTP
                otpGroup.classList.add('show');
                loginBtn.style.display = 'none';
                otpBtn.style.display = 'block';
                isOtpRequested = true;
                
                // Mostrar OTP en el navegador (modo desarrollo)
                if (data.otp) {
                    showOTP(data.otp);
                }
                
                // Mostrar mensaje de éxito
                errorMessage.style.background = 'var(--success-color)';
                errorMessage.textContent = 'Código OTP enviado. Revisa el código mostrado arriba.';
                errorMessage.classList.add('show');
                
                // Limpiar mensaje después de 5 segundos
                setTimeout(() => {
                    errorMessage.classList.remove('show');
                    errorMessage.style.background = 'var(--error-color)';
                }, 5000);
                
                // Enfocar campo OTP
                otpInput.focus();
            } else {
                showError(data.message || 'Error al solicitar OTP. Verifica tus credenciales.');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Error de conexión. Intenta nuevamente.');
        } finally {
            setLoading(loginBtn, false);
        }
    }

    // Función para validar OTP
    async function validateOTP(email, password, otp) {
        setLoading(otpBtn, true);
        
        try {
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('otp', otp);
            formData.append('action', 'validate_otp');

            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Redirigir según el rol
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.href = 'dashboard.php';
                }
            } else {
                showError(data.message || 'Código OTP inválido. Intenta nuevamente.');
                otpInput.value = '';
                otpInput.focus();
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Error de conexión. Intenta nuevamente.');
        } finally {
            setLoading(otpBtn, false);
        }
    }

    // Botón para validar OTP
    otpBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();
        const otp = otpInput.value.trim();

        if (!otp || otp.length !== 6) {
            showError('Por favor, ingrese el código OTP de 6 dígitos.');
            return;
        }

        await validateOTP(email, password, otp);
    });

    // Validación en tiempo real del email
    emailInput.addEventListener('input', function() {
        const email = this.value.trim();
        if (email && !isValidEmail(email)) {
            this.style.borderColor = 'var(--error-color)';
        } else {
            this.style.borderColor = 'var(--border-color)';
        }
    });

    // Validación en tiempo real del OTP
    otpInput.addEventListener('input', function() {
        const otp = this.value.trim();
        if (otp && otp.length !== 6) {
            this.style.borderColor = 'var(--error-color)';
        } else {
            this.style.borderColor = 'var(--border-color)';
        }
    });

    // Permitir solo números en el campo OTP
    otpInput.addEventListener('keypress', function(e) {
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
    });

    // Limitar OTP a 6 dígitos
    otpInput.addEventListener('input', function() {
        if (this.value.length > 6) {
            this.value = this.value.slice(0, 6);
        }
    });

    // Enfocar email al cargar la página
    emailInput.focus();
}); 