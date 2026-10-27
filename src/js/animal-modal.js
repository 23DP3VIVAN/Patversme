function formatDate(date) {
    if (!date) return "Nav norādīts";

    const parsedDate = new Date(date);

    if (Number.isNaN(parsedDate.getTime())) {
        return "Nav norādīts";
    }

    return parsedDate.toLocaleDateString("lv-LV", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric"
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("animalModal");
    const closeButton = document.getElementById("closeModal");

    if (!modal || !closeButton) {
        console.error("Nav atrasts animalModal vai closeModal HTML elements.");
        return;
    }

    let selectedAnimal = null;

    function openAnimal(animal) {
        selectedAnimal = animal;

        const type = animal.species === "suns" ? "Suns" : "Kaķis";

        document.getElementById("modalImage").src = `https://images.unsplash.com/${animal.photo}?auto=format&fit=crop&w=600&q=85`;
        document.getElementById("modalImage").alt = animal.name;
        document.getElementById("animalName").textContent = animal.name;
        document.getElementById("animalBirt").textContent = `Vecums: ${formatAge(animal.age)}`;
        document.getElementById("animalGender").textContent = `Dzimums: ${animal.gender === "male" ? "Tēviņš" : "Mātīte"}`;
        document.getElementById("animalBreed").textContent = `Šķirne: ${animal.breed || "Nav norādīta"}`;
        document.getElementById("animalSpecialNeeds").textContent = animal.special? "Īpašas vajadzības: Jā" : "Īpašas vajadzības: Nē";
        document.getElementById("animalDescription").textContent = animal.description || "Nav apraksta";
        document.getElementById("animalSince").textContent = `Patversmē kopš: ${formatDate(animal.since)}`;

        modal.classList.add("is-open");
        document.body.style.overflow = "hidden";
    }

    function closeAnimalModal() {
        modal.classList.remove("is-open");
        document.body.style.overflow = "";
        selectedAnimal = null;
    }

    document.addEventListener("click", event => {
        const card = event.target.closest(".animal-card");

        if (!card || event.target.closest(".heart")) return;

        const id = card.dataset.id;
        const animal = animals.find(item => String(item.id) === String(id));

        if (animal) openAnimal(animal);
    });

    closeButton.addEventListener("click", closeAnimalModal);

    modal.addEventListener("click", event => {
        if (event.target === modal) closeAnimalModal();
    });

    document.addEventListener("keydown", event => {
        if (event.key === "Escape" && modal.classList.contains("is-open")) {
            closeAnimalModal();
        }
    });
    const adoptButton = document.getElementById("adoptButton");

    if (adoptButton) {
        adoptButton.addEventListener("click", () => {
            if (!selectedAnimal) return;
            const url = new URL("adoption.html", window.location.href);
            url.searchParams.set("animal_id", selectedAnimal.id);
            window.location.href = url.href;
        });
    }
});