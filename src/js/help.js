const form = document.getElementById("volunteerForm");
const formMessage = document.getElementById("formMessage");

form.addEventListener("submit", function(event) {
    event.preventDefault();

    formMessage.textContent =
        "Pieteikums ir aizpildīts! Nosūtīšana būs pieejama pēc servera pieslēgšanas.";

    formMessage.style.display = "block";
});

document.getElementById("shareButton").addEventListener("click", async function() {
    const shareData = {
        title: "Pēdas Mājās",
        text: "Palīdzi dzīvniekiem atrast mājas!",
        url: window.location.origin + "/animals.html"
    };

    if (navigator.share) {
        try {
            await navigator.share(shareData);
        } catch (error) {
            // Lietotājs atcēla kopīgošanu
        }
    } else {
        try {
            await navigator.clipboard.writeText(shareData.url);
            alert("Saite nokopēta!");
        } catch (error) {
            alert("Atver dzīvnieku lapu un nokopē tās adresi.");
        }
    }
});