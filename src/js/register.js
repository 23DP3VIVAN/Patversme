const registerTab = document.getElementById("registerTab");
const loginTab = document.getElementById("loginTab");
const registerSection = document.getElementById("registerSection");
const loginSection = document.getElementById("loginSection");

function showSection(section) {
    const isRegister = section === "register";

    registerSection.hidden = !isRegister;
    loginSection.hidden = isRegister;

    registerTab.classList.toggle("active", isRegister);
    loginTab.classList.toggle("active", !isRegister);
}

registerTab.addEventListener("click", () => showSection("register"));
loginTab.addEventListener("click", () => showSection("login"));

document.getElementById("showLogin").addEventListener("click", () => showSection("login"));

document.getElementById("showRegister").addEventListener("click", () => showSection("register"));

document.getElementById("registerForm").addEventListener("submit", function(event) {
    event.preventDefault();

    const password = document.getElementById("registerPassword").value;
    const confirm = document.getElementById("confirmPassword").value;
    const message = document.getElementById("registerMessage");

    if (password !== confirm) {
        message.textContent = "Paroles nesakrīt!";
    } else {
        message.textContent ="Šī ir demonstrācijas forma. Konts vēl netiek izveidots.";
    }
    message.style.display = "block";
});

document.getElementById("loginForm").addEventListener("submit", function(event) {
    event.preventDefault();

    const message = document.getElementById("loginMessage");
    message.textContent =
        "Demonstrācijas režīms. Pieteikšanās vēl nav pieejama.";
    message.style.display = "block";
});
