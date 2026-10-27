document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  const status = document.getElementById("formStatus");

  if (!form) return;

  form.addEventListener("submit", async function (event) {
    event.preventDefault();

    const formData = {
      name: document.getElementById("name").value,
      surname: document.getElementById("surname").value,
      email: document.getElementById("email").value,
      topic: document.getElementById("topic").value,
      message: document.getElementById("message").value,
    };

    try {
      const response = await fetch("back-end/contact.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      });

      const result = await response.json();

      if (response.ok) {
        status.textContent = "Ziņa veiksmīgi nosūtīta! Paldies. ♡";
        status.style.color = "green";
        form.reset();
      } else {
        status.textContent = result.error || "Kļūda nosūtot ziņu.";
        status.style.color = "red";
      }
    } catch (error) {
      status.textContent = "Neizdevās izveidot savienojumu ar serveri.";
      status.style.color = "red";
    }

    status.classList.add("visible");
  });
});
