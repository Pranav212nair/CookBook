document.getElementById('signup-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const username = document.getElementById('signup-username').value.trim();
    const password = document.getElementById('signup-password').value;
    const messageDiv = document.getElementById('signup-message');
    messageDiv.textContent = '';
    try {
        const res = await fetch('http://localhost:3001/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const data = await res.json();
        if (res.ok) {
            messageDiv.textContent = 'Signup successful! Redirecting to login...';
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 1000);
        } else {
            messageDiv.textContent = data.error || 'Signup failed.';
        }
    } catch (err) {
        messageDiv.textContent = 'Network error.';
    }
});
