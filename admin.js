document.addEventListener("DOMContentLoaded", function () {
    const container = document.querySelector('.container');
    const loginRegister = document.querySelector('.SignUp');

    container.classList.add("active");

    loginRegister?.addEventListener('click', () => {
        container.classList.add('active');
    });

    document.getElementById('adminForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const username = document.getElementById('usernameInput').value.trim();
        const password = document.getElementById('passwordInput').value.trim();
        const usernameErrorEl = document.getElementById('usernameError');
        const passwordErrorEl = document.getElementById('passwordError');
        const serverErrorEl = document.getElementById('serverError');
        usernameErrorEl.textContent = "";
        passwordErrorEl.textContent = "";
        serverErrorEl.textContent = "";

        let isValid = true;

        if (username === "") {
            usernameErrorEl.textContent = "Username required";
            isValid = false;
        }

        if (password.length < 6) {
            passwordErrorEl.textContent = "Password must have at least 6 characters";
            isValid = false;
        }

        if (!isValid) return;

        fetch('adminLogin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = "dashboard.html";
            } else if (data.error) {
                serverErrorEl.textContent = data.error;
            }
        })
        .catch(() => {
            serverErrorEl.textContent = "Something went wrong. Please try again later.";
        });
    });
});
const passwordInput = document.getElementById('passwordInput');
const togglePassword = document.getElementById('togglePassword');

togglePassword.addEventListener('click', () => {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    togglePassword.classList.toggle('bx-hide', !isPassword);
    togglePassword.classList.toggle('bx-show', isPassword);
});
