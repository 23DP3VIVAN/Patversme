const form = document.getElementById("contactForm");
const formStatus = document.getElementById("formStatus");

form.addEventListener("submit", function(event) {
    event.preventDefault();
    formStatus.textContent =
        "Forma ir aizpildīta! Ziņas nosūtīšana būs pieejama pēc servera pieslēgšanas.";
    formStatus.classList.add("visible");
});