const express = require('express');
const mysql = require('mysql2');
const path = require('path');
const helmet = require('helmet');
const app = express();
const port = 3000;

// Conexión a la base de datos
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: 'Obc19447/*',
  database: 'mi_proyecto'
});

db.connect(err => {
  if (err) {
    console.error('Error connecting to the database:', err);
    return;
  }
  console.log('Connected to the database');
});

// Usa helmet para configurar las cabeceras de seguridad
app.use(helmet());

// Configura Content Security Policy (CSP) para permitir fuentes de datos en línea
app.use(
  helmet.contentSecurityPolicy({
    directives: {
      defaultSrc: ["'self'"],
      scriptSrc: ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
      styleSrc: ["'self'", "'unsafe-inline'"],
      fontSrc: ["'self'", 'data:'],
      imgSrc: ["'self'", 'data:', 'https:'],
      connectSrc: ["'self'"],
      frameSrc: ["'self'"]
    },
  })
);

// Servir archivos estáticos desde la carpeta 'frontend'
app.use(express.static(path.join(__dirname, '../frontend')));

// Redirigir la ruta principal a login.html
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, '../frontend', 'login.html'));
});

// Middleware para parsear JSON y URL-encoded bodies
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Ruta para el login
app.post('/login', (req, res) => {
  const email = req.body.email;
  const password = req.body.password;

  const query = 'SELECT * FROM usuarios WHERE correo = ? AND contraseña = ?';
  db.query(query, [email, password], (err, result) => {
    if (err) {
      console.error('Error executing query:', err);
      return res.status(500).send('Error en el servidor');
    }
    if (result.length > 0) {
      // Aquí manejas el inicio de sesión exitoso
      res.redirect('/index.html');
    } else {
      res.redirect('/login.html?error=Credenciales%20incorrectas');
    }
  });
});

// Iniciar el servidor
app.listen(port, () => {
  console.log(`Server running on http://localhost:${port}`);
});
