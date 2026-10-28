document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("animalModal");
  const closeButton = document.getElementById("closeModal");
  const favoriteButton = document.getElementById("animalFavorite");
  const adoptButton = document.getElementById("adoptButton");
  const adoptionMessage = document.getElementById("adoptionMessage");
  if (!modal || !closeButton) return;

  let selectedAnimal = null;
  const value = (id, text) => {
    document.getElementById(id).textContent = text || "Nav norādīts";
  };
  const photoUrl = (photo) =>
    typeof photo === "string" && photo.trim() ? photo : "src/img/logo.jpg";

  function openAnimal(animal) {
    selectedAnimal = animal;
    const image = document.getElementById("modalImage");
    image.src = photoUrl(animal.photo);
    image.alt = animal.name;
    image.onerror = () => {
      image.onerror = null;
      image.src = "src/img/logo.jpg";
    };
    value("animalName", animal.name);
    value("animalBirt", formatAge(animal.age));
    value(
      "animalGender",
      animal.gender === "male"
        ? "Tēviņš"
        : animal.gender === "female"
          ? "Mātīte"
          : "Nav zināms",
    );
    value("animalBreed", animal.breed);
    value("animalHealth", animal.health_status);
    value("animalStatus", animal.adoption_status);
    value("animalDescription", animal.description);
    value("animalSince", animal.arrival_date);
    value("ownerRequirements", animal.owner_requirements);
    const available = animal.adoption_status === "Pieejams";
    adoptButton.disabled = !available;
    adoptButton.textContent = available
      ? "Iesniegt adopcijas pieteikumu"
      : "Šis dzīvnieks nav pieejams";
    adoptionMessage.textContent = "";
    favoriteButton.setAttribute(
      "aria-pressed",
      String(Boolean(animal.is_favorite)),
    );
    favoriteButton.textContent = animal.is_favorite ? "♥" : "♡";
    modal.classList.add("is-open");
    document.body.style.overflow = "hidden";
  }

  function closeAnimalModal() {
    modal.classList.remove("is-open");
    document.body.style.overflow = "";
    selectedAnimal = null;
  }

  document.addEventListener("animal:selected", (event) => {
    openAnimal(event.detail);
  });
  document.addEventListener("favorites:loaded", (event) => {
    if (!selectedAnimal) return;
    const isFavorite = event.detail.ids.has(Number(selectedAnimal.id));
    selectedAnimal.is_favorite = isFavorite;
    favoriteButton.setAttribute("aria-pressed", String(isFavorite));
    favoriteButton.textContent = isFavorite ? "♥" : "♡";
  });
  closeButton.addEventListener("click", closeAnimalModal);
  modal.addEventListener("click", (event) => {
    if (event.target === modal) closeAnimalModal();
  });
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && modal.classList.contains("is-open")) {
      closeAnimalModal();
    }
  });

  favoriteButton.addEventListener("click", async () => {
    if (!selectedAnimal) return;
    const favorite = favoriteButton.getAttribute("aria-pressed") !== "true";
    try {
      const response = await fetch("back-end/auth.php", {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          action: "favorite",
          animal_id: Number(selectedAnimal.id),
          favorite,
        }),
      });
      const result = await response.json();
      if (response.status === 401) {
        window.location.href = "register.html?mode=login";
        return;
      }
      if (!response.ok)
        throw new Error(result.error || "Neizdevās atjaunināt favorītus.");
      favoriteButton.setAttribute("aria-pressed", String(favorite));
      favoriteButton.textContent = favorite ? "♥" : "♡";
      selectedAnimal.is_favorite = favorite;
      document.dispatchEvent(
        new CustomEvent("favorite:changed", {
          detail: { id: Number(selectedAnimal.id), favorite },
        }),
      );
    } catch (error) {
      adoptionMessage.textContent =
        error.message || "Neizdevās atjaunināt favorītus.";
    }
  });

  adoptButton.addEventListener("click", async () => {
    if (!selectedAnimal || selectedAnimal.adoption_status !== "Pieejams")
      return;
    adoptButton.disabled = true;
    adoptionMessage.textContent = "Nosūta pieteikumu...";
    try {
      const response = await fetch("back-end/adoptions.php", {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          animal_id: Number(selectedAnimal.id),
          application_message: document
            .getElementById("adoptionReason")
            .value.trim(),
        }),
      });
      const result = await response.json();
      if (response.status === 401) {
        window.location.href = "register.html?mode=login";
        return;
      }
      if (!response.ok)
        throw new Error(result.error || "Neizdevās iesniegt pieteikumu.");
      adoptionMessage.textContent = `Pieteikums #${result.adoption_id} iesniegts. Statuss: ${result.status}.`;
      selectedAnimal.adoption_status = "Rezervets";
      value("animalStatus", "Rezervets");
      adoptButton.textContent = "Pieteikums iesniegts";
      document.dispatchEvent(new CustomEvent("animal:updated"));
    } catch (error) {
      adoptionMessage.textContent =
        error.message || "Neizdevās iesniegt pieteikumu.";
      adoptButton.disabled = false;
    }
  });
});
