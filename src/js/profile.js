document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("profileForm");
  if (!form) return;
  const fields = {
    firstName: document.getElementById("firstName"),
    lastName: document.getElementById("lastName"),
    birthDay: document.getElementById("birthDay"),
    birthMonth: document.getElementById("birthMonth"),
    birthYear: document.getElementById("birthYear"),
    email: document.getElementById("email"),
    description: document.getElementById("description"),
  };

  const profilePhoto = document.getElementById("profilePhoto");
  const navPhoto = document.getElementById("navPhoto");
  const photoInput = document.getElementById("photoInput");
  const changePhoto = document.getElementById("changePhoto");
  const removePhoto = document.getElementById("removePhoto");
  const cancelButton = document.getElementById("cancelButton");
  const API_URL = "back-end/profile.php";
  const defaultPhoto =
    "https://placehold.co/500x350/fbe8ee/c13e68?text=Pievieno+bildi";

  let originalData = null;
  let selectedPhotoFile = null;
  let photoRemoved = false;
  let currentPhoto = null;

  function showMessage(message, isError = false) {
    let messageElement = document.getElementById("profileMessage");
    if (!messageElement) {
      messageElement = document.createElement("p");
      messageElement.id = "profileMessage";
      form.prepend(messageElement);
    }
    messageElement.textContent = message;
    messageElement.style.color = isError ? "#c62828" : "#258044";
    messageElement.style.display = "block";
  }

  function readForm() {
    return {
      firstName: fields.firstName?.value.trim() || "",
      lastName: fields.lastName?.value.trim() || "",
      birthDay: fields.birthDay?.value || "",
      birthMonth: fields.birthMonth?.value || "",
      birthYear: fields.birthYear?.value || "",
      email: fields.email?.value.trim() || "",
      description: fields.description?.value || "",
    };
  }

  function fillForm(data = {}) {
    if (fields.firstName) {
      fields.firstName.value = data.firstName || "";
    }
    if (fields.lastName) {
      fields.lastName.value = data.lastName || "";
    }
    if (fields.email) {
      fields.email.value = data.email || "";
    }
    if (fields.description) {
      fields.description.value = data.description || "";
    }

    const birthDate = data.birthDate || data.birth_date || "";

    if (birthDate) {
      const parts = birthDate.split("-");
      if (parts.length === 3) {
        if (fields.birthYear) fields.birthYear.value = parts[0];
        if (fields.birthMonth) fields.birthMonth.value = parts[1];
        if (fields.birthDay) fields.birthDay.value = parts[2];
      }
    } else {
      if (fields.birthDay) fields.birthDay.value = "";
      if (fields.birthMonth) fields.birthMonth.value = "";
      if (fields.birthYear) fields.birthYear.value = "";
    }
    currentPhoto = data.photo || null;
    selectedPhotoFile = null;
    photoRemoved = false;
    updatePhoto();
  }

  function updatePhoto() {
    const hasPhoto = Boolean(selectedPhotoFile || currentPhoto);
    const photoUrl = selectedPhotoFile
      ? URL.createObjectURL(selectedPhotoFile)
      : currentPhoto || defaultPhoto;

    if (profilePhoto) profilePhoto.src = photoUrl;
    if (navPhoto) navPhoto.src = photoUrl;
    if (changePhoto) {
      changePhoto.textContent = hasPhoto ? "Mainīt bildi" : "Pievienot bildi";
    }
    if (removePhoto) {
      removePhoto.hidden = !hasPhoto;
    }
  }

  function getFormData() {
    const data = readForm();
    const formData = new FormData();

    formData.append("firstName", data.firstName);
    formData.append("lastName", data.lastName);
    formData.append("email", data.email);
    formData.append("description", data.description);

    let birthDate = "";

    if (data.birthYear && data.birthMonth && data.birthDay) {
      birthDate = `${data.birthYear}-${data.birthMonth.padStart(2, "0")}-${data.birthDay.padStart(2, "0")}`;
    }

    formData.append("birthDate", birthDate);
    formData.append("photoRemoved", photoRemoved ? "1" : "0");

    if (selectedPhotoFile) {
      formData.append("photo", selectedPhotoFile);
    }

    return formData;
  }

  async function loadProfile() {
    try {
      const response = await fetch(API_URL, {
        method: "GET",
        credentials: "include",
        headers: {
          Accept: "application/json",
        },
      });
      const result = await response.json();
      if (!response.ok) {
        throw new Error(result.error || "Neizdevās ielādēt profila datus.");
      }

      const user = result.user || result;

      fillForm(user);
      originalData = readForm();
    } catch (error) {
      showMessage(error.message || "Neizdevās savienoties ar serveri.", true);
    }
  }

  if (changePhoto && photoInput) {
    changePhoto.addEventListener("click", () => {
      photoInput.click();
    });
  }

  if (photoInput) {
    photoInput.addEventListener("change", () => {
      const file = photoInput.files[0];

      if (!file) return;
      if (!file.type.startsWith("image/")) {
        showMessage("Lūdzu, izvēlies attēla failu.", true);
        photoInput.value = "";
        return;
      }
      if (file.size > 2 * 1024 * 1024) {
        showMessage("Attēla izmērs nedrīkst pārsniegt 2 MB.", true);
        photoInput.value = "";
        return;
      }

      selectedPhotoFile = file;
      photoRemoved = false;
      currentPhoto = null;

      updatePhoto();
    });
  }

  if (removePhoto) {
    removePhoto.addEventListener("click", () => {
      selectedPhotoFile = null;
      currentPhoto = null;
      photoRemoved = true;

      if (photoInput) photoInput.value = "";

      updatePhoto();
    });
  }

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const submitButton = form.querySelector(
      'button[type="submit"], input[type="submit"]',
    );

    if (submitButton) submitButton.disabled = true;
    try {
      const response = await fetch(API_URL, {
        method: "POST",
        credentials: "include",
        headers: { Accept: "application/json" },
        body: getFormData(),
      });

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.error || "Neizdevās saglabāt profila datus.");
      }

      const user = result.user || {
        ...readForm(),
        photo: result.photo || null,
      };

      fillForm(user);
      originalData = readForm();
      showMessage("Profila dati veiksmīgi saglabāti!");
    } catch (error) {
      showMessage(error.message || "Radās kļūda, saglabājot profilu.", true);
    } finally {
      if (submitButton) submitButton.disabled = false;
    }
  });

  if (cancelButton) {
    cancelButton.addEventListener("click", () => {
      if (originalData) {
        fillForm({ ...originalData, photo: currentPhoto });
      }

      if (photoInput) photoInput.value = "";

      const messageElement = document.getElementById("profileMessage");

      if (messageElement) messageElement.style.display = "none";
    });
  }
  loadProfile();
});
