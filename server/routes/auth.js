const express = require('express');
const router = express.Router();
const db = require('../db');

router.post('/login', (req, res) => {
  const email = req.body['login-email'];
  const password = req.body['login-password'];

  const query = 'SELECT * FROM usuarios WHERE correo = ? AND contraseña = ?';
  db.query(query, [email, password], (err, result) => {
    if (err) {
      console.error('Error executing query:', err);
      return res.status(500).send('Error en el servidor');
    }

    if (result.length > 0) {
      
      res.redirect('/index.html');
    } else {
      res.redirect('/login.html?error=Credenciales incorrectas');
    }
  });
});

module.exports = router;
