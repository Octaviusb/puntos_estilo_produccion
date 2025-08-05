<?php
session_start();
require_once '../server/config.php';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = filter_input(INPUT_POST, 'cliente', FILTER_SANITIZE_STRING);
    $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
    $celular = filter_input(INPUT_POST, 'celular', FILTER_SANITIZE_STRING);
    $mensaje = filter_input(INPUT_POST, 'mensaje', FILTER_SANITIZE_STRING);
    
    // Validar el correo electrónico
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, ingrese un correo electrónico válido.";
    } else {
        // Insertar el mensaje en la base de datos
        $sql = "INSERT INTO mensajes_contacto (nombre, correo, celular, mensaje, fecha) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $correo, $celular, $mensaje);
        
        if ($stmt->execute()) {
            $success = "Mensaje enviado correctamente. Nos pondremos en contacto contigo pronto.";
        } else {
            $error = "Error al enviar el mensaje. Por favor, intente nuevamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Puntos Estilo</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <link rel="stylesheet" href="css/contacto.css?v=3">
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    
    <main class="dashboard-container">
        <h2>Contacto</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="contact-form-container">
            <form action="contacto.php" id="form" method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" name="cliente" id="nombre" placeholder="Ingrese su Nombre" onkeypress="return sololetras(event)" onpaste="return false" required>
                </div>
                
                <div class="form-group">
                    <label for="correo">Correo Electrónico:</label>
                    <input type="email" name="correo" id="correo" placeholder="Ingrese su Correo" required>
                </div>
                
                <div class="form-group">
                    <label for="celular">Celular:</label>
                    <input type="tel" name="celular" id="celular" placeholder="Ingrese su Celular" onkeypress="return solonumeros(event)" onpaste="return false" required>
                </div>
                
                <div class="form-group">
                    <label for="mensaje">Mensaje:</label>
                    <textarea name="mensaje" id="mensaje" placeholder="Escriba su Mensaje" required></textarea>
                </div>
                
                <button type="submit" class="btn">Enviar Mensaje</button>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="js/contacto.js"></script>
    
    <style>
    .contact-form-container {
        max-width: 600px;
        margin: 0 auto;
        padding: 2rem;
        background: var(--white);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: bold;
        color: var(--text-color);
    }
    
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 1rem;
        border: 2px solid var(--border-color);
        border-radius: 4px;
        font-size: 1rem;
        transition: var(--transition);
        font-family: inherit;
        box-sizing: border-box;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }
    
    .form-group textarea {
        min-height: 120px;
        resize: vertical;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1.5rem;
        border-left: 4px solid;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border-color: #dc3545;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border-color: #28a745;
    }
    
    .btn-secondary {
        color: #222 !important;
    }
    button.btn, .btn {
        color: #fff !important;
        background: var(--primary-color) !important;
    }
    header h1 {
        color: var(--primary-color) !important;
    }
    </style>
</body>
</html> 