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

document.getElementById("registerForm").addEventListener("submit", async function(event) {
    event.preventDefault();

    const firstName = document.getElementById("firstName").value.trim();
    const lastName = document.getElementById("lastName").value.trim();
    const email = document.getElementById("registerEmail").value.trim();
    const password = document.getElementById("registerPassword").value;
    const confirm = document.getElementById("confirmPassword").value;
    const message = document.getElementById("registerMessage");

    if (password !== confirm) {
        message.textContent = "Paroles nesakrīt!";
        message.style.color = "red";
        message.style.display = "block";
        return;
    }

    const formData = {
        action: "register", 
        firstName: firstName, 
        lastName: lastName,   
        email: email,
        password: password
    };

    try {
        const response = await fetch("back-end/auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (response.ok) {
            message.textContent = "Konts veiksmīgi izveidots! Tagad vari pieteikties. ♡";
            message.style.color = "green";
            message.style.display = "block";
            this.reset();
        } else {
            message.textContent = result.error || "Kļūda reģistrējoties.";
            message.style.color = "red";
            message.style.display = "block";
        }
    } catch (error) {
        message.textContent = "Neizdevās izveidot savienojumu ar serveri.";
        message.style.color = "red";
        message.style.display = "block";
    }
});

// Вход (Логин)
document.getElementById("loginForm").addEventListener("submit", async function(event) {
    event.preventDefault();

    const email = document.getElementById("loginEmail").value.trim();
    const password = document.getElementById("loginPassword").value;
    const message = document.getElementById("loginMessage");

    const formData = {
        action: "login", 
        email: email,
        password: password
    };

    try {
        const response = await fetch("back-end/auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (response.ok) {
            message.textContent = "Veiksmīga pieteikšanās! Laipni lūgti! ♡";
            message.style.color = "green";
            message.style.display = "block";
        } else {
            message.textContent = result.error || "Nepareizs e-pasts vai parole.";
            message.style.color = "red";
            message.style.display = "block";
        }
    } catch (error) {
        message.textContent = "Neizdevās izveidot savienojumu ar serveri.";
        message.style.color = "red";
        message.style.display = "block";
    }
});