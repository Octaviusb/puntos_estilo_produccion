// Funcionalidad para la página de perfil

document.addEventListener('DOMContentLoaded', function() {
    
    // Manejo de carga de avatar
    const avatarUpload = document.getElementById('avatar-upload');
    const avatar = document.querySelector('.avatar');
    const editIcon = document.querySelector('.edit-icon');
    
    if (avatarUpload && avatar) {
        avatarUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tipo de archivo
                if (!file.type.startsWith('image/')) {
                    alert('Por favor selecciona una imagen válida');
                    return;
                }
                
                // Validar tamaño (máximo 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen debe ser menor a 5MB');
                    return;
                }
                
                // Mostrar preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatar.src = e.target.result;
                };
                reader.readAsDataURL(file);
                
                // Aquí podrías enviar la imagen al servidor
                uploadAvatar(file);
            }
        });
        
        // Hacer clic en el ícono de edición
        if (editIcon) {
            editIcon.addEventListener('click', function() {
                avatarUpload.click();
            });
        }
    }
    
    // Función para subir avatar al servidor
    function uploadAvatar(file) {
        const formData = new FormData();
        formData.append('avatar', file);
        
        fetch('upload_avatar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Avatar actualizado correctamente');
            } else {
                console.error('Error al actualizar avatar:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    // Resaltar enlace activo en el menú
    const currentPage = window.location.pathname.split('/').pop();
    const menuLinks = document.querySelectorAll('.sidebar-menu a');
    
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
    
    // Animaciones para las tarjetas
    const cards = document.querySelectorAll('.card, .benefit-card');
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
    
    // Contador animado para los puntos
    const pointsElement = document.querySelector('.points');
    if (pointsElement) {
        const finalPoints = parseInt(pointsElement.textContent.replace(/\D/g, ''));
        let currentPoints = 0;
        const increment = finalPoints / 50; // 50 pasos para la animación
        
        const counter = setInterval(() => {
            currentPoints += increment;
            if (currentPoints >= finalPoints) {
                currentPoints = finalPoints;
                clearInterval(counter);
            }
            pointsElement.textContent = Math.floor(currentPoints).toLocaleString() + ' pts';
        }, 50);
    }
    
    // Tooltip para las tarjetas de beneficios
    const benefitCards = document.querySelectorAll('.benefit-card');
    benefitCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
        
        // Hacer clic en la tarjeta para ir al catálogo
        card.addEventListener('click', function() {
            window.location.href = 'catalogo.php';
        });
    });
    
    // Notificaciones toast para acciones
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#4caf50' : type === 'error' ? '#f44336' : '#2196f3'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        // Animar entrada
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
    
    // Exponer función globalmente
    window.showToast = showToast;
}); 