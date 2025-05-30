document.addEventListener("DOMContentLoaded", function () {
    const container = document.querySelector('.container');
    const LoginLink = document.querySelector('.SignIn');
    const LoginRegister = document.querySelector('.SignUp');
    const signUpForm = document.getElementById('signUpForm');

    const passwordInput = document.getElementById('passwordInput');
    const togglePassword = document.getElementById('togglePassword');

    container.classList.add("active");

    LoginRegister?.addEventListener('click', () => {
        container.classList.add('active');
    });

    togglePassword?.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        togglePassword.classList.toggle('bx-show');
        togglePassword.classList.toggle('bx-hide');
    });

    signUpForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const fullname = document.getElementById('fullnameInput').value.trim();
        const username = document.getElementById('usernameInput').value.trim();
        const password = passwordInput.value.trim();
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

        // ✅ Only call sign-up handler if all validations pass
        if (isValid) {
            handleSignUp(fullname, username, email, password, phone);
        }
    });
});

// ✅ Popup functions globally accessible
function showPopup(message, redirectUrl = null) {
    document.getElementById("popupMessage").textContent = message;
    document.getElementById("myPopup").style.display = "block";

    if (redirectUrl) {
        setTimeout(() => {
            window.location.href = redirectUrl;
        }, 2000);
    }
}

function closePopup() {
    document.getElementById("myPopup").style.display = "none";
}

// ✅ Actual sign-up request
function handleSignUp(fullname, username, email, password, phone) {
    fetch("sign-up.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `fullname=${encodeURIComponent(fullname)}&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&phone=${encodeURIComponent(phone)}`
    })
    .then(response => response.text())
    .then(result => {
        if (result.includes("User registered successfully")) {
            emailjs.send("service_c0oqnhr", "template_0hr6k8i", {
                user_name: username,
                user_email: email
            })
            .then(() => {
                showPopup("Sign-up successful! Welcome email sent.", "log-in.html");
            })
            .catch(() => {
                showPopup("Sign-up successful, but welcome email failed.", "log-in.html");
            });
        } else {
            showPopup("Sign-up failed: " + result);
        }
    })
    .catch(err => {
        console.error("Error posting form:", err);
        showPopup("There was a problem submitting the form.");
    });
}
