document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("volunteerForm");
    const formMessage = document.getElementById("formMessage");

    if (form) {
        form.addEventListener("submit", async function(event) {
            event.preventDefault();

            const formData = {
                name: document.getElementById("name").value.trim(),
                surname: document.getElementById("surname").value.trim(),
                email: document.getElementById("email").value.trim(),
                phone: document.getElementById("phone").value.trim(),
                activity: document.getElementById("activity").value,
                message: document.getElementById("message").value.trim()
            };

            try {
                const response = await fetch("back-end/volunteers.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (response.ok) {
                    formMessage.textContent = "Pieteikums veiksmīgi nosūtīts! Paldies. ♡";
                    formMessage.style.color = "green";
                    formMessage.style.display = "block";
                    form.reset();
                } else {
                    formMessage.textContent = result.error || "Kļūda nosūtot pieteikumu.";
                    formMessage.style.color = "red";
                    formMessage.style.display = "block";
                }
            } catch (error) {
                formMessage.textContent = "Neizdevās izveidot savienojumu ar serveri.";
                formMessage.style.color = "red";
                formMessage.style.display = "block";
            }
        });
    }

    const shareButton = document.getElementById("shareButton");
    if (shareButton) {
        shareButton.addEventListener("click", async function() {
            const shareData = {
                title: "Pēdas Mājās",
                text: "Palīdzi dzīvniekiem atrast mājas!",
                url: window.location.origin + "/animals.html"
            };

            if (navigator.share) {
                try {
                    await navigator.share(shareData);
                } catch (error) {
                    
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
    }
});