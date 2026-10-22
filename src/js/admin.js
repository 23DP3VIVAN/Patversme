
"use strict";

// DEMO DATA - данные пока не сохраняются в MySQL

let animals = [];

let applications = [];

const users = [];

const messages = [];

const donations = [];

// HELPERS

function createElement(tag, text, className) {
    const element = document.createElement(tag);
    element.textContent = text;

    if (className) {
        element.className = className;
    }

    return element;
}

function makeButton(label, callback) {
    const button = createElement("button", label, "table-btn");
    button.type = "button";
    button.addEventListener("click", callback);
    return button;
}

function addCell(row, content) {
    const cell = document.createElement("td");

    if (content instanceof Node) {
        cell.appendChild(content);
    } else {
        cell.textContent = String(content);
    }

    row.appendChild(cell);
    return cell;
}

function statusBadge(status) {
    let className = "status";

    if (
        status === "Pieejams" ||
        status === "Apstiprināts" ||
        status === "Saņemts"
    ) {
        className += " success";
    }

    if (
        status === "Noraidīts" ||
        status === "Adoptēts"
    ) {
        className += " danger";
    }

    return createElement("span", status, className);
}

function makeTable(headers, items) {
    const table = document.createElement("table");
    const thead = document.createElement("thead");
    const tbody = document.createElement("tbody");

    const headRow = document.createElement("tr");

    headers.forEach(header => {
        const th = createElement("th", header);
        headRow.appendChild(th);
    });

    thead.appendChild(headRow);
    table.append(thead, tbody);

    items.forEach(item => {
        const row = document.createElement("tr");

        item.forEach(value => {
            addCell(row, value);
        });

        tbody.appendChild(row);
    });

    return table;
}

// NAVIGATION

const pageTitles = {
    overview: "Pārskats",
    animals: "Dzīvnieki",
    applications: "Adopcijas pieteikumi",
    users: "Lietotāji",
    messages: "Ziņojumi",
    donations: "Ziedojumi"
};

function showPage(pageId) {
    if (!pageTitles[pageId]) return;

    document.querySelectorAll(".admin-page").forEach(page => {
        page.classList.toggle("active", page.id === pageId);
    });

    document.querySelectorAll(".nav-link").forEach(button => {
        button.classList.toggle(
            "active",
            button.dataset.page === pageId
        );
    });

    document.getElementById("pageTitle").textContent =
        pageTitles[pageId];

    window.scrollTo(0, 0);
}

document.querySelectorAll(".nav-link").forEach(button => {
    button.addEventListener("click", () => {
        showPage(button.dataset.page);
    });
});

document.querySelectorAll("[data-go]").forEach(button => {
    button.addEventListener("click", () => {
        showPage(button.dataset.go);

        if (
            button.dataset.go === "animals" &&
            button.textContent.includes("Pievienot")
        ) {
            openAnimalForm();
        }
    });
});

// STATISTICS

function updateStatistics() {
    document.getElementById("animalCount").textContent =
        animals.length;

    document.getElementById("applicationCount").textContent =
        applications.length;

    document.getElementById("userCount").textContent =
        users.length;

    const total = donations.reduce(
        (sum, donation) => sum + donation.amount,
        0
    );

    document.getElementById("donationTotal").textContent =
        total.toFixed(2) + " €";
}

// ANIMALS

const animalDialog = document.getElementById("animalDialog");
const animalForm = document.getElementById("animalForm");

function openAnimalForm(id = null) {
    animalForm.reset();

    document.getElementById("editAnimalId").value = "";
    document.getElementById("modalTitle").textContent =
        "Pievienot dzīvnieku";

    if (id !== null) {
        const animal = animals.find(item => item.id === id);

        if (!animal) return;

        document.getElementById("modalTitle").textContent =
            "Rediģēt dzīvnieku";

        document.getElementById("editAnimalId").value = animal.id;
        document.getElementById("animalName").value = animal.name;
        document.getElementById("animalType").value = animal.type;
        document.getElementById("animalAge").value = animal.age;
        document.getElementById("animalStatus").value = animal.status;
        document.getElementById("animalDescription").value =
            animal.description;
    }

    animalDialog.showModal();
}

document.getElementById("addAnimalBtn")
    .addEventListener("click", () => openAnimalForm());

document.getElementById("closeModal")
    .addEventListener("click", () => animalDialog.close());

animalDialog.addEventListener("click", event => {
    if (event.target === animalDialog) {
        animalDialog.close();
    }
});

animalForm.addEventListener("submit", event => {
    event.preventDefault();

    const idValue = document.getElementById("editAnimalId").value;

    const animalData = {
        name: document.getElementById("animalName").value.trim(),
        type: document.getElementById("animalType").value,
        age: document.getElementById("animalAge").value.trim(),
        status: document.getElementById("animalStatus").value,
        description: document.getElementById("animalDescription").value.trim()
    };

    if (!animalData.name || !animalData.age) return;

    if (idValue) {
        const animal = animals.find(
            item => item.id === Number(idValue)
        );

        if (animal) {
            Object.assign(animal, animalData);
        }
    } else {
        const nextId = Math.max(0, ...animals.map(a => a.id)) + 1;

        animals.push({
            id: nextId,
            ...animalData
        });
    }

    animalDialog.close();
    renderAnimals();
    updateStatistics();
});

function renderAnimals() {
    const search = document.getElementById("animalSearch")
        .value.toLowerCase();

    const filter = document.getElementById("animalFilter").value;

    const filtered = animals.filter(animal => {
        const matchesName = animal.name.toLowerCase().includes(search);
        const matchesType = filter === "all" || animal.type === filter;

        return matchesName && matchesType;
    });

    const tbody = document.getElementById("animalsTable");
    tbody.replaceChildren();

    filtered.forEach(animal => {
        const row = document.createElement("tr");

        addCell(row, animal.name);
        addCell(row, animal.type);
        addCell(row, animal.age);
        addCell(row, statusBadge(animal.status));

        const actions = document.createElement("div");

        actions.append(
            makeButton("Rediģēt", () => openAnimalForm(animal.id)),
            makeButton("Dzēst", () => {
                if (!confirm("Vai tiešām dzēst šo dzīvnieku?")) {
                    return;
                }

                animals = animals.filter(item => item.id !== animal.id);
                renderAnimals();
                updateStatistics();
            })
        );

        addCell(row, actions);
        tbody.appendChild(row);
    });

    if (filtered.length === 0) {
        const row = document.createElement("tr");
        const cell = addCell(row, "Dzīvnieki nav atrasti.");
        cell.colSpan = 5;
        cell.className = "empty";
        tbody.appendChild(row);
    }
}

document.getElementById("animalSearch")
    .addEventListener("input", renderAnimals);

document.getElementById("animalFilter")
    .addEventListener("change", renderAnimals);

// APPLICATIONS

function changeApplicationStatus(id, newStatus) {
    const application = applications.find(item => item.id === id);

    if (!application) return;

    application.status = newStatus;

    renderApplications();
    renderRecentApplications();
}

function renderApplications() {
    const filter = document.getElementById("applicationFilter").value;
    const tbody = document.getElementById("applicationsTable");

    tbody.replaceChildren();

    const filtered = applications.filter(item =>
        filter === "all" || item.status === filter
    );

    filtered.forEach(application => {
        const row = document.createElement("tr");

        addCell(row, application.animal);
        addCell(row, application.applicant);
        addCell(row, application.date);
        addCell(row, statusBadge(application.status));

        const actions = document.createElement("div");

        actions.append(
            makeButton("Izskatīt", () =>
                changeApplicationStatus(application.id, "Izskatīšanā")
            ),
            makeButton("Apstiprināt", () =>
                changeApplicationStatus(application.id, "Apstiprināts")
            ),
            makeButton("Noraidīt", () =>
                changeApplicationStatus(application.id, "Noraidīts")
            )
        );

        addCell(row, actions);
        tbody.appendChild(row);
    });

    if (filtered.length === 0) {
        const row = document.createElement("tr");
        const cell = addCell(row, "Pieteikumi nav atrasti.");
        cell.colSpan = 5;
        cell.className = "empty";
        tbody.appendChild(row);
    }
}

document.getElementById("applicationFilter")
    .addEventListener("change", renderApplications);

function renderRecentApplications() {
    const container = document.getElementById("recentApplications");

    const rows = applications.slice(0, 3).map(item => [
        item.animal,
        item.applicant,
        item.date,
        statusBadge(item.status)
    ]);

    const wrapper = createElement("div", "", "table-scroll");

    wrapper.appendChild(
        makeTable(
            ["Dzīvnieks", "Pieteicējs", "Datums", "Statuss"],
            rows
        )
    );

    container.replaceChildren(wrapper);
}

// USERS

function renderUsers() {
    const tbody = document.getElementById("usersTable");
    tbody.replaceChildren();

    users.forEach(user => {
        const row = document.createElement("tr");

        addCell(row, user.name);
        addCell(row, user.email);
        addCell(row, user.role);

        tbody.appendChild(row);
    });
}

// MESSAGES

function renderMessages() {
    const container = document.getElementById("messagesList");
    container.replaceChildren();

    messages.forEach(message => {
        const card = createElement("article", "", "message-card");

        const title = createElement("h3", message.subject);
        const sender = createElement(
            "small",
            message.name + " — " + message.email
        );
        const content = createElement("p", message.message);

        card.append(title, sender, content);
        container.appendChild(card);
    });
}

// DONATIONS

function renderDonations() {
    const tbody = document.getElementById("donationsTable");
    tbody.replaceChildren();

    donations.forEach(donation => {
        const row = document.createElement("tr");

        addCell(row, donation.name);
        addCell(row, donation.amount.toFixed(2) + " €");
        addCell(row, donation.date);
        addCell(row, statusBadge(donation.status));

        tbody.appendChild(row);
    });
}

// LOGOUT DEMO

document.getElementById("logoutBtn")
    .addEventListener("click", () => {
        alert(
            "Demonstrācijas režīms: īsta autorizācija vēl nav pieslēgta."
        );
    });

// INITIAL RENDER

renderAnimals();
renderApplications();
renderRecentApplications();
renderUsers();
renderMessages();
renderDonations();
updateStatistics();
