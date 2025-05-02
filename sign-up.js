const container = document.querySelector('.container');
const LoginLink = document.querySelector('.SignIn');
const LoginRegister = document.querySelector('.SignUp');


LoginRegister.addEventListener('click', () => {
    container.classList.add('active');
});


document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".container").classList.add("active");
});


document.getElementById('signUpForm').addEventListener('submit', function (e) {
    e.preventDefault(); 

    const username = document.getElementById('usernameInput').value.trim();
    const password = document.getElementById('passwordInput').value.trim();
    const email = document.getElementById('emailInput').value.trim();
    const phone = document.getElementById('phoneInput').value.trim();
    
    const usernameErrorEl = document.getElementById('usernameError');
    const passwordErrorEl = document.getElementById('passwordError');
    const emailErrorEl = document.getElementById('emailError');
    const phoneErrorEl = document.getElementById('phoneError');


    usernameErrorEl.textContent = "";
    passwordErrorEl.textContent = "";
    emailErrorEl.textContent = "";
    phoneErrorEl.textContent = "";

    let isValid = true;

    if (username === "") {
        usernameErrorEl.textContent = "Username required";
        isValid = false;
    }

    if (password.length < 6) {
        passwordErrorEl.textContent = "Password must have at least 6 characters";
        isValid = false;
    }


    if (!email.includes("@") || !email.includes(".com")) {
        emailErrorEl.textContent = "Enter a valid email address";
        isValid = false;
    }


    if (phone.length !== 10 || phone[0] !== "0") {
        phoneErrorEl.textContent = "Enter a valid South African phone number";
        isValid = false;
    }


    if (isValid) {
   
        this.submit();  
    } else {
        console.log("Form contains errors, please correct them.");
    }
});
