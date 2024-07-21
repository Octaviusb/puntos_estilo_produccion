// scripts.js
document.addEventListener('DOMContentLoaded', function() {
    // Ejemplo de cómo agregar un producto al catálogo
    const productos = [
        { nombre: "Producto Mercado", categoria: "Mercado", precio: "$100" },
        { nombre: "Boleta Cine", categoria: "Entretenimiento", precio: "$50" },
        // Agrega más productos aquí
    ];

    const contenedorProductos = document.querySelector('#productos');

    productos.forEach(producto => {
        const card = document.createElement('div');
        card.className = 'producto';
        card.innerHTML = `
            <h3>${producto.nombre}</h3>
            <p>${producto.categoria}</p>
            <p>${producto.precio}</p>
        `;
        contenedorProductos.appendChild(card);
    });
});
