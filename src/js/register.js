"use strict";

document.addEventListener("DOMContentLoaded", () => {
  const loginUrl = "register.html?mode=login";
  const loginLinks = document.querySelectorAll(".header-buttons a.register");

  async function loadSession() {
    try {
      const response = await fetch("back-end/auth.php?action=session", {
        credentials: "same-origin",
        headers: { Accept: "application/json" },
        cache: "no-store",
      });
      const result = await response.json();
      if (!response.ok || typeof result.logged_in !== "boolean") {
        throw new Error(
          result.error || "Neizdevās pārbaudīt lietotāja sesiju.",
        );
      }

      if (result.logged_in) {
        if (!result.user || typeof result.user.role !== "string") {
          throw new Error("Servera atbilde par lietotāja sesiju nav derīga.");
        }
        const isAdmin = result.user.role === "admin";
        loginLinks.forEach((link) => {
          link.href = isAdmin ? "admin.html" : "profile.html";
          link.textContent = isAdmin ? "Administrācija" : "Mans profils";
        });

        if (document.body.dataset.requireRole === "admin" && !isAdmin) {
          window.location.replace("profile.html");
        }
      } else {
        loginLinks.forEach((link) => {
          link.href = loginUrl;
          link.textContent = "Pieteikties";
        });
        if (document.body.dataset.requireRole === "admin") {
          window.location.replace(loginUrl);
        }
      }
    } catch (error) {
      console.error("Neizdevās pārbaudīt lietotāja sesiju.", error);
      if (document.body.dataset.requireRole === "admin") {
        window.location.replace(loginUrl);
      }
    }
  }

  loadSession();

  const registerTab = document.getElementById("registerTab");
  const loginTab = document.getElementById("loginTab");
  const registerSection = document.getElementById("registerSection");
  const loginSection = document.getElementById("loginSection");

  function showSection(section) {
    if (!registerSection || !loginSection || !registerTab || !loginTab) return;
    const isRegister = section === "register";
    registerSection.hidden = !isRegister;
    loginSection.hidden = isRegister;
    registerTab.classList.toggle("active", isRegister);
    loginTab.classList.toggle("active", !isRegister);
  }

  registerTab?.addEventListener("click", () => showSection("register"));
  loginTab?.addEventListener("click", () => showSection("login"));
  document
    .getElementById("showLogin")
    ?.addEventListener("click", () => showSection("login"));
  document
    .getElementById("showRegister")
    ?.addEventListener("click", () => showSection("register"));

  if (new URLSearchParams(window.location.search).get("mode") === "login") {
    showSection("login");
  }

  document
    .getElementById("registerForm")
    ?.addEventListener("submit", async function (event) {
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

      const passwordMeetsRequirements =
        [...password].length >= 8 &&
        [...password].length <= 1024 &&
        /\p{L}/u.test(password) &&
        /\p{N}/u.test(password);

      if (!passwordMeetsRequirements) {
        message.textContent =
          "Parolei jābūt vismaz 8 rakstzīmes garai un jāsatur burti un cipari.";
        message.style.color = "red";
        message.style.display = "block";
        return;
      }

      try {
        const response = await fetch("back-end/auth.php", {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            action: "register",
            firstName,
            lastName,
            email,
            password,
          }),
        });
        const result = await response.json();
        if (!response.ok)
          throw new Error(result.error || "Kļūda reģistrējoties.");
        message.textContent =
          "Konts veiksmīgi izveidots! Tagad vari pieteikties. ♡";
        message.style.color = "green";
        message.style.display = "block";
        this.reset();
        showSection("login");
        document.getElementById("loginEmail").value = email;
      } catch (error) {
        message.textContent =
          error.message || "Neizdevās izveidot savienojumu ar serveri.";
        message.style.color = "red";
        message.style.display = "block";
      }
    });

  document
    .getElementById("loginForm")
    ?.addEventListener("submit", async function (event) {
      event.preventDefault();

      const identifier = document.getElementById("loginEmail").value.trim();
      const password = document.getElementById("loginPassword").value;
      const message = document.getElementById("loginMessage");

      try {
        const response = await fetch("back-end/auth.php", {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            action: "login",
            email: identifier,
            password,
          }),
        });
        const result = await response.json();
        if (!response.ok)
          throw new Error(result.error || "Nepareizs e-pasts vai parole.");
        if (!result.user) throw new Error("Pieteikšanās neizdevās.");
        message.textContent = "Pieteikšanās veiksmīga!";
        message.style.color = "green";
        message.style.display = "block";
        window.location.href =
          result.user.role === "admin" ? "admin.html" : "profile.html";
      } catch (error) {
        message.textContent =
          error.message || "Neizdevās izveidot savienojumu ar serveri.";
        message.style.color = "red";
        message.style.display = "block";
      }
    });
});
