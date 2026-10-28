"use strict";

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("profileForm");
  if (!form) return;

  const byId = (id) => document.getElementById(id);
  const fields = {
    firstName: byId("firstName"),
    lastName: byId("lastName"),
    birthDay: byId("birthDay"),
    birthMonth: byId("birthMonth"),
    birthYear: byId("birthYear"),
    email: byId("email"),
    description: byId("description"),
  };
  const API_URL = "back-end/profile.php";
  const DEFAULT_PHOTO = "src/img/logo.jpg";
  const profilePhoto = byId("profilePhoto");
  const navPhoto = byId("navPhoto");
  const photoInput = byId("photoInput");
  const changePhoto = byId("changePhoto");
  const removePhoto = byId("removePhoto");
  let originalData = null;
  let selectedPhoto = null;
  let currentPhoto = null;
  let photoRemoved = false;
  let previewUrl = null;

  function showMessage(message, isError = false) {
    let node = byId("profileMessage");
    if (!node) {
      node = document.createElement("p");
      node.id = "profileMessage";
      node.setAttribute("role", "status");
      form.prepend(node);
    }
    node.textContent = message;
    node.style.color = isError ? "#c62828" : "#258044";
    node.style.display = "block";
  }

  async function api(action, { method = "GET", data = null } = {}) {
    const url = new URL(API_URL, window.location.href);
    url.searchParams.set("action", action);
    const options = {
      method,
      credentials: "same-origin",
      headers: { Accept: "application/json" },
    };
    if (data) {
      options.headers["Content-Type"] = "application/json";
      options.body = JSON.stringify(data);
    }
    const response = await fetch(url, options);
    const result = await response.json();
    if (response.status === 401) {
      window.location.href = "register.html?mode=login";
      throw new Error("Lūdzu, piesakieties savā kontā.");
    }
    if (!response.ok) {
      throw new Error(result.error || "Pieprasījums neizdevās.");
    }
    return result;
  }

  function updatePhoto() {
    const url = selectedPhoto ? previewUrl : currentPhoto || DEFAULT_PHOTO;
    profilePhoto.src = url;
    navPhoto.src = url;
    changePhoto.textContent =
      currentPhoto || selectedPhoto ? "Mainīt bildi" : "Pievienot bildi";
    removePhoto.hidden = !currentPhoto && !selectedPhoto;
  }

  function readForm() {
    return {
      firstName: fields.firstName.value.trim(),
      lastName: fields.lastName.value.trim(),
      email: fields.email.value.trim(),
      description: fields.description.value,
      birthDay: fields.birthDay.value,
      birthMonth: fields.birthMonth.value,
      birthYear: fields.birthYear.value,
    };
  }

  function getBirthDate(data) {
    if (!data.birthDay && !data.birthMonth && !data.birthYear) return "";
    if (!data.birthDay || !data.birthMonth || !data.birthYear) {
      throw new Error(
        "Norādiet pilnu dzimšanas datumu vai atstājiet visus datumus tukšus.",
      );
    }
    const date = new Date(
      Date.UTC(
        Number(data.birthYear),
        Number(data.birthMonth) - 1,
        Number(data.birthDay),
      ),
    );
    if (
      date.getUTCFullYear() !== Number(data.birthYear) ||
      date.getUTCMonth() !== Number(data.birthMonth) - 1 ||
      date.getUTCDate() !== Number(data.birthDay)
    ) {
      throw new Error("Ievadiet derīgu dzimšanas datumu.");
    }
    return `${data.birthYear}-${String(data.birthMonth).padStart(2, "0")}-${String(data.birthDay).padStart(2, "0")}`;
  }

  function fillProfile(user) {
    fields.firstName.value = user.firstName || "";
    fields.lastName.value = user.lastName || "";
    fields.email.value = user.email || "";
    fields.description.value = user.description || "";
    if (user.birthDate) {
      const [year, month, day] = user.birthDate.split("-");
      fields.birthYear.value = year;
      fields.birthMonth.value = Number(month);
      fields.birthDay.value = Number(day);
    } else {
      fields.birthYear.value = "";
      fields.birthMonth.value = "";
      fields.birthDay.value = "";
    }
    currentPhoto = user.photo || null;
    updatePhoto();
  }

  function fileAsDataUrl(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.addEventListener("load", () => resolve(reader.result));
      reader.addEventListener("error", () =>
        reject(new Error("Neizdevās nolasīt attēlu.")),
      );
      reader.readAsDataURL(file);
    });
  }

  async function loadProfile() {
    const result = await api("profile");
    fillProfile(result.user);
    originalData = readForm();
  }

  function addText(parent, tag, text, className = "") {
    const node = document.createElement(tag);
    node.textContent = text ?? "";
    if (className) node.className = className;
    parent.appendChild(node);
    return node;
  }

  function openAnimalLink(animalId, parent, label) {
    const link = document.createElement("a");
    link.href = `animals.html?id=${encodeURIComponent(animalId)}`;
    link.textContent = label;
    parent.appendChild(link);
  }

  async function loadApplications() {
    const container = byId("applicationsList");
    container.replaceChildren();
    const result = await api("adoptions");
    if (!result.adoptions.length) {
      addText(container, "p", "Tev vēl nav adopcijas pieteikumu.");
      return;
    }

    result.adoptions.forEach((application) => {
      const card = addText(container, "article", "", "application-card");
      const details = addText(card, "details", "");
      addText(
        details,
        "summary",
        `#${application.adoption_id} · ${application.animal_name} · ${application.status}`,
      );
      addText(details, "p", `Iesniegts: ${application.adoption_date}`);
      addText(details, "p", `Dzīvnieka statuss: ${application.animal_status}`);
      if (application.application_message) {
        addText(
          details,
          "p",
          `Pieteikuma ziņa: ${application.application_message}`,
        );
      }
      if (application.status === "Noraidīta" && application.rejection_reason) {
        addText(
          details,
          "p",
          `Atteikuma iemesls: ${application.rejection_reason}`,
        );
      }
      openAnimalLink(application.animal_id, details, "Atvērt dzīvnieku");
      if (["Gaida", "Izskatīšanā"].includes(application.status)) {
        const cancel = addText(details, "button", "Atcelt pieteikumu");
        cancel.type = "button";
        cancel.addEventListener("click", async () => {
          if (!window.confirm("Vai tiešām atcelt adopcijas pieteikumu?"))
            return;
          cancel.disabled = true;
          try {
            await api("adoptions", {
              method: "DELETE",
              data: { adoption_id: Number(application.adoption_id) },
            });
            await loadApplications();
            showMessage("Adopcijas pieteikums atcelts.");
          } catch (error) {
            showMessage(error.message, true);
            cancel.disabled = false;
          }
        });
      }
    });
  }

  async function loadFavorites() {
    const container = byId("favoritesList");
    container.replaceChildren();
    const result = await api("favorites");
    if (!result.favorites.length) {
      addText(container, "p", "Tev vēl nav saglabātu favorītu.");
      return;
    }
    result.favorites.forEach((animal) => {
      const card = addText(container, "article", "", "application-card");
      const image = addText(card, "img", "");
      image.src = animal.photo || DEFAULT_PHOTO;
      image.alt = animal.name;
      image.addEventListener(
        "error",
        () => {
          image.src = DEFAULT_PHOTO;
        },
        { once: true },
      );
      addText(card, "h3", animal.name);
      addText(card, "p", `${animal.breed} · ${animal.adoption_status}`);
      openAnimalLink(animal.animal_id, card, "Apskatīt dzīvnieku");
      if (animal.adoption_status === "Pieejams") {
        const adopt = addText(card, "button", "Pieteikties adopcijai");
        adopt.type = "button";
        adopt.addEventListener("click", async () => {
          adopt.disabled = true;
          try {
            await fetch("back-end/adoptions.php", {
              method: "POST",
              credentials: "same-origin",
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
              },
              body: JSON.stringify({
                animal_id: Number(animal.animal_id),
                application_message: "",
              }),
            }).then(async (response) => {
              const application = await response.json();
              if (!response.ok) {
                throw new Error(
                  application.error ||
                    "Neizdevās iesniegt adopcijas pieteikumu.",
                );
              }
            });
            await Promise.all([loadFavorites(), loadApplications()]);
            showMessage("Adopcijas pieteikums iesniegts.");
          } catch (error) {
            showMessage(
              error.message || "Neizdevās iesniegt pieteikumu.",
              true,
            );
            adopt.disabled = false;
          }
        });
      }
      const remove = addText(card, "button", "Noņemt no favorītiem");
      remove.type = "button";
      remove.addEventListener("click", async () => {
        try {
          await api("favorite", {
            method: "POST",
            data: { animal_id: Number(animal.animal_id), favorite: false },
          });
          await loadFavorites();
        } catch (error) {
          showMessage(error.message, true);
        }
      });
    });
  }

  const donationStatuses = {
    not_processed:
      "Nav apstrādāts — demonstrācijas ieraksts, maksājums netika veikts",
    pending: "Gaida maksājuma apstiprinājumu",
    succeeded: "Maksājums apstiprināts",
    failed: "Maksājums nav pabeigts",
  };

  async function loadDonations() {
    const container = byId("donationsList");
    container.replaceChildren();
    const result = await api("donations");
    if (!result.donations.length) {
      addText(container, "p", "Nav ar tavu kontu saistītu ziedojumu.");
      return;
    }
    result.donations.forEach((donation) => {
      const card = addText(container, "article", "", "application-card");
      addText(card, "strong", `${Number(donation.amount).toFixed(2)} €`);
      addText(card, "p", `Datums: ${donation.created_at}`);
      addText(card, "p", `Maksājuma veids: ${donation.payment_method}`);
      addText(
        card,
        "p",
        donationStatuses[donation.status] || "Nezināms maksājuma statuss",
      );
    });
  }

  changePhoto.addEventListener("click", () => photoInput.click());
  photoInput.addEventListener("change", () => {
    const file = photoInput.files?.[0];
    if (!file) return;
    if (
      !["image/jpeg", "image/png", "image/webp"].includes(file.type) ||
      file.size > 2 * 1024 * 1024
    ) {
      showMessage("Izvēlieties JPG, PNG vai WEBP attēlu līdz 2 MB.", true);
      photoInput.value = "";
      return;
    }
    selectedPhoto = file;
    photoRemoved = false;
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = URL.createObjectURL(file);
    updatePhoto();
  });
  removePhoto.addEventListener("click", () => {
    selectedPhoto = null;
    currentPhoto = null;
    photoRemoved = true;
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = null;
    photoInput.value = "";
    updatePhoto();
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const submit = form.querySelector('button[type="submit"]');
    submit.disabled = true;
    try {
      const data = readForm();
      const photo = selectedPhoto ? await fileAsDataUrl(selectedPhoto) : null;
      await api("profile", {
        method: "POST",
        data: {
          ...data,
          birthDate: getBirthDate(data),
          photo,
          removePhoto: photoRemoved,
        },
      });
      currentPhoto = photoRemoved ? null : photo || currentPhoto;
      selectedPhoto = null;
      photoRemoved = false;
      originalData = readForm();
      updatePhoto();
      showMessage("Profila dati saglabāti.");
    } catch (error) {
      showMessage(error.message, true);
    } finally {
      submit.disabled = false;
    }
  });

  byId("cancelButton").addEventListener("click", () => {
    if (!originalData) return;
    fields.firstName.value = originalData.firstName;
    fields.lastName.value = originalData.lastName;
    fields.email.value = originalData.email;
    fields.description.value = originalData.description;
    fields.birthDay.value = originalData.birthDay;
    fields.birthMonth.value = originalData.birthMonth;
    fields.birthYear.value = originalData.birthYear;
    selectedPhoto = null;
    photoRemoved = false;
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = null;
    photoInput.value = "";
    updatePhoto();
  });

  byId("changePassword").addEventListener("click", async () => {
    const currentPassword = window.prompt("Ievadiet pašreizējo paroli:");
    if (currentPassword === null) return;
    const newPassword = window.prompt(
      "Ievadiet jaunu paroli (vismaz 15 rakstzīmes, lielie/mazie burti, cipars un simbols):",
    );
    if (newPassword === null) return;
    const confirmation = window.prompt("Atkārtojiet jauno paroli:");
    if (confirmation === null) return;
    if (newPassword !== confirmation) {
      showMessage("Jaunās paroles nesakrīt.", true);
      return;
    }
    try {
      await api("changePassword", {
        method: "POST",
        data: { currentPassword, newPassword },
      });
      showMessage("Parole nomainīta.");
    } catch (error) {
      showMessage(error.message, true);
    }
  });

  byId("deleteAccount").addEventListener("click", async () => {
    if (
      !window.confirm(
        "Dzēst kontu neatgriezeniski? Tavi pieteikumi un favorīti tiks dzēsti.",
      )
    )
      return;
    const currentPassword = window.prompt("Ievadiet paroli, lai dzēstu kontu:");
    if (currentPassword === null) return;
    try {
      await api("account", { method: "DELETE", data: { currentPassword } });
      window.location.href = "index.html";
    } catch (error) {
      showMessage(error.message, true);
    }
  });

  byId("logoutButton").addEventListener("click", async () => {
    try {
      const response = await fetch("back-end/logout.php", {
        method: "POST",
        credentials: "same-origin",
        headers: { Accept: "application/json" },
      });
      if (!response.ok) throw new Error("Neizdevās izrakstīties.");
      window.location.href = "register.html?mode=login";
    } catch (error) {
      showMessage(error.message || "Neizdevās izrakstīties.", true);
    }
  });

  Promise.all([
    loadProfile(),
    loadApplications().then(() => undefined),
    loadFavorites(),
    loadDonations(),
  ]).catch((error) => showMessage(error.message, true));
});
