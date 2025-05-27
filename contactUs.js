const form = document.getElementById("form");
const name = document.getElementById("namejs");
const email = document.getElementById("emailjs");
const message = document.getElementById("messagejs");

form.addEventListener("submit", submit);

function submit(event) {
    event.preventDefault();

    const nameValue = name.value.trim();
    const emailValue = email.value.trim();
    const messageValue = message.value.trim();

    let errors = [];

    if (nameValue === "") errors.push("Name is required");
    if (emailValue === "") {
        errors.push("Email is required");
    } else {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailValue)) {
            errors.push("Email is not valid");
        }
    }
    if (messageValue === "") errors.push("Message is required");

    if (errors.length > 0) {
        openPopup(errors.join("\n"), false);
    } else {
        emailjs.sendForm("service_bt2a4v8", "template_sdjtt1k", form)
            .then(function(response) {
                openPopup("Message sent successfully!", true);
                form.reset();
            }, function(error) {
                console.error("FAILED...", error);
                openPopup("Failed to send message. Please try again.", false);
            });
    }
}

function openPopup(message, isSuccess = true) {
    const popup = document.getElementById("myPopup");
    const popupMessage = document.getElementById("popupMessage");

    popupMessage.textContent = message;
    popupMessage.style.color = isSuccess ? "green" : "red";

    popup.style.display = "block";
}

function closePopup() {
    document.getElementById("myPopup").style.display = "none";
}
