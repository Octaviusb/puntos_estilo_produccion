document.getElementById('login-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
  
    try {
      const response = await fetch('http://localhost:3000/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          email: email,
          password: password
        })
      });
  
      if (!response.ok) {
        throw new Error('Error logging in: ' + response.statusText);
      }
  
      const result = await response.text();
      if (result.includes('error')) {
        window.location.href = '/login.html?error=Credenciales%20incorrectas';
      } else {
        window.location.href = '/dashboard.html';
      }
    } catch (error) {
      console.error('Error logging in:', error);
    }
  });
  