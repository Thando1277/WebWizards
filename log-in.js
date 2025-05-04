document.getElementById('logInForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const username = document.getElementById('usernameInput').value.trim();
    const password = document.getElementById('passwordInput').value.trim();
    const usernameErrorEl = document.getElementById('usernameError');
    const passwordErrorEl = document.getElementById('passwordError');

    usernameErrorEl.textContent = "";
    passwordErrorEl.textContent = "";

    let isValid = true;

    if (username === "") {
        usernameErrorEl.textContent = "Username required";
        isValid = false;
    }

    if (password.length < 6) {
        passwordErrorEl.textContent = "Password must have at least 6 characters";
        isValid = false;
    }

    if (isValid) {
        // Create a temporary form submit event to bypass preventDefault
        this.removeEventListener('submit', arguments.callee); // Avoid infinite loop
        this.submit();
    }
});
