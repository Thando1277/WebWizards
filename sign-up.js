const container=document.querySelector('.container');
const LoginLink=document.querySelector('.SignIn');
const LoginRegister=document.querySelector('.SignUp');
LoginRegister.addEventListener('click',()=>{
    container.classList.add('active');
})


document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".container").classList.add("active");
});

//VALIDATIONS

document.getElementById('signUpForm').addEventListener('submit', function(e){
    e.preventDefault();

    const username = document.getElementById('usernameInput').value.trim();
    const password = document.getElementById('passwordInput').value.trim();
    const email = document.getElementById('emailInput').value.trim();
    const phone = document.getElementById('phoneInput').value.trim();
    const usernameErrorEl = document.getElementById('usernameError');
    const passwordErrorEl = document.getElementById('passwordError');
    const emailErrorE1 = document.getElementById('emailError');
    const phoneErrorE1 = document.getElementById('phoneError');

    usernameErrorEl.textContent = "";
    passwordErrorEl.textContent = "";
    emailErrorE1.textContent = "";
    phoneErrorE1.textContent = "";

    if (username === ""){
        usernameErrorEl.textContent = "Username required";
        return;
    }
    if (password.length < 6){
        passwordErrorEl.textContent = "Password must have at least 6 characters";
        return;
    }

    if (!email.includes("@") || !email.includes(".com")){
        emailErrorE1.textContent = "Enter a valid email";
        return;
    }

    if(phone.length != 10 || phone[0] != "0"){
        phoneErrorE1.textContent = "Enter a valid SA Number";
        return;
    }

    // Redirect to dashboard on successful login
    // window.location.href = "dashboard.html";

    document.getElementById('signUpForm').reset(); 
});