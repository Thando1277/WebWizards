const container=document.querySelector('.container');
const LoginLink=document.querySelector('.SignIn');
const LoginRegister=document.querySelector('.SignUp');
LoginRegister.addEventListener('click',()=>{
    container.classList.add('active');
})


document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".container").classList.add("active");
});