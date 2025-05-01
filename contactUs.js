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

    if (nameValue === "") {
        errors.push("Name is required");
    }

    if (emailValue === "") {
        errors.push("Email is required");
    } else {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailValue)) {
            errors.push("Email is not valid");
        }
    }

    if (messageValue === "") {
        errors.push("Message is required");
    }

    if (errors.length > 0) {
        alert(errors.join("\n"));
    } else {

        emailjs.sendForm("service_bt2a4v8", "template_sdjtt1k", form)
            .then(function(response) {
                alert("Message sent successfully!");
                form.reset();
            }, function(error) {
                console.error("FAILED...", error);
                alert("Failed to send message. Please try again.");
            });
    }
}

