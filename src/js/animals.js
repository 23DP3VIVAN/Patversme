"use strict";

const perPage = 8;
const animalsApi = "back-end/animals.php";
const authApi = "back-end/auth.php";
let animals = [];
let currentPage = 1;
let currentCategory = "all";
let favorites = new Set();
let activeRequest = null;
let searchTimer = null;

const grid = document.getElementById("animalGrid");
const pagination = document.getElementById("pagination");
const resultsCount = document.getElementById("resultsCount");
const emptyMessage = document.getElementById("emptyMessage");
const searchInput = document.getElementById("searchInput");
const sortSelect = document.getElementById("sortSelect");

function formatAge(age) {
  if (age === null || age === undefined || age === "") return "Nav norādīts";
  const years = Number(age);
  if (years < 1) return `${Math.max(1, Math.round(years * 12))} mēneši`;
  return `${years} ${years === 1 ? "gads" : "gadi"}`;
}

function imageSource(photo) {
  return typeof photo === "string" && photo.trim() ? photo : "src/img/logo.jpg";
}

function getFilters() {
  const params = new URLSearchParams();
  const selectedSpecies = document.querySelector(
    'input[name="species"]:checked',
  )?.value;
  const species = ["suns", "kakis"].includes(currentCategory)
    ? currentCategory
    : selectedSpecies;
  if (species && species !== "all") params.set("species", species);
  if (["kucens", "kakens"].includes(currentCategory)) {
    params.set("species", currentCategory === "kucens" ? "suns" : "kakis");
    params.set("category_age", "baby");
  }
  if (currentCategory === "special") params.set("special", "1");

  const gender = document.querySelector('input[name="gender"]:checked')?.value;
  if (gender && gender !== "all") params.set("gender", gender);

  document.querySelectorAll('input[name="age"]:checked').forEach((input) => {
    params.append("age[]", input.value);
  });
  const breed = document.getElementById("breedFilter").value.trim();
  const health = document.getElementById("healthFilter").value.trim();
  const search = searchInput.value.trim();
  if (breed) params.set("breed", breed);
  if (health) params.set("health_status", health);
  if (search) params.set("search", search);
  if (document.getElementById("specialFilter").checked) {
    params.set("special", "1");
  }
  return params;
}

function createAnimalCard(animal) {
  const card = document.createElement("article");
  card.className = "animal-card";
  card.dataset.id = String(animal.id);

  const photoWrapper = document.createElement("div");
  photoWrapper.className = "animal-photo";
  const image = document.createElement("img");
  image.src = imageSource(animal.photo);
  image.alt = animal.name;
  image.loading = "lazy";
  image.addEventListener(
    "error",
    () => {
      image.src = "src/img/logo.jpg";
    },
    { once: true },
  );

  const favorite = document.createElement("button");
  favorite.type = "button";
  favorite.className = `heart ${favorites.has(Number(animal.id)) ? "liked" : ""}`;
  favorite.dataset.id = String(animal.id);
  favorite.setAttribute("aria-label", "Pievienot favorītiem");
  favorite.setAttribute(
    "aria-pressed",
    String(favorites.has(Number(animal.id))),
  );
  favorite.textContent = favorites.has(Number(animal.id)) ? "♥" : "♡";
  photoWrapper.append(image, favorite);

  const info = document.createElement("div");
  info.className = "animal-info";
  const title = document.createElement("h3");
  title.textContent = animal.name;
  const type =
    animal.species === "suns"
      ? "Suns"
      : animal.species === "kakis"
        ? "Kaķis"
        : animal.species;
  const summary = document.createElement("p");
  summary.textContent = `${type} • ${formatAge(animal.age)} • ${animal.adoption_status}`;
  const tags = document.createElement("div");
  tags.className = "animal-tags";
  [animal.breed, animal.health_status, animal.adoption_status].forEach(
    (text) => {
      const tag = document.createElement("span");
      tag.textContent = text || "Nav norādīts";
      tags.appendChild(tag);
    },
  );
  info.append(title, summary, tags);
  card.append(photoWrapper, info);
  return card;
}

function renderPagination(totalPages) {
  pagination.replaceChildren();
  if (totalPages <= 1) return;
  const addPageButton = (label, page, disabled = false) => {
    const button = document.createElement("button");
    button.type = "button";
    button.textContent = label;
    button.disabled = disabled;
    button.setAttribute("aria-label", `Lapa ${label}`);
    if (Number(label) === currentPage) button.classList.add("current");
    button.addEventListener("click", () => {
      currentPage = page;
      renderAnimals();
      document.getElementById("catalog").scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    });
    pagination.appendChild(button);
  };
  addPageButton("‹", Math.max(1, currentPage - 1), currentPage === 1);
  for (let page = 1; page <= totalPages; page++) {
    addPageButton(String(page), page);
  }
  addPageButton(
    "›",
    Math.min(totalPages, currentPage + 1),
    currentPage === totalPages,
  );
}

function renderAnimals() {
  const sorted = [...animals];
  if (sortSelect.value === "youngest") {
    sorted.sort((a, b) => Number(a.age ?? 999) - Number(b.age ?? 999));
  } else if (sortSelect.value === "oldest") {
    sorted.sort((a, b) => Number(b.age ?? -1) - Number(a.age ?? -1));
  } else if (sortSelect.value === "name") {
    sorted.sort((a, b) => a.name.localeCompare(b.name, "lv"));
  }

  const totalPages = Math.ceil(sorted.length / perPage);
  currentPage = Math.min(currentPage, Math.max(totalPages, 1));
  const start = (currentPage - 1) * perPage;
  grid.replaceChildren(
    ...sorted.slice(start, start + perPage).map(createAnimalCard),
  );
  resultsCount.textContent = `Atrasti: ${sorted.length} dzīvnieki`;
  emptyMessage.hidden = sorted.length !== 0;
  renderPagination(totalPages);
}

async function loadAnimals() {
  if (activeRequest) activeRequest.abort();
  activeRequest = new AbortController();
  const params = getFilters();
  resultsCount.textContent = "Ielādē dzīvniekus...";
  try {
    const response = await fetch(`${animalsApi}?${params}`, {
      headers: { Accept: "application/json" },
      signal: activeRequest.signal,
    });
    const result = await response.json();
    if (!response.ok || !Array.isArray(result)) {
      throw new Error(result.error || "Neizdevās ielādēt dzīvniekus.");
    }
    animals = result;
    currentPage = 1;
    renderAnimals();
    const selectedAnimalId = new URLSearchParams(window.location.search).get(
      "id",
    );
    const selectedAnimal = animals.find(
      (animal) => String(animal.id) === selectedAnimalId,
    );
    if (selectedAnimal) {
      document.dispatchEvent(
        new CustomEvent("animal:selected", {
          detail: {
            ...selectedAnimal,
            is_favorite: favorites.has(Number(selectedAnimal.id)),
          },
        }),
      );
    }
  } catch (error) {
    if (error.name === "AbortError") return;
    grid.replaceChildren();
    pagination.replaceChildren();
    emptyMessage.hidden = true;
    resultsCount.textContent = error.message || "Neizdevās ielādēt dzīvniekus.";
  }
}

async function loadFavorites() {
  try {
    const response = await fetch(`${authApi}?action=favorites`, {
      credentials: "same-origin",
      headers: { Accept: "application/json" },
    });
    if (response.status === 401) return;
    const result = await response.json();
    if (!response.ok || !Array.isArray(result.favorites)) {
      throw new Error(result.error || "Neizdevās ielādēt favorītus.");
    }
    favorites = new Set(result.favorites.map(Number));
    renderAnimals();
    document.dispatchEvent(
      new CustomEvent("favorites:loaded", { detail: { ids: favorites } }),
    );
  } catch (error) {
    console.error("Neizdevās ielādēt favorītus.", error);
  }
}

function scheduleLoad() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(loadAnimals, 250);
}

async function loadHomeFeatured() {
  const homeGrid = document.querySelector(".animals-section .animal-grid");
  if (!homeGrid) return;
  try {
    const response = await fetch(`${animalsApi}?status=Pieejams`, {
      headers: { Accept: "application/json" },
    });
    const result = await response.json();
    if (!response.ok || !Array.isArray(result)) {
      throw new Error(result.error || "Neizdevās ielādēt dzīvniekus.");
    }
    const cards = result.slice(0, 4).map((animal) => {
      const card = createAnimalCard(animal);
      card.querySelector(".heart")?.remove();
      card.tabIndex = 0;
      card.setAttribute("role", "link");
      card.addEventListener("click", () => {
        window.location.href = `animals.html?id=${encodeURIComponent(animal.id)}`;
      });
      card.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          window.location.href = `animals.html?id=${encodeURIComponent(animal.id)}`;
        }
      });
      return card;
    });
    if (!cards.length) {
      const empty = document.createElement("p");
      empty.textContent = "Pašlaik nav pieejamu dzīvnieku adopcijai.";
      homeGrid.replaceChildren(empty);
      return;
    }
    homeGrid.replaceChildren(...cards);
  } catch (error) {
    const message = document.createElement("p");
    message.textContent = error.message || "Neizdevās ielādēt dzīvniekus.";
    homeGrid.replaceChildren(message);
  }
}

if (grid) {
  document.querySelectorAll(".category").forEach((button) => {
    button.addEventListener("click", () => {
      currentCategory =
        button.dataset.category === "all" ? "all" : button.dataset.category;
      if (currentCategory === "all") {
        document.querySelector('input[name="species"][value="all"]').checked =
          true;
      }
      if (["suns", "kakis"].includes(currentCategory)) {
        document.querySelector(
          `input[name="species"][value="${currentCategory}"]`,
        ).checked = true;
      }
      document.querySelectorAll(".category").forEach((item) => {
        item.classList.toggle("active", item === button);
      });
      scheduleLoad();
    });
  });

  document.querySelectorAll(".sidebar input").forEach((input) => {
    input.addEventListener("input", scheduleLoad);
    input.addEventListener("change", scheduleLoad);
  });
  searchInput.addEventListener("input", scheduleLoad);
  sortSelect.addEventListener("change", renderAnimals);

  document.getElementById("clearFilters").addEventListener("click", () => {
    document.querySelector('input[name="species"][value="all"]').checked = true;
    document.querySelector('input[name="gender"][value="all"]').checked = true;
    document.querySelectorAll('input[name="age"]').forEach((input) => {
      input.checked = false;
    });
    document.getElementById("specialFilter").checked = false;
    document.getElementById("breedFilter").value = "";
    document.getElementById("healthFilter").value = "";
    searchInput.value = "";
    sortSelect.value = "default";
    currentCategory = "all";
    document.querySelectorAll(".category").forEach((button) => {
      button.classList.toggle("active", button.dataset.category === "all");
    });
    scheduleLoad();
  });

  document.addEventListener("favorite:changed", (event) => {
    const { id, favorite } = event.detail;
    if (favorite) favorites.add(id);
    else favorites.delete(id);
    renderAnimals();
  });
  document.addEventListener("animal:updated", loadAnimals);

  grid.addEventListener("click", async (event) => {
    const favoriteButton = event.target.closest(".heart");
    if (favoriteButton) {
      event.stopPropagation();
      const animalId = Number(favoriteButton.dataset.id);
      const favorite = !favorites.has(animalId);
      try {
        const response = await fetch(authApi, {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            action: "favorite",
            animal_id: animalId,
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
        if (favorite) favorites.add(animalId);
        else favorites.delete(animalId);
        renderAnimals();
      } catch (error) {
        window.alert(error.message || "Neizdevās atjaunināt favorītus.");
      }
      return;
    }

    const card = event.target.closest(".animal-card");
    const animal = animals.find((item) => String(item.id) === card?.dataset.id);
    if (animal) {
      document.dispatchEvent(
        new CustomEvent("animal:selected", {
          detail: { ...animal, is_favorite: favorites.has(Number(animal.id)) },
        }),
      );
    }
  });

  loadAnimals();
  loadFavorites();
} else {
  loadHomeFeatured();
}
