const profileForm = document.getElementById("profileForm");
const description = document.getElementById("description");

const photoInput = document.getElementById("photoInput");
const profilePhoto = document.getElementById("profilePhoto");
const navPhoto = document.getElementById("navPhoto");

const changePhoto = document.getElementById("changePhoto");
const removePhoto = document.getElementById("removePhoto");
const cancelButton = document.getElementById("cancelButton");
const changePassword = document.getElementById("changePassword");

const logoutButton = document.getElementById("logoutButton");
const profileButton = document.getElementById("profileButton");

const defaultPhoto ="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=500&q=85";
let selectedPhoto = null;

function getProfileData() {
    return {
        firstName: document.getElementById("firstName").value,
        lastName: document.getElementById("lastName").value,
        birthDay: document.getElementById("birthDay").value,
        birthMonth: document.getElementById("birthMonth").value,
        birthYear: document.getElementById("birthYear").value,
        email: document.getElementById("email").value,
        description: description.value,
        photo: profilePhoto.src
    };
}

let originalData = getProfileData();

changePhoto.addEventListener("click", () => {
    photoInput.click();
});

photoInput.addEventListener("change", () => {
    const file = photoInput.files[0];
    if (!file) return;
    if (!file.type.startsWith("image/")) {
        alert("Lūdzu, izvēlies attēla failu.");
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        alert("Attēla izmērs nedrīkst pārsniegt 5 MB.");
        photoInput.value = "";
        return;
    }
    if (selectedPhoto) {
        URL.revokeObjectURL(selectedPhoto);
    }
    selectedPhoto = URL.createObjectURL(file);
    profilePhoto.src = selectedPhoto;
    navPhoto.src = selectedPhoto;
});

removePhoto.addEventListener("click", () => {
    profilePhoto.src = defaultPhoto;
    navPhoto.src = defaultPhoto;
    photoInput.value = "";
    if (selectedPhoto) {
        URL.revokeObjectURL(selectedPhoto);
        selectedPhoto = null;
    }
});

profileForm.addEventListener("submit", (event) => {
    event.preventDefault();

    const firstName = document.getElementById("firstName").value.trim();
    const lastName = document.getElementById("lastName").value.trim();
    const email = document.getElementById("email").value.trim();
    const day = Number(document.getElementById("birthDay").value);
    const month = Number(document.getElementById("birthMonth").value);
    const year = Number(document.getElementById("birthYear").value);

    if (!firstName || !lastName || !email) {
        alert("Lūdzu, aizpildi visus obligātos laukus.");
        return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert("Lūdzu, ievadi derīgu e-pasta adresi.");
        return;
    }

    const date = new Date(year, month - 1, day);
    const validDate = date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day;

    if (
        !validDate ||
        year < 1900 ||
        date > new Date()
    ) {
        alert("Lūdzu, ievadi derīgu dzimšanas datumu.");
        return;
    }

    const profile = {firstName, lastName, birthDay: day, birthMonth: month, birthYear: year, email, description: description.value, photo: profilePhoto.src};

    try {
        localStorage.setItem(
            "pedaMajasProfile",
            JSON.stringify(profile)
        );
        originalData = profile;
        alert("Profila izmaiņas saglabātas!");
    } catch {
        alert(
            "Neizdevās saglabāt datus pārlūkprogrammā. " +
            "Attēls var būt pārāk liels."
        );
    }
});

cancelButton.addEventListener("click", () => {
    const data = originalData;
    document.getElementById("firstName").value = data.firstName;
    document.getElementById("lastName").value = data.lastName;
    document.getElementById("birthDay").value = data.birthDay;
    document.getElementById("birthMonth").value = data.birthMonth;
    document.getElementById("birthYear").value = data.birthYear;
    document.getElementById("email").value = data.email;
    description.value = data.description;
    profilePhoto.src = data.photo || defaultPhoto;
    navPhoto.src = data.photo || defaultPhoto;
    photoInput.value = "";

    if (selectedPhoto) {
        URL.revokeObjectURL(selectedPhoto);
        selectedPhoto = null;
    }
});

function loadProfile() {
    try {
        const saved = localStorage.getItem("pedaMajasProfile");
        if (!saved) return;
        const data = JSON.parse(saved);
        document.getElementById("firstName").value =
            data.firstName ?? "Maksims";
        document.getElementById("lastName").value =
            data.lastName ?? "Ozols";
        document.getElementById("birthDay").value =
            data.birthDay ?? 13;
        document.getElementById("birthMonth").value =
            data.birthMonth ?? 8;
        document.getElementById("birthYear").value =
            data.birthYear ?? 2000;
        document.getElementById("email").value =
            data.email ?? "";
        description.value = data.description ?? "";

        if (data.photo) {
            profilePhoto.src = data.photo;
            navPhoto.src = data.photo;
        }

        originalData = getProfileData();

    } catch (error) {
        console.error("Neizdevās ielādēt profilu:", error);
    }
}

changePassword.addEventListener("click", () => {
    alert(
        "Paroles maiņas funkcijai nepieciešama servera " +
        "autentifikācija un paroles maiņas forma."
    );
});

profileButton.addEventListener("click", () => {
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
});

logoutButton.addEventListener("click", () => {
    const confirmed = confirm(
        "Vai tiešām vēlies atteikties no konta?"
    );
    if (confirmed) {
        window.location.href = "index.html";
    }
});

loadProfile();