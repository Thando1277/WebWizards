const container = document.querySelector('.container');
const LoginLink = document.querySelector('.SignIn');
const LoginRegister = document.querySelector('.SignUp');

LoginRegister.addEventListener('click', () => {
    container.classList.add('active');
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".container").classList.add("active");
});


document.getElementById('adminForm').addEventListener('submit', function(e){
    e.preventDefault(); 
    
    const username = document.getElementById('usernameInput').value.trim();
    const password = document.getElementById('passwordInput').value.trim();
    const usernameErrorEl = document.getElementById('usernameError');
    const passwordErrorEl = document.getElementById('passwordError');

    usernameErrorEl.textContent = "";
    passwordErrorEl.textContent = "";


    if (username === "") {
        usernameErrorEl.textContent = "Username required";
        return;
    }
    if (password.length < 6) {
        passwordErrorEl.textContent = "Password must have at least 6 characters";
        return;
    }


    this.submit();
});
