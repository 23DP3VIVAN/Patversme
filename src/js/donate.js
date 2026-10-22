const amountButtons = document.querySelectorAll(".amount");
const frequencyButtons = document.querySelectorAll(".choice");

const customAmount = document.getElementById("customAmount");
const total = document.getElementById("total");
const form = document.getElementById("donationForm");
const result = document.getElementById("result");

let selectedAmount = 10;
let selectedFrequency = "once";

function updateTotal() {
    const formatted = selectedAmount.toLocaleString("lv-LV", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    total.textContent = formatted + " €";

    if (selectedFrequency === "monthly") {
        total.textContent += " / mēnesī";
    }
}

amountButtons.forEach(function(button) {
    button.addEventListener("click", function() {
        amountButtons.forEach(function(item) {
            item.classList.remove("active");
        });

        button.classList.add("active");
        selectedAmount = Number(button.dataset.amount);
        customAmount.value = "";

        updateTotal();
    });
});

customAmount.addEventListener("input", function() {
    amountButtons.forEach(function(button) {
        button.classList.remove("active");
    });

    selectedAmount = Number(customAmount.value);
    updateTotal();
});

frequencyButtons.forEach(function(button) {
    button.addEventListener("click", function() {
        frequencyButtons.forEach(function(item) {
            item.classList.remove("active");
        });

        button.classList.add("active");
        selectedFrequency = button.dataset.frequency;

        updateTotal();
    });
});

form.addEventListener("submit", function(event) {
    event.preventDefault();

    if (!Number.isFinite(selectedAmount) ||
        selectedAmount < 1 ||
        selectedAmount > 100000) {
        result.textContent = "Lūdzu, izvēlies summu no 1 līdz 100 000 €.";
    } else {
        result.textContent = "Paldies par vēlmi palīdzēt! " + "Maksājumu sistēma vēl nav pieslēgta, " + "tāpēc ziedojums nav veikts.";
    }

    result.style.display = "block";
});

updateTotal();
