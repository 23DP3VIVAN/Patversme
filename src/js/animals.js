const animals = [
  {
    id: 1,
    name: "Luna",
    species: "suns",
    age: 2,
    gender: "female",
    breed: "Samojeds",
    character: "Aktīva",
    size: "Liels",
    special: false,
    photo: "photo-1558788353-f76d92427f16",
  },
  {
    id: 2,
    name: "Reks",
    species: "suns",
    age: 4,
    gender: "male",
    breed: "Jauktenis",
    character: "Rotaļīgs",
    size: "Vidējs",
    special: false,
    photo: "photo-1561037404-61cd46aa615b",
  },
  {
    id: 3,
    name: "Mika",
    species: "kakis",
    age: 1,
    gender: "female",
    breed: "Mājas kaķis",
    character: "Draudzīga",
    size: "Mazs",
    special: false,
    photo: "photo-1573865526739-10659fec78a5",
  },
  {
    id: 4,
    name: "Rafs",
    species: "suns",
    age: 2,
    gender: "male",
    breed: "Retrīvers",
    character: "Aktīvs",
    size: "Vidējs",
    special: false,
    photo: "photo-1552053831-71594a27632d",
  },
  {
    id: 5,
    name: "Lilo",
    species: "kakis",
    age: 0.5,
    gender: "female",
    breed: "Mājas kaķis",
    character: "Mierīga",
    size: "Mazs",
    special: false,
    photo: "photo-1574158622682-e40e69881006",
  },
  {
    id: 6,
    name: "Maks",
    species: "suns",
    age: 3,
    gender: "male",
    breed: "Jauktenis",
    character: "Aktīvs",
    size: "Vidējs",
    special: false,
    photo: "photo-1537151608828-ea2b11777ee8",
  },
  {
    id: 7,
    name: "Pika",
    species: "kakis",
    age: 2,
    gender: "female",
    breed: "Mājas kaķis",
    character: "Ziņkārīga",
    size: "Mazs",
    special: false,
    photo: "photo-1514888286974-6c03e2ca1dba",
  },
  {
    id: 8,
    name: "Bella",
    species: "suns",
    age: 5,
    gender: "female",
    breed: "Jauktenis",
    character: "Mierīga",
    size: "Liels",
    special: true,
    photo: "photo-1543466835-00a7907e9de1",
  },
  {
    id: 9,
    name: "Oskars",
    species: "kakis",
    age: 3,
    gender: "male",
    breed: "Mājas kaķis",
    character: "Rotaļīgs",
    size: "Mazs",
    special: false,
    photo: "photo-1533738363-b7f9aef128ce",
  },
  {
    id: 10,
    name: "Džeks",
    species: "suns",
    age: 0.7,
    gender: "male",
    breed: "Jauktenis",
    character: "Draudzīgs",
    size: "Mazs",
    special: false,
    photo: "photo-1548199973-03cce0bbc87b",
  },
  {
    id: 11,
    name: "Muris",
    species: "kakis",
    age: 8,
    gender: "male",
    breed: "Mājas kaķis",
    character: "Mierīgs",
    size: "Mazs",
    special: true,
    photo: "photo-1495360010541-f48722b34f7d",
  },
  {
    id: 12,
    name: "Riko",
    species: "suns",
    age: 1,
    gender: "male",
    breed: "Jauktenis",
    character: "Enerģisks",
    size: "Vidējs",
    special: false,
    photo: "photo-1587300003388-59208cc962cb",
  },
  {
    id: 13,
    name: "Kleo",
    species: "kakis",
    age: 0.4,
    gender: "female",
    breed: "Mājas kaķis",
    character: "Rotaļīga",
    size: "Mazs",
    special: false,
    photo: "photo-1511044568932-338cba0ad803",
  },
  {
    id: 14,
    name: "Dora",
    species: "suns",
    age: 6,
    gender: "female",
    breed: "Jauktenis",
    character: "Mierīga",
    size: "Vidējs",
    special: false,
    photo: "photo-1558788353-f76d92427f16",
  },
  {
    id: 15,
    name: "Simba",
    species: "kakis",
    age: 4,
    gender: "male",
    breed: "Mājas kaķis",
    character: "Ziņkārīgs",
    size: "Mazs",
    special: false,
    photo: "photo-1573865526739-10659fec78a5",
  },
  {
    id: 16,
    name: "Bruno",
    species: "suns",
    age: 9,
    gender: "male",
    breed: "Jauktenis",
    character: "Mierīgs",
    size: "Liels",
    special: true,
    photo: "photo-1561037404-61cd46aa615b",
  },
  {
    id: 17,
    name: "Poga",
    species: "kakis",
    age: 0.6,
    gender: "female",
    breed: "Mājas kaķis",
    character: "Draudzīga",
    size: "Mazs",
    special: false,
    photo: "photo-1574158622682-e40e69881006",
  },
  {
    id: 18,
    name: "Tobis",
    species: "suns",
    age: 2,
    gender: "male",
    breed: "Jauktenis",
    character: "Rotaļīgs",
    size: "Vidējs",
    special: false,
    photo: "photo-1552053831-71594a27632d",
  },
  {
    id: 19,
    name: "Mince",
    species: "kakis",
    age: 5,
    gender: "female",
    breed: "Mājas kaķis",
    character: "Mierīga",
    size: "Mazs",
    special: true,
    photo: "photo-1514888286974-6c03e2ca1dba",
  },
  {
    id: 20,
    name: "Rūdis",
    species: "suns",
    age: 0.8,
    gender: "male",
    breed: "Jauktenis",
    character: "Aktīvs",
    size: "Mazs",
    special: false,
    photo: "photo-1537151608828-ea2b11777ee8",
  },
  {
    id: 21,
    name: "Fēlikss",
    species: "kakis",
    age: 7,
    gender: "male",
    breed: "Mājas kaķis",
    character: "Mierīgs",
    size: "Mazs",
    special: false,
    photo: "photo-1495360010541-f48722b34f7d",
  },
  {
    id: 22,
    name: "Mia",
    species: "kakis",
    age: 1,
    gender: "female",
    breed: "Mājas kaķis",
    character: "Draudzīga",
    size: "Mazs",
    special: false,
    photo: "photo-1533738363-b7f9aef128ce",
  },
  {
    id: 23,
    name: "Bobs",
    species: "suns",
    age: 5,
    gender: "male",
    breed: "Jauktenis",
    character: "Draudzīgs",
    size: "Liels",
    special: false,
    photo: "photo-1543466835-00a7907e9de1",
  },
  {
    id: 24,
    name: "Sāra",
    species: "suns",
    age: 3,
    gender: "female",
    breed: "Jauktenis",
    character: "Aktīva",
    size: "Vidējs",
    special: false,
    photo: "photo-1587300003388-59208cc962cb",
  },
];

const perPage = 8;
let currentPage = 1;
let currentCategory = "all";
let favorites = [];

try {
  favorites = JSON.parse(localStorage.getItem("pedasFavorites") || "[]");
  if (!Array.isArray(favorites)) favorites = [];
} catch {
  favorites = [];
}

const grid = document.getElementById("animalGrid");
const pagination = document.getElementById("pagination");
const resultsCount = document.getElementById("resultsCount");
const emptyMessage = document.getElementById("emptyMessage");
const searchInput = document.getElementById("searchInput");
const sortSelect = document.getElementById("sortSelect");

function formatAge(age) {
  if (age < 1) {
    return `${Math.round(age * 12)} mēneši`;
  }
  if (age === 1) return "1 gads";
  return `${age} gadi`;
}

function getFilteredAnimals() {
  const search = searchInput.value.toLocaleLowerCase("lv").trim();
  const species = document.querySelector('input[name="species"]:checked').value;
  const gender = document.querySelector('input[name="gender"]:checked').value;
  const selectedAges = [
    ...document.querySelectorAll('input[name="age"]:checked'),
  ].map((input) => input.value);
  const specialOnly = document.getElementById("specialFilter").checked;

  let result = animals.filter((animal) => {
    const text =
      `${animal.name} ${animal.breed} ${animal.character} ${animal.species}`.toLocaleLowerCase(
        "lv",
      );

    if (!text.includes(search)) return false;
    if (species !== "all" && animal.species !== species) return false;
    if (gender !== "all" && animal.gender !== gender) return false;
    if (specialOnly && !animal.special) return false;

    if (selectedAges.length > 0) {
      const matchesAge = selectedAges.some((group) => {
        if (group === "baby") return animal.age < 1;
        if (group === "young") return animal.age >= 1 && animal.age <= 3;
        if (group === "adult") return animal.age >= 4 && animal.age <= 7;
        if (group === "senior") return animal.age > 7;
        return false;
      });
      if (!matchesAge) return false;
    }

    if (currentCategory === "suns" && animal.species !== "suns") return false;
    if (currentCategory === "kakis" && animal.species !== "kakis") return false;
    if (
      currentCategory === "kucens" &&
      !(animal.species === "suns" && animal.age < 1)
    )
      return false;
    if (
      currentCategory === "kakens" &&
      !(animal.species === "kakis" && animal.age < 1)
    )
      return false;
    if (currentCategory === "special" && !animal.special) return false;

    return true;
  });

  if (sortSelect.value === "youngest") {
    result.sort((a, b) => a.age - b.age);
  } else if (sortSelect.value === "oldest") {
    result.sort((a, b) => b.age - a.age);
  } else if (sortSelect.value === "name") {
    result.sort((a, b) => a.name.localeCompare(b.name, "lv"));
  }

  return result;
}

function renderAnimals() {
  const filtered = getFilteredAnimals();
  const totalPages = Math.ceil(filtered.length / perPage);

  if (currentPage > totalPages) currentPage = Math.max(1, totalPages);

  const start = (currentPage - 1) * perPage;
  const visible = filtered.slice(start, start + perPage);

  resultsCount.textContent = `Atrasti: ${filtered.length} dzīvnieki`;
  emptyMessage.hidden = filtered.length !== 0;

  grid.innerHTML = visible
    .map((animal) => {
      const liked = favorites.includes(animal.id);
      const type = animal.species === "suns" ? "Suns" : "Kaķis";

      return `
        <article class="animal-card" data-id="${animal.id}">
          <div class="animal-photo">
            <img
              src="https://images.unsplash.com/${animal.photo}?auto=format&fit=crop&w=600&q=85"
              alt="${animal.name}"
              loading="lazy"
            >
            <button
              class="heart ${liked ? "liked" : ""}"
              data-id="${animal.id}"
              aria-label="Pievienot favorītiem"
              aria-pressed="${liked}"
              type="button"
            >${liked ? "♥" : "♡"}</button>
          </div>
          <div class="animal-info">
            <h3>${animal.name}</h3>
            <p>${type} • ${formatAge(animal.age)} • ${animal.character}</p>
            <div class="animal-tags">
              <span>${animal.character}</span>
              <span>${animal.size}</span>
              ${animal.special ? "<span>Īpašas vajadzības</span>" : ""}
            </div>
          </div>
        </article>
        `;
    })
    .join("");

  renderPagination(totalPages);
}

function renderPagination(totalPages) {
  pagination.innerHTML = "";
  if (totalPages <= 1) return;

  const previous = document.createElement("button");
  previous.textContent = "‹";
  previous.disabled = currentPage === 1;
  previous.setAttribute("aria-label", "Iepriekšējā lapa");
  previous.addEventListener("click", () => changePage(currentPage - 1));
  pagination.appendChild(previous);

  for (let page = 1; page <= totalPages; page++) {
    const button = document.createElement("button");
    button.textContent = page;
    if (page === currentPage) button.classList.add("current");
    button.setAttribute("aria-label", `Lapa ${page}`);
    button.addEventListener("click", () => changePage(page));
    pagination.appendChild(button);
  }

  const next = document.createElement("button");
  next.textContent = ">";
  next.disabled = currentPage === totalPages;
  next.setAttribute("aria-label", "Nākamā lapa");
  next.addEventListener("click", () => changePage(currentPage + 1));
  pagination.appendChild(next);
}

function changePage(page) {
  currentPage = page;
  renderAnimals();
  document.getElementById("catalog").scrollIntoView({
    behavior: "smooth",
    block: "start",
  });
}

function refreshFilters() {
  currentPage = 1;
  renderAnimals();
}

document.querySelectorAll(".category").forEach((button) => {
  button.addEventListener("click", () => {
    currentCategory = button.dataset.category;

    document.querySelectorAll(".category").forEach((item) => {
      item.classList.toggle("active", item === button);
    });

    refreshFilters();
  });
});

document.querySelectorAll(".sidebar input").forEach((input) => {
  input.addEventListener("change", refreshFilters);
});

searchInput.addEventListener("input", refreshFilters);
sortSelect.addEventListener("change", refreshFilters);

document.getElementById("clearFilters").addEventListener("click", () => {
  document.querySelector('input[name="species"][value="all"]').checked = true;
  document.querySelector('input[name="gender"][value="all"]').checked = true;

  document.querySelectorAll('input[name="age"]').forEach((input) => {
    input.checked = false;
  });

  document.getElementById("specialFilter").checked = false;
  searchInput.value = "";
  sortSelect.value = "default";
  currentCategory = "all";

  document.querySelectorAll(".category").forEach((button) => {
    button.classList.toggle("active", button.dataset.category === "all");
  });

  refreshFilters();
});

grid.addEventListener("click", (event) => {
  const button = event.target.closest(".heart");
  if (!button) return;

  const id = Number(button.dataset.id);

  if (favorites.includes(id)) {
    favorites = favorites.filter((item) => item !== id);
  } else {
    favorites.push(id);
  }

  try {
    localStorage.setItem("pedasFavorites", JSON.stringify(favorites));
  } catch {
    // Lapa darbojas arī tad, ja pārlūks bloķē glabāšanu.
  }

  renderAnimals();
});

document.querySelector(".donate").addEventListener("click", () => {
  alert("Ziedošanas sadaļa vēl tiek izstrādāta.");
});

document.querySelector(".register").addEventListener("click", () => {
  alert("Pieslēgšanās sadaļa vēl tiek izstrādāta.");
});

renderAnimals();
