const signInForm = document.getElementById("signInForm");
const signUpForm = document.getElementById("signUpForm");
const showSignUp = document.getElementById("showSignUp");
const showSignIn = document.getElementById("showSignIn");


showSignUp.addEventListener("click", function() {
    signInForm.classList.remove("active");
    signUpForm.classList.add("active");
});

showSignIn.addEventListener("click", function() {
    signUpForm.classList.remove("active");
    signInForm.classList.add("active");
});

