// scripts.js
document.addEventListener('DOMContentLoaded', function() {
    // Ejemplo de cómo agregar un producto al catálogo
    const productos = [
        { nombre: "Producto Mercado", categoria: "Mercado", precio: "$100", puntos: 100 },
        { nombre: "Boleta Cine", categoria: "Entretenimiento", precio: "$50", puntos: 50 },
        // Agrega más productos aquí
    ];

    const contenedorProductos = document.querySelector('#productos');
    const puntosElemento = document.getElementById('puntos');
    const puntosItems = document.querySelectorAll('.puntos-item');

    // Obtener puntos disponibles del localStorage o inicializar a 0
    let puntosDisponibles = parseInt(localStorage.getItem('puntosDisponibles')) || 0;
    puntosElemento.textContent = puntosDisponibles;

    productos.forEach(producto => {
        const card = document.createElement('div');
        card.className = 'producto';
        card.innerHTML = `
            <h3>${producto.nombre}</h3>
            <p>${producto.categoria}</p>
            <p>${producto.precio}</p>
            <p>Puntos: ${producto.puntos}</p>
            <button class="canje-button" data-points="${producto.puntos}">Canjear</button>
        `;
        contenedorProductos.appendChild(card);
    });

    document.querySelectorAll('.canje-button').forEach(button => {
        button.addEventListener('click', function () {
            const puntosRequeridos = parseInt(this.dataset.points);

            if (puntosDisponibles >= puntosRequeridos) {
                const confirmacion = confirm(`¿Deseas canjear ${puntosRequeridos} puntos por este producto?`);
                if (confirmacion) {
                    puntosDisponibles -= puntosRequeridos;
                    localStorage.setItem('puntosDisponibles', puntosDisponibles);
                    puntosElemento.textContent = puntosDisponibles;
                    alert('¡Canje realizado con éxito!');
                }
            } else {
                alert('No tienes suficientes puntos para realizar este canje.');
            }
        });
    });

    puntosItems.forEach(item => {
        item.addEventListener('click', function () {
            const puntos = parseInt(this.dataset.puntos);
            puntosDisponibles += puntos;
            localStorage.setItem('puntosDisponibles', puntosDisponibles);
            puntosElemento.textContent = puntosDisponibles;
            alert(`¡Has ganado ${puntos} puntos!`);
        });
    });
});

