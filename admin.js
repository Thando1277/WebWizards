const container = document.querySelector('.container');
const LoginLink = document.querySelector('.SignIn');
const LoginRegister = document.querySelector('.SignUp');

LoginRegister.addEventListener('click', () => {
    container.classList.add('active');
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".container").classList.add("active");
});

// VALIDATIONS
document.getElementById('adminForm').addEventListener('submit', function(e){
    e.preventDefault(); // Prevents the form from submitting immediately
    
    const username = document.getElementById('usernameInput').value.trim();
    const password = document.getElementById('passwordInput').value.trim();
    const usernameErrorEl = document.getElementById('usernameError');
    const passwordErrorEl = document.getElementById('passwordError');

    usernameErrorEl.textContent = "";
    passwordErrorEl.textContent = "";

    // Validation checks
    if (username === "") {
        usernameErrorEl.textContent = "Username required";
        return;
    }
    if (password.length < 6) {
        passwordErrorEl.textContent = "Password must have at least 6 characters";
        return;
    }

    // If the validation passes, allow the form to submit (remove preventDefault)
    // Manually trigger form submission after validation
    this.submit();  // This submits the form to adminLogin.php
});
