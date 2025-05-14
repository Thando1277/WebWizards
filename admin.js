const container=document.querySelector('.container');
const LoginLink=document.querySelector('.SignIn');
const LoginRegister=document.querySelector('.SignUp');
LoginRegister.addEventListener('click',()=>{
    container.classList.add('active');
})


document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".container").classList.add("active");
});

document.getElementById('adminForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const username = document.getElementById('usernameInput').value.trim();
    const password = document.getElementById('passwordInput').value.trim();
    const usernameErrorEl = document.getElementById('usernameError');
    const passwordErrorEl = document.getElementById('passwordError');
    const serverErrorEl = document.getElementById('serverError');

    // Clear all errors
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

    // Submit via AJAX
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
