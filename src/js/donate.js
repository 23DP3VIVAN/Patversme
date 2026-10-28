document.addEventListener("DOMContentLoaded", function () {
  const amountButtons = document.querySelectorAll(".amount");

  const customAmount = document.getElementById("customAmount");
  const total = document.getElementById("total");
  const form = document.getElementById("donationForm");
  const result = document.getElementById("result");

  if (!form) return;

  let selectedAmount = 10;

  function updateTotal() {
    const formatted = selectedAmount.toLocaleString("lv-LV", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

    total.textContent = formatted + " €";
  }

  amountButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      amountButtons.forEach(function (item) {
        item.classList.remove("active");
      });

      button.classList.add("active");
      selectedAmount = Number(button.dataset.amount);
      customAmount.value = "";

      updateTotal();
    });
  });

  customAmount.addEventListener("input", function () {
    amountButtons.forEach(function (button) {
      button.classList.remove("active");
    });

    selectedAmount = Number(customAmount.value);
    updateTotal();
  });

  form.addEventListener("submit", async function (event) {
    event.preventDefault();

    if (
      !Number.isFinite(selectedAmount) ||
      selectedAmount < 1 ||
      selectedAmount > 100000
    ) {
      result.textContent = "Lūdzu, izvēlies summu no 1 līdz 100 000 €.";
      result.style.display = "block";
      return;
    }

    const formData = {
      firstName: document.getElementById("firstName").value,
      lastName: document.getElementById("lastName").value,
      email: document.getElementById("email").value,
      customAmount: selectedAmount,
    };

    try {
      const response = await fetch("back-end/donations.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      });

      const resData = await response.json();

      if (response.ok) {
        result.textContent =
          "Paldies! Demonstrācijas ziedojuma ieraksts saglabāts; maksājums netika veikts. ♡";
        result.style.color = "green";
        form.reset();
        selectedAmount = 10;
        updateTotal();
      } else {
        result.textContent = resData.error || "Kļūda apstrādājot ziedojumu.";
        result.style.color = "red";
      }
    } catch (error) {
      result.textContent = "Neizdevās izveidot savienojumu ar serveri.";
      result.style.color = "red";
    }

    result.style.display = "block";
  });

  updateTotal();
});
