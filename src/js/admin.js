"use strict";

const API = "back-end/admin.php";
let animals = [];
let applications = [];
let users = [];
let messages = [];
let donations = [];
let editingAnimal = null;
let selectedPhoto = null;
let requestTimer = null;
let totalUsers = 0;
let summary = {};
let filterTimer = null;

const pageTitles = {
  overview: "Pārskats",
  animals: "Dzīvnieki",
  applications: "Adopcijas pieteikumi",
  users: "Lietotāji",
  messages: "Ziņojumi",
  donations: "Ziedojumi",
};

function showNotice(message, isError = false) {
  let notice = document.getElementById("adminNotice");
  if (!notice) {
    notice = document.createElement("p");
    notice.id = "adminNotice";
    notice.setAttribute("role", "status");
    document.querySelector(".topbar").appendChild(notice);
  }
  notice.textContent = message;
  notice.style.color = isError ? "#b42318" : "#258044";
}

async function api(action, options = {}) {
  const { params, ...requestOptions } = options;
  const url = new URL(API, window.location.href);
  url.searchParams.set("action", action);
  if (params) {
    Object.entries(params).forEach(([key, value]) => {
      if (value) url.searchParams.set(key, value);
    });
  }
  const response = await fetch(url, {
    credentials: "same-origin",
    headers: {
      Accept: "application/json",
      ...(requestOptions.body ? { "Content-Type": "application/json" } : {}),
    },
    ...requestOptions,
  });
  const result = await response.json();
  if (response.status === 401) {
    window.location.href = "register.html?mode=login";
    throw new Error("Piesakieties administratora kontā.");
  }
  if (!response.ok) throw new Error(result.error || "Pieprasījums neizdevās.");
  return result;
}

function createElement(tag, text, className) {
  const element = document.createElement(tag);
  element.textContent = text ?? "";
  if (className) element.className = className;
  return element;
}

function addCell(row, value) {
  const cell = document.createElement("td");
  if (value instanceof Node) cell.appendChild(value);
  else cell.textContent = value == null ? "" : String(value);
  row.appendChild(cell);
  return cell;
}

function makeButton(label, callback) {
  const button = createElement("button", label, "table-btn");
  button.type = "button";
  button.addEventListener("click", callback);
  return button;
}

function statusBadge(status) {
  const badge = createElement("span", status, "status");
  if (["Pieejams", "Apstiprināta", "Pabeigta"].includes(status))
    badge.classList.add("success");
  if (["Noraidīta", "Atcelta", "Adoptets"].includes(status))
    badge.classList.add("danger");
  return badge;
}

function showPage(pageId) {
  if (!pageTitles[pageId]) return;
  document.querySelectorAll(".admin-page").forEach((page) => {
    page.classList.toggle("active", page.id === pageId);
  });
  document.querySelectorAll(".nav-link").forEach((button) => {
    button.classList.toggle("active", button.dataset.page === pageId);
  });
  document.getElementById("pageTitle").textContent = pageTitles[pageId];
  window.scrollTo(0, 0);
}

document.querySelectorAll(".nav-link").forEach((button) => {
  button.addEventListener("click", () => showPage(button.dataset.page));
});
document.querySelectorAll("[data-go]").forEach((button) => {
  button.addEventListener("click", () => {
    showPage(button.dataset.go);
    if (button.textContent.includes("Pievienot")) openAnimalForm();
  });
});

function updateStatistics(total) {
  document.getElementById("animalCount").textContent = summary.animals ?? animals.length;
  document.getElementById("availableAnimalCount").textContent = summary.available ?? 0;
  document.getElementById("applicationCount").textContent = summary.applications ?? applications.length;
  document.getElementById("newApplicationCount").textContent = summary.newApplications ?? 0;
  document.getElementById("userCount").textContent = summary.users ?? totalUsers ?? users.length;
  document.getElementById("donationTotal").textContent =
    `${Number(total ?? summary.donations ?? 0).toFixed(2)} €`;
}

const animalDialog = document.getElementById("animalDialog");
const animalForm = document.getElementById("animalForm");

function clearAnimalForm() {
  animalForm.reset();
  editingAnimal = null;
  selectedPhoto = null;
  animalForm.dataset.removePhoto = "false";
  document.getElementById("editAnimalId").value = "";
  document.getElementById("modalTitle").textContent = "Pievienot dzīvnieku";
  document.getElementById("animalArrivalDate").value = new Date()
    .toISOString()
    .slice(0, 10);
  document.getElementById("animalPhoto").value = "";
  const preview = document.getElementById("photoPreview");
  preview.removeAttribute("src");
  preview.style.display = "none";
}

function openAnimalForm(id = null) {
  clearAnimalForm();
  if (id !== null) {
    editingAnimal = animals.find((animal) => Number(animal.id) === Number(id));
    if (!editingAnimal) return;
    document.getElementById("modalTitle").textContent = "Rediģēt dzīvnieku";
    document.getElementById("editAnimalId").value = editingAnimal.id;
    document.getElementById("animalName").value = editingAnimal.name;
    document.getElementById("animalType").value = editingAnimal.species;
    document.getElementById("animalBreed").value = editingAnimal.breed;
    document.getElementById("animalGender").value = editingAnimal.gender || "";
    document.getElementById("animalAge").value = editingAnimal.age ?? "";
    document.getElementById("animalHealth").value = editingAnimal.health_status;
    document.getElementById("animalSpecialNeeds").checked = Boolean(
      Number(editingAnimal.special_needs),
    );
    document.getElementById("animalBirthDate").value =
      editingAnimal.birth_date || "";
    document.getElementById("animalArrivalDate").value =
      editingAnimal.arrival_date;
    document.getElementById("animalStatus").value = editingAnimal.status;
    document.getElementById("animalDescription").value =
      editingAnimal.description;
    document.getElementById("animalRequirements").value =
      editingAnimal.owner_requirements;
    const preview = document.getElementById("photoPreview");
    preview.src = editingAnimal.photo || "src/img/logo.jpg";
    preview.style.display = "block";
  }
  animalDialog.showModal();
}

document
  .getElementById("addAnimalBtn")
  .addEventListener("click", () => openAnimalForm());
document
  .getElementById("closeModal")
  .addEventListener("click", () => animalDialog.close());
animalDialog.addEventListener("close", clearAnimalForm);
animalDialog.addEventListener("click", (event) => {
  if (event.target === animalDialog) animalDialog.close();
});

document.getElementById("animalPhoto").addEventListener("change", () => {
  const file = document.getElementById("animalPhoto").files?.[0];
  if (!file) return;
  if (
    !["image/jpeg", "image/png", "image/webp"].includes(file.type) ||
    file.size > 2 * 1024 * 1024
  ) {
    showNotice("Izvēlieties JPG, PNG vai WEBP attēlu līdz 2 MB.", true);
    document.getElementById("animalPhoto").value = "";
    return;
  }
  selectedPhoto = file;
  animalForm.dataset.removePhoto = "false";
  const preview = document.getElementById("photoPreview");
  preview.src = URL.createObjectURL(file);
  preview.style.display = "block";
});

document.getElementById("removeAnimalPhoto").addEventListener("click", () => {
  selectedPhoto = null;
  animalForm.dataset.removePhoto = "true";
  document.getElementById("animalPhoto").value = "";
  const preview = document.getElementById("photoPreview");
  preview.src = "src/img/logo.jpg";
  preview.style.display = "block";
});

function readImage(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.addEventListener("load", () => resolve(reader.result));
    reader.addEventListener("error", () =>
      reject(new Error("Neizdevās nolasīt attēlu.")),
    );
    reader.readAsDataURL(file);
  });
}

animalForm.addEventListener("submit", async (event) => {
  event.preventDefault();
  const submit = animalForm.querySelector('button[type="submit"]');
  submit.disabled = true;
  try {
    const payload = {
      name: document.getElementById("animalName").value.trim(),
      species: document.getElementById("animalType").value,
      breed: document.getElementById("animalBreed").value.trim(),
      gender: document.getElementById("animalGender").value,
      age: document.getElementById("animalAge").value,
      health_status: document.getElementById("animalHealth").value.trim(),
      special_needs: document.getElementById("animalSpecialNeeds").checked,
      birth_date: document.getElementById("animalBirthDate").value,
      arrival_date: document.getElementById("animalArrivalDate").value,
      status: document.getElementById("animalStatus").value,
      description: document.getElementById("animalDescription").value.trim(),
      owner_requirements: document
        .getElementById("animalRequirements")
        .value.trim(),
      photo: selectedPhoto
        ? await readImage(selectedPhoto)
        : animalForm.dataset.removePhoto === "true"
          ? "src/img/logo.jpg"
          : editingAnimal?.photo || "src/img/logo.jpg",
    };
    if (editingAnimal) {
      payload.animal_id = Number(editingAnimal.id);
      await api("animals", { method: "PUT", body: JSON.stringify(payload) });
      showNotice("Dzīvnieks atjaunināts.");
    } else {
      await api("animals", { method: "POST", body: JSON.stringify(payload) });
      showNotice("Dzīvnieks pievienots.");
    }
    animalDialog.close();
    await loadAnimals();
  } catch (error) {
    showNotice(error.message || "Neizdevās saglabāt dzīvnieku.", true);
  } finally {
    submit.disabled = false;
  }
});

function renderAnimals() {
  const tbody = document.getElementById("animalsTable");
  tbody.replaceChildren();
  animals.forEach((animal) => {
    const row = document.createElement("tr");
    addCell(row, `#${animal.id} ${animal.name}`);
    addCell(row, animal.species === "suns" ? "Suns" : animal.species === "kakis" ? "Kaķis" : animal.species);
    addCell(row, animal.display_age == null ? "Nav norādīts" : `${animal.display_age} gadi`);
    addCell(row, statusBadge(animal.status));
    const actions = document.createElement("div");
    actions.append(
      makeButton("Rediģēt", () => openAnimalForm(animal.id)),
      makeButton("Publiskā karte", () => {
        window.open(`animals.html?id=${encodeURIComponent(animal.id)}`, "_blank", "noopener");
      }),
      makeButton("Pieteikumu vēsture", () => {
        document.getElementById("applicationSearch").value = "";
        document.getElementById("applicationDateFilter").value = "";
        document.getElementById("applicationFilter").value = "all";
        document.getElementById("applicationAnimalFilter").value = String(animal.id);
        showPage("applications");
        loadApplications().catch((error) => showNotice(error.message, true));
      }),
      makeButton("Dzēst", async () => {
        if (!window.confirm(`Vai tiešām dzēst dzīvnieku #${animal.id}?`))
          return;
        try {
          await api("animals", { method: "DELETE", params: { id: animal.id } });
          await loadAnimals();
          showNotice("Dzīvnieks dzēsts.");
        } catch (error) {
          showNotice(error.message || "Neizdevās dzēst dzīvnieku.", true);
        }
      }),
    );
    addCell(row, actions);
    tbody.appendChild(row);
  });
  if (!animals.length) {
    const row = document.createElement("tr");
    addCell(row, "Dzīvnieki nav atrasti.").colSpan = 5;
    tbody.appendChild(row);
  }
}

async function loadAnimals(params = {}) {
  const result = await api("animals", { params });
  animals = result.animals;
  renderAnimals();
}

function loadFilteredAnimals() {
  clearTimeout(filterTimer);
  filterTimer = setTimeout(() => {
    const value = (id) => document.getElementById(id).value.trim();
    loadAnimals({
      q: value("animalSearch"),
      species: value("animalFilter") === "all" ? "" : value("animalFilter"),
      breed: value("animalBreedFilter"),
      health: value("animalHealthFilter"),
      age_min: value("animalAgeMin"),
      age_max: value("animalAgeMax"),
      gender: value("animalGenderFilter") === "all" ? "" : value("animalGenderFilter"),
      status: value("animalStatusFilter") === "all" ? "" : value("animalStatusFilter"),
      sort: value("animalSort"),
    }).catch((error) => showNotice(error.message, true));
  }, 250);
}
["animalSearch", "animalBreedFilter", "animalHealthFilter", "animalAgeMin", "animalAgeMax"].forEach((id) =>
  document.getElementById(id).addEventListener("input", loadFilteredAnimals),
);
["animalFilter", "animalGenderFilter", "animalStatusFilter", "animalSort"].forEach((id) =>
  document.getElementById(id).addEventListener("change", loadFilteredAnimals),
);

function renderApplications() {
  const tbody = document.getElementById("applicationsTable");
  tbody.replaceChildren();
  applications.forEach((application) => {
    const row = document.createElement("tr");
    const animalLink = createElement("a", `#${application.id} · ${application.animal}`);
    animalLink.href = `animals.html?id=${encodeURIComponent(application.animal_id)}`;
    addCell(row, animalLink);
    const applicant = createElement("a", `${application.applicant} (${application.email})`);
    applicant.href = `mailto:${encodeURIComponent(application.email)}`;
    addCell(row, applicant);
    addCell(row, application.date);
    addCell(row, statusBadge(application.status));
    const actions = document.createElement("div");
    if (application.application_message || application.rejection_reason) {
      actions.appendChild(
        makeButton("Ziņa", () =>
          window.alert(
            [
              application.application_message
                ? `Pieteikuma ziņa: ${application.application_message}`
                : "",
              application.rejection_reason
                ? `Atteikuma iemesls: ${application.rejection_reason}`
                : "",
            ]
              .filter(Boolean)
              .join("\n\n"),
          ),
      );
  )}
    if (application.status === "Gaida") {
      actions.appendChild(
        makeButton("Izskatīt", () =>
          changeApplicationStatus(application.id, "Izskatīšanā"),
        ),
      );
    }
    if (["Gaida", "Izskatīšanā"].includes(application.status)) {
      actions.appendChild(makeButton("Apstiprināt", () => {
        const warning = Number(application.other_active_count) > 0
          ? `Šim dzīvniekam ir vēl ${application.other_active_count} aktīvs pieteikums. Apstiprinot šo, pārējie tiks noraidīti. Turpināt?`
          : "Apstiprināt šo pieteikumu?";
        if (window.confirm(warning)) changeApplicationStatus(application.id, "Apstiprināta");
      }));
      actions.appendChild(
        makeButton("Noraidīt", async () => {
          const reason = window.prompt("Norādiet atteikuma iemeslu (neobligāti):");
          if (reason === null) return;
          await changeApplicationStatus(application.id, "Noraidīta", reason);
        },
        ),
      );
    }
    if (application.status === "Apstiprināta") {
      actions.appendChild(makeButton("Pabeigt adopciju", () => {
        if (window.confirm("Apstiprināt, ka dzīvnieks nodots jaunajam saimniekam?")) {
          changeApplicationStatus(application.id, "Pabeigta");
        }
      }));
    }
    addCell(row, actions);
    tbody.appendChild(row);
  });
  if (!applications.length) {
    const row = document.createElement("tr");
    addCell(row, "Pieteikumi nav atrasti.").colSpan = 5;
    tbody.appendChild(row);
  }
}

async function changeApplicationStatus(id, status, rejectionReason = "") {
  try {
    await api("adoptions", {
      method: "PATCH",
      body: JSON.stringify({
        adoption_id: id,
        status,
        rejection_reason: rejectionReason,
      }),
    });
    await Promise.all([loadApplications(), loadAnimals()]);
    showNotice("Pieteikuma statuss atjaunināts.");
  } catch (error) {
    showNotice(
      error.message || "Neizdevās atjaunināt pieteikuma statusu.",
      true,
    );
  }
}

async function loadApplications() {
  const value = (id) => document.getElementById(id).value.trim();
  const result = await api("adoptions", {
    params: {
      q: value("applicationSearch"),
      status: value("applicationFilter") === "all" ? "" : value("applicationFilter"),
      date: value("applicationDateFilter"),
      animal: value("applicationAnimalFilter"),
    },
  });
  applications = result.adoptions;
  renderApplications();
  renderRecentApplications();
  updateStatistics();
}

function renderRecentApplications() {
  const container = document.getElementById("recentApplications");
  const table = document.createElement("table");
  const head = document.createElement("tr");
  ["Pieteikums", "Dzīvnieks", "Pieteicējs", "Datums", "Statuss"].forEach(
    (title) => {
      head.appendChild(createElement("th", title));
    },
  );
  const thead = document.createElement("thead");
  thead.appendChild(head);
  const tbody = document.createElement("tbody");
  applications.slice(0, 5).forEach((application) => {
    const row = document.createElement("tr");
    addCell(row, `#${application.id}`);
    addCell(row, application.animal);
    addCell(row, application.applicant);
    addCell(row, application.date);
    addCell(row, statusBadge(application.status));
    tbody.appendChild(row);
  });
  table.append(thead, tbody);
  container.replaceChildren(table);
}

function renderUsers() {
  const tbody = document.getElementById("usersTable");
  tbody.replaceChildren();
  users.forEach((user) => {
      const row = document.createElement("tr");
      addCell(row, user.user_id);
      addCell(row, `${user.first_name} ${user.last_name}`);
      addCell(row, user.email);
      addCell(row, user.role);
      addCell(row, user.created_at);
      tbody.appendChild(row);
    });
}

async function loadUsers() {
  const result = await api("users", {
    params: { q: document.getElementById("userSearch").value.trim() },
  });
  users = result.users;
  totalUsers = result.total;
  renderUsers();
  updateStatistics();
}
document.getElementById("userSearch").addEventListener("input", () => {
  clearTimeout(requestTimer);
  requestTimer = setTimeout(
    () => loadUsers().catch((error) => showNotice(error.message, true)),
    250,
  );
});

function renderMessages() {
  const container = document.getElementById("messagesList");
  const cards = messages.map((message) => {
    const card = createElement("article", "", "message-card");
    const email = createElement("a", message.email);
    email.href = `mailto:${encodeURIComponent(message.email)}`;
    card.append(
      createElement("h3", message.subject),
      createElement("small", `${message.first_name} ${message.last_name} · ${message.type} · ${message.created_at} · ${Number(message.is_processed) ? "Apstrādāts" : "Neapstrādāts"}`),
      email,
      createElement("p", message.message),
    );
    card.appendChild(makeButton(Number(message.is_processed) ? "Atzīmēt kā neapstrādātu" : "Atzīmēt kā apstrādātu", async () => {
      try {
        await api("messages", {
          method: "PATCH",
          body: JSON.stringify({ id: Number(message.id), type: message.type, is_processed: !Number(message.is_processed) }),
        });
        await loadMessages();
      } catch (error) {
        showNotice(error.message, true);
      }
    }));
    return card;
  });
  if (!cards.length) {
    cards.push(createElement("p", "Nav saņemtu ziņojumu."));
  }
  container.replaceChildren(...cards);
}

async function loadMessages() {
  const result = await api("messages", {
    params: {
      q: document.getElementById("messageSearch").value.trim(),
      date: document.getElementById("messageDateFilter").value,
      processed: document.getElementById("messageStatusFilter").value === "all"
        ? ""
        : document.getElementById("messageStatusFilter").value === "processed" ? "1" : "0",
    },
  });
  messages = result.messages;
  renderMessages();
}

function renderDonations() {
  const tbody = document.getElementById("donationsTable");
  tbody.replaceChildren();
  donations.forEach((donation) => {
    const row = document.createElement("tr");
    addCell(row, `#${donation.id}`);
    addCell(row, donation.donor_name);
    addCell(row, donation.donor_email);
    addCell(row, `${Number(donation.amount).toFixed(2)} €`);
    addCell(row, donation.created_at);
    addCell(row, donation.payment_method);
    addCell(
      row,
      donation.status === "succeeded"
        ? "Apstiprināts"
        : donation.status === "pending"
          ? "Gaida maksājuma apstiprinājumu"
          : donation.status === "failed"
            ? "Nav pabeigts"
            : "Nav apstrādāts (demonstrācija)",
    );
    tbody.appendChild(row);
  });
  document.getElementById("donationPeriodTotal").textContent =
    `Apstiprināto ziedojumu summa izvēlētajā periodā: ${Number(donationTotal).toFixed(2)} €`;
}

let donationTotal = 0;

async function loadDonations() {
  const value = (id) => document.getElementById(id).value.trim();
  const result = await api("donations", {
    params: {
      q: value("donationSearch"),
      from: value("donationDateFrom"),
      to: value("donationDateTo"),
      min: value("donationMinAmount"),
      max: value("donationMaxAmount"),
      status: value("donationStatusFilter") === "all" ? "" : value("donationStatusFilter"),
    },
  });
  donations = result.donations;
  donationTotal = result.total;
  renderDonations();
  updateStatistics(donationTotal);
}

async function loadAll() {
  try {
    const [animalData, applicationData, userData, messageData, donationData, summaryData] =
      await Promise.all([
        api("animals"),
        api("adoptions"),
        api("users"),
        api("messages"),
        api("donations"),
        api("summary"),
      ]);
    animals = animalData.animals;
    applications = applicationData.adoptions;
    users = userData.users;
    totalUsers = userData.total;
    messages = messageData.messages;
    donations = donationData.donations;
    donationTotal = donationData.total;
    summary = summaryData.summary;
    renderAnimals();
    renderApplications();
    renderRecentApplications();
    renderUsers();
    renderMessages();
    renderDonations();
    updateStatistics(donationData.total);
  } catch (error) {
    showNotice(
      error.message || "Neizdevās ielādēt administratora paneli.",
      true,
    );
  }
}

function scheduleApplicationLoad() {
  clearTimeout(filterTimer);
  filterTimer = setTimeout(() => loadApplications().catch((error) => showNotice(error.message, true)), 250);
}
["applicationSearch", "applicationAnimalFilter"].forEach((id) =>
  document.getElementById(id).addEventListener("input", scheduleApplicationLoad),
);
["applicationDateFilter", "applicationFilter"].forEach((id) =>
  document.getElementById(id).addEventListener("change", scheduleApplicationLoad),
);

["messageSearch"].forEach((id) =>
  document.getElementById(id).addEventListener("input", () => {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => loadMessages().catch((error) => showNotice(error.message, true)), 250);
  }),
);
["messageDateFilter", "messageStatusFilter"].forEach((id) =>
  document.getElementById(id).addEventListener("change", () =>
    loadMessages().catch((error) => showNotice(error.message, true)),
  ),
);

document.getElementById("donationSearch").addEventListener("input", () => {
  clearTimeout(filterTimer);
  filterTimer = setTimeout(() => loadDonations().catch((error) => showNotice(error.message, true)), 250);
});
["donationDateFrom", "donationDateTo", "donationMinAmount", "donationMaxAmount", "donationStatusFilter"].forEach((id) =>
  document.getElementById(id).addEventListener("change", () =>
    loadDonations().catch((error) => showNotice(error.message, true)),
  ),
);
document.getElementById("logoutBtn").addEventListener("click", async () => {
  try {
    const response = await fetch("back-end/logout.php", {
      method: "POST",
      credentials: "same-origin",
      headers: { Accept: "application/json" },
    });
    if (!response.ok) throw new Error("Neizdevās izrakstīties.");
    window.location.href = "register.html?mode=login";
  } catch (error) {
    showNotice(error.message || "Neizdevās izrakstīties.", true);
  }
});

loadAll();
