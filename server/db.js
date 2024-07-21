const mysql = require('mysql2');

const connection = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: 'Obc19447/*',
  database: 'mi_proyecto'
});

connection.connect(err => {
  if (err) {
    console.error('Error conectándose a la base de datos:', err.stack);
    return;
  }
  console.log('Conectado a la base de datos como id ' + connection.threadId);
});

module.exports = connection;
