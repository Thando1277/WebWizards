const container = document.querySelector('.container');
const LoginLink = document.querySelector('.SignIn');
const LoginRegister = document.querySelector('.SignUp');

LoginRegister.addEventListener('click', () => {
    container.classList.add('active');
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".container").classList.add("active");
});

// Initialize EmailJS (make sure your key is correct)
(function() {
    emailjs.init("ALFssTcDRmb8yBusM");  // <-- Your public EmailJS key here
})();

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

    // Clear previous errors
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
        // Step 1: Send data to PHP backend
        fetch("sign-up.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}&email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone)}`
        })
        .then(response => response.text())
        .then(result => {
            if (result.includes("User registered successfully")) {
                // Step 2: Send confirmation email via EmailJS
                emailjs.send("service_c0oqnhr", "template_0hr6k8i", {
                    user_name: username,
                    user_email: email
                })
                .then(() => {
                    alert("Sign-up successful! Welcome email sent.");
                    window.location.href = "log-in.html";
                })
                .catch(() => {
                    alert("Sign-up successful, but welcome email failed.");
                    window.location.href = "log-in.html";
                });
            } else {
                alert("Sign-up failed: " + result);
            }
        })
        .catch(err => {
            console.error("Error posting form:", err);
            alert("There was a problem submitting the form.");
        });
    }
});
