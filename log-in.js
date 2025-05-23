const container = document.querySelector('.container');
const LoginLink = document.querySelector('.SignIn');
const LoginRegister = document.querySelector('.SignUp');

LoginRegister.addEventListener('click', () => {
    container.classList.add('active');
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".container").classList.add("active");
});

document.getElementById('logInForm').addEventListener('submit', function (e) {
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
        usernameErrorEl.textContent = "Username is required";
        isValid = false;
    }

    if (password.length < 6) {
        passwordErrorEl.textContent = "Password must be at least 6 characters";
        isValid = false;
    }

    if (isValid) {
        fetch('log-in.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            } else if (data.error) {
                serverErrorEl.textContent = data.error;
            }
        })
        .catch(error => {
            serverErrorEl.textContent = "Something went wrong.";
            console.error("Error:", error);
        });
    }
});

const passwordInput = document.getElementById('passwordInput');
const togglePassword = document.getElementById('togglePassword');

togglePassword.addEventListener('click', () => {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    togglePassword.classList.toggle('bx-hide', !isPassword);
    togglePassword.classList.toggle('bx-show', isPassword);
});
