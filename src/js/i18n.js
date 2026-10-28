(() => {
  "use strict";

  const translations = {
    "Dzīvnieku patversme": "Animal shelter",
    Sākums: "Home",
    Dzīvnieki: "Animals",
    "Par mums": "About us",
    "Kā palīdzēt?": "How to help",
    Kontakti: "Contact",
    "♡ Ziedot": "♡ Donate",
    Ziedot: "Donate",
    Pieteikties: "Sign in",
    Atteikties: "Log out",
    "PĒDAS MĀJĀS": "PĒDAS MĀJĀS",
    "Katrs dzīvnieks ir pelnījis mājas.": "Every animal deserves a home.",
    "🐾 PĒDAS MĀJĀS": "🐾 PĒDAS MĀJĀS",
    "© 2026 PĒDAS MĀJĀS | Veidots ar mīlestību ♡":
      "© 2026 PĒDAS MĀJĀS | Made with love ♡",
    "© 2026 PĒDAS MĀJĀS": "© 2026 PĒDAS MĀJĀS",
    "Par mums | Pēdas Mājās": "About us | PĒDAS MĀJĀS",
    "Administrators | Pēdas Mājās": "Admin | PĒDAS MĀJĀS",
    "Dzīvnieki | Pēdas Mājās": "Animals | PĒDAS MĀJĀS",
    "Ziedot | Pēdas Mājās": "Donate | PĒDAS MĀJĀS",
    "Kā palīdzēt? | Pēdas Mājās": "How to help | PĒDAS MĀJĀS",
    "Pēdas Mājās": "PĒDAS MĀJĀS",
    "Kontakti | Pēdas Mājās": "Contact | PĒDAS MĀJĀS",
    "Profils | Pēdas mājās": "Profile | PĒDAS MĀJĀS",
    "Reģistrācija | Pēdas Mājās": "Sign in | PĒDAS MĀJĀS",

    "PAR MUMS": "ABOUT US",
    "Mēs ticam otrajām iespējām": "We believe in second chances",
    "PĒDAS MĀJĀS ir vieta, kur dzīvnieki saņem rūpes, drošību un mīlestību, kamēr viņi gaida savas īstās mājas.":
      "PĒDAS MĀJĀS is a place where animals receive care, safety, and love while they wait for their forever homes.",
    "Katram dzīvniekam": "Every animal",
    "ir savs stāsts ♡": "has a story ♡",
    "Mūsu stāsts": "Our story",
    "PĒDAS MĀJĀS projekts tika izveidots ar vienu mērķi – palīdzēt dzīvniekiem, kuri palikuši bez mājām, atrast jaunas, mīlošas ģimenes.":
      "PĒDAS MĀJĀS was created with one goal: to help animals without homes find new, loving families.",
    "Mēs ticam, ka katrs dzīvnieks ir unikāls un pelnījis laimīgu dzīvi. Mūsu sistēma palīdzēs cilvēkiem iepazīties ar patversmes dzīvniekiem un pieteikties adopcijai.":
      "We believe every animal is unique and deserves a happy life. Our website helps people meet shelter animals and apply to adopt.",
    "Mūsu misija": "Our mission",
    "Palīdzēt dzīvniekiem atrast mīlošas, drošas un atbildīgas mājas.":
      "To help animals find loving, safe, and responsible homes.",
    "Mūsu vīzija": "Our vision",
    "Pasaule, kurā katram dzīvniekam ir iespēja dzīvot drošībā un mīlestībā.":
      "A world where every animal can live in safety and love.",
    "Dzīvnieki patversmē": "Animals at the shelter",
    "Adoptēti dzīvnieki": "Animals adopted",
    Brīvprātīgie: "Volunteers",
    "Daudz iespēju": "Many ways to help",
    "Palīdzēt arī Tev!": "You can help too!",
    "Mūsu vērtības": "Our values",
    Rūpes: "Care",
    "Katrs dzīvnieks ir pelnījis mīlestību, drošību un uzmanību.":
      "Every animal deserves love, safety, and attention.",
    Atbildība: "Responsibility",
    "Mēs veicinām atbildīgu attieksmi pret dzīvniekiem.":
      "We encourage responsible care for animals.",
    Uzticēšanās: "Trust",
    "Mēs vēlamies veidot uzticamu sadarbību ar adoptētājiem.":
      "We aim to build trusting relationships with adopters.",
    "Otrā iespēja": "A second chance",
    "Katram dzīvniekam ir tiesības uz jaunu un laimīgu sākumu.":
      "Every animal deserves a happy new beginning.",
    "Mūsu komanda 🐒": "Our team 🐒",
    "Iepazīsties ar mūsu komandu, kas veido PĒDAS MĀJĀS projektu!":
      "Meet the team behind PĒDAS MĀJĀS!",
    "UI/UX dizains": "UI/UX design",
    "🎀 Dizaina karaliene": "🎀 Design lead",
    Datubāzes: "Database",
    "🔠 Datu pavēlniece": "🔠 Data expert",
    "Front-end": "Front end",
    "💻 Koda burve": "💻 Code wizard",
    "PIEVIENOJIES MUMS": "JOIN US",
    "Palīdzi radīt labāku nākotni": "Help create a better future",
    "Tu vari palīdzēt, adoptējot, ziedojot, kļūstot par brīvprātīgo vai daloties ar mūsu stāstiem.":
      "You can help by adopting, donating, volunteering, or sharing our stories.",
    "Kļūt par brīvprātīgo": "Become a volunteer",

    "Administrācijas panelis": "Admin panel",
    "GALVENĀ IZVĒLNE": "MAIN MENU",
    Pārskats: "Overview",
    "Adopcijas pieteikumi": "Adoption applications",
    Lietotāji: "Users",
    Favorīti: "Favorites",
    Ziņojumi: "Messages",
    Ziedojumi: "Donations",
    "← Atgriezties vietnē": "← Back to website",
    Izlogoties: "Log out",
    "PĒDAS MĀJĀS / ADMIN": "PĒDAS MĀJĀS / ADMIN",
    Admin: "Admin",
    "Sveika, Admin! 👋": "Welcome, Admin! 👋",
    "Šeit vari pārvaldīt patversmes informāciju.":
      "Manage shelter information here.",
    "Pieejami adopcijai": "Available for adoption",
    "Jauni pieteikumi": "New applications",
    "Saņemtie ziedojumi": "Donations received",
    "Favorītu ieraksti": "Favorite entries",
    "Statistika par periodu": "Statistics for a period",
    "Reģistrācijas, adopcijas pieteikumi, pabeigtas adopcijas un saņemtie ziedojumi.":
      "Registrations, adoption applications, completed adoptions, and donations received.",
    "Iepriekš pabeigtajām adopcijām pabeigšanas datums netika saglabāts; tās šajā statistikā neparādīsies.":
      "Completion dates were not saved for older adoptions, so they are not included in this statistic.",
    No: "From",
    Līdz: "To",
    Parādīt: "Show",
    "Jauni lietotāji": "New users",
    "Pabeigtas adopcijas": "Completed adoptions",
    "Ātrās darbības": "Quick actions",
    "+ Pievienot dzīvnieku": "+ Add animal",
    "Skatīt pieteikumus": "View applications",
    "Skatīt ziņojumus": "View messages",
    "Pārvaldīt lietotājus": "Manage users",
    "Skatīt favorītus": "View favorites",
    "Pēdējie adopcijas pieteikumi": "Recent adoption applications",
    "Skatīt visus →": "View all →",
    "Dzīvnieku pārvaldība": "Animal management",
    "Pievieno, rediģē un pārvaldi dzīvniekus.":
      "Add, edit, and manage animals.",
    "Visi dzimumi": "All genders",
    Tēviņš: "Male",
    Mātīte: "Female",
    "Nav zināms": "Unknown",
    "Visi statusi": "All statuses",
    Pieejams: "Available",
    Rezervēts: "Reserved",
    Adoptēts: "Adopted",
    "Jaunākie vispirms": "Newest first",
    "Pēc vārda": "By name",
    "Senākie vispirms": "Oldest first",
    "Visi dzīvnieki": "All animals",
    Suņi: "Dogs",
    Kaķi: "Cats",
    Citi: "Other",
    Dzīvnieks: "Animal",
    Tips: "Type",
    Vecums: "Age",
    Statuss: "Status",
    Darbības: "Actions",
    "Izskati saņemtos pieteikumus.": "Review received applications.",
    Gaida: "Pending",
    Izskatīšanā: "Under review",
    Apstiprināta: "Approved",
    Noraidīta: "Rejected",
    Atcelta: "Cancelled",
    Pabeigta: "Completed",
    Pieteicējs: "Applicant",
    Datums: "Date",
    "Reģistrētie lietotāji": "Registered users",
    "Lietotāju informācijas pārskats.": "Registered user information.",
    ID: "ID",
    Vārds: "First name",
    "E-pasts": "Email",
    Loma: "Role",
    Reģistrējās: "Registered",
    "Lietotāju favorīti": "Users’ favorites",
    "Dzīvnieki, kurus lietotāji pievienojuši izlasei.":
      "Animals users have added to their favorites.",
    Lietotājs: "User",
    "Suga un šķirne": "Species and breed",
    "Pievienots izlasei": "Added to favorites",
    "Brīvprātīgo pieteikumi": "Volunteer applications",
    "Kas pieteicies, kādu darbību izvēlējies un ko ierakstījis anketā.":
      "Who applied, which activity they chose, and what they wrote.",
    "Visi pieteikumi": "All applications",
    Neapstrādāti: "Unprocessed",
    Apstrādāti: "Processed",
    "Kas pieteicās": "Applicant",
    "Izvēlētā aktivitāte": "Selected activity",
    "Ziņa anketā": "Application message",
    "Saņemtie ziņojumi": "Received messages",
    "Jautājumi un ziņojumi no apmeklētājiem.":
      "Questions and messages from visitors.",
    "Visi ziņojumi": "All messages",
    "Ziedojumu pārskats": "Donation overview",
    "Ziedojumu ieraksti un to iesniegšanas laiks.":
      "Donation records and submission dates.",
    "Nav apstrādāts": "Not processed",
    Apstiprināts: "Succeeded",
    "Nav pabeigts": "Failed",
    Ziedotājs: "Donor",
    Summa: "Amount",
    "Maksājuma veids": "Payment method",
    "Pievienot dzīvnieku": "Add animal",
    "Dzīvnieka tips": "Animal type",
    Suns: "Dog",
    Kaķis: "Cat",
    "Cits dzīvnieks": "Other animal",
    Šķirne: "Breed",
    Dzimums: "Gender",
    "Veselības stāvoklis": "Health",
    "Īpašas vajadzības": "Special needs",
    "Dzimšanas datums": "Date of birth",
    "Ierašanās datums": "Arrival date",
    Apraksts: "Description",
    "Īpašie nosacījumi dzīvniekam": "Special requirements for this animal",
    "Dzīvnieka fotogrāfija (JPG, PNG vai WEBP; līdz 2 MB)":
      "Animal photo (JPG, PNG, or WEBP; up to 2 MB)",
    "Noņemt fotogrāfiju": "Remove photo",
    Saglabāt: "Save",

    "Meklē savu draugu": "Find your friend",
    "Apskati mūsu patversmes dzīvniekus un atrodi sev piemērotāko draugu.":
      "Browse our shelter animals and find the right companion for you.",
    Filtri: "Filters",
    "Dzīvnieka veids": "Animal type",
    Visi: "All",
    "Citi dzīvnieki": "Other animals",
    "Līdz 1 gadam": "Under 1 year",
    "1-3 gadi": "1–3 years",
    "4-7 gadi": "4–7 years",
    "Virs 7 gadiem": "Over 7 years",
    Jā: "Yes",
    "Notīrīt filtrus": "Clear filters",
    "Kārtot pēc:": "Sort by:",
    Noklusējums: "Default",
    Jaunākie: "Newest",
    Vecākie: "Oldest",
    "Vārds A-Z": "Name A–Z",
    Kucēni: "Puppies",
    Kaķēni: "Kittens",
    Adoptēt: "Adopt",
    "Prasības saimniekam:": "Requirements for adopter:",
    "Nav norādītas": "Not specified",
    "Dzīvnieka vārds": "Animal name",
    "Dzīvnieka apraksts...": "Animal description...",
    "Vecums:": "Age:",
    "Dzimums:": "Gender:",
    "Šķirne:": "Breed:",
    "Veselības stāvoklis:": "Health:",
    "Statuss:": "Status:",
    "Patversmē no:": "At the shelter since:",
    "Kāpēc vēlies adoptēt?": "Why would you like to adopt?",
    "Diemžēl neviens dzīvnieks neatbilst izvēlētajiem filtriem.":
      "No animals match the selected filters.",

    "KATRS LABAIS DARBS IR SVARĪGS": "EVERY KIND ACT MATTERS",
    "Dāvā cerību": "Give hope",
    "mazajām ķepām ♡": "to little paws ♡",
    "Katrs ziedojums palīdz dzīvniekiem saņemt nepieciešamo aprūpi, barību un iespēju sagaidīt savu īsto ģimeni.":
      "Every donation helps provide animals with the care and food they need while they wait for their forever family.",
    "Ziedot tagad →": "Donate now →",
    "PALĪDZI DZĪVNIEKIEM": "HELP THE ANIMALS",
    "Veic ziedojumu ♡": "Make a donation ♡",
    "Izvēlies sev piemērotāko ziedojuma summu. Pat neliels atbalsts var daudz ko mainīt!":
      "Choose a donation amount that works for you. Even a small gift can make a difference!",
    "Izvēlies summu": "Choose an amount",
    "Cita summa (€)": "Other amount (€)",
    "Tava informācija": "Your information",
    Uzvārds: "Last name",
    "Izvēlies maksājuma veidu": "Choose a payment method",
    VISA: "VISA",
    Mastercard: "Mastercard",
    "Apple Pay": "Apple Pay",
    "Google Pay": "Google Pay",
    "Internet Bank": "Online banking",
    PayPal: "PayPal",
    "Piekrītu personas datu apstrādei ziedojuma noformēšanas nolūkos":
      "I agree to the processing of my personal data to arrange this donation.",
    "Tavs ziedojums": "Your donation",
    "Turpināt ar ziedojumu ♡": "Continue with donation ♡",
    "Maksājums veiksmīgi apstiprināts. Paldies par ziedojumu! ♡":
      "Payment successful. Thank you for your donation! ♡",
    "Kam palīdz Tavs ziedojums? 🐾": "What does your donation support? 🐾",
    Barība: "Food",
    "Pilnvērtīgs uzturs mūsu četrkājainajiem draugiem.":
      "Nutritious food for our four-legged friends.",
    "Veterinārā aprūpe": "Veterinary care",
    "Ārstēšana, vakcinācija un veselības pārbaudes.":
      "Treatment, vaccinations, and health checks.",
    "Drošas mājas": "A safe shelter",
    "Silta un droša vide, kamēr dzīvnieki gaida ģimeni.":
      "A warm, safe place while animals wait for a family.",
    "Rotaļlietas un aprūpe": "Toys and care",
    "Lietas, kas palīdz padarīt ikdienu priecīgāku.":
      "Things that make everyday life happier.",
    "KOPĀ MĒS VARAM VAIRĀK": "TOGETHER WE CAN DO MORE",
    "Palīdzēt var arī bez naudas ♡": "You can help without donating money ♡",
    "Kļūsti par brīvprātīgo, atnes nepieciešamās lietas vai palīdzi dzīvniekam atrast mājas!":
      "Volunteer, bring needed supplies, or help an animal find a home!",
    "Kā palīdzēt? →": "How to help →",

    "Mazs solis Tev.": "A small step for you.",
    "Liela cerība viņiem. ♡": "A big hope for them. ♡",
    "Lai palīdzētu dzīvniekiem, nav obligāti jāziedo nauda. Ir daudz veidu, kā dāvāt viņiem rūpes, mīlestību un otro iespēju.":
      "You don't have to donate money to help animals. There are many ways to give them care, love, and a second chance.",
    "Uzzini, kā palīdzēt →": "Learn how to help →",
    "TAVA PALĪDZĪBA IR SVARĪGA": "YOUR HELP MATTERS",
    "Kā Tu vari palīdzēt?": "How can you help?",
    "Izvēlies veidu, kas Tev ir vispiemērotākais.":
      "Choose the way that works best for you.",
    "Adoptē dzīvnieku": "Adopt an animal",
    "Dāvā mājas dzīvniekam, kurš gaida savu īsto ģimeni. Iepazīsties ar mūsu četrkājainajiem draugiem!":
      "Give a home to an animal waiting for a family. Meet our four-legged friends!",
    "Apskatīt dzīvniekus →": "Browse animals →",
    "Palīdzi rūpēties par dzīvniekiem, dodies pastaigās ar suņiem vai piedalies patversmes ikdienas darbos.":
      "Help care for animals, walk the dogs, or lend a hand with daily shelter tasks.",
    "Pieteikties →": "Apply →",
    "Ziedo nepieciešamās lietas": "Donate needed supplies",
    "Dzīvniekiem vienmēr noder barība, segas, rotaļlietas un citas ikdienā nepieciešamās lietas.":
      "Animals always need food, blankets, toys, and other everyday essentials.",
    "Apskatīt sarakstu →": "See the list →",
    "Palīdzi ar transportu": "Help with transport",
    "Dažreiz dzīvniekiem nepieciešama palīdzība nokļūt pie veterinārārsta vai jaunajiem saimniekiem.":
      "Sometimes animals need a ride to the vet or to their new family.",
    "Sazināties →": "Get in touch →",
    "Dalies ar mūsu stāstiem": "Share our stories",
    "Pastāsti draugiem par mūsu dzīvniekiem! Iespējams, tieši Tavs ieraksts palīdzēs atrast mājas.":
      "Tell your friends about our animals! Your post might help one of them find a home.",
    "Dalīties →": "Share →",
    "Atbalsti ar ziedojumu": "Support us with a donation",
    "Finansiāls atbalsts palīdz nodrošināt barību, ārstēšanu un nepieciešamo aprūpi dzīvniekiem.":
      "Financial support helps provide food, treatment, and essential care.",
    "Doties uz ziedojumiem →": "Go to donations →",
    "PRAKTISKA PALĪDZĪBA": "PRACTICAL HELP",
    "Kas mūsu draugiem ir nepieciešams?": "What do our friends need?",
    "Arī neliela palīdzība var padarīt dzīvnieku ikdienu daudz labāku.":
      "Even a little help can make an animal's day much better.",
    "🐕 Suņu un kaķu barība": "🐕 Dog and cat food",
    "🧸 Rotaļlietas dzīvniekiem": "🧸 Animal toys",
    "🛏️ Segas un guļvietas": "🛏️ Blankets and beds",
    "🧼 Higiēnas piederumi": "🧼 Hygiene supplies",
    "🦮 Pavadas un kaklasiksnas": "🦮 Leashes and collars",
    "🥣 Barības un ūdens trauki": "🥣 Food and water bowls",
    "Pirms lietu nogādāšanas sazinies ar mums, lai precizētu aktuālās vajadzības.":
      "Please contact us before bringing supplies so we can confirm current needs.",
    "Kļūsti par brīvprātīgo ♡": "Become a volunteer ♡",
    "Vai Tev patīk dzīvnieki un vēlies palīdzēt? Aizpildi pieteikumu!":
      "Love animals and want to help? Fill out an application!",
    "♡ Pavadi laiku ar dzīvniekiem": "♡ Spend time with animals",
    "♡ Iegūsti jaunu pieredzi": "♡ Gain new experience",
    "♡ Palīdzi mainīt dzīvnieku dzīves": "♡ Help change animals' lives",
    "Brīvprātīgā pieteikums": "Volunteer application",
    Tālrunis: "Phone",
    "Kā vēlies palīdzēt?": "How would you like to help?",
    "Izvēlies iespēju": "Choose an option",
    "Dzīvnieku aprūpe": "Animal care",
    "Suņu pastaigas": "Dog walking",
    Transportēšana: "Transport",
    "Pasākumu organizēšana": "Event planning",
    "Cita palīdzība": "Other help",
    "Papildu informācija": "Additional information",
    "Piekrītu personas datu apstrādei saziņas nolūkos":
      "I agree to the processing of my personal data so you can contact me.",
    "Nosūtīt pieteikumu →": "Submit application →",
    "Vēlies palīdzēt arī finansiāli?":
      "Would you also like to help financially?",
    "Apmeklē mūsu ziedojumu sadaļu un uzzini, kā atbalstīt dzīvniekus.":
      "Visit our donations page to learn how you can support the animals.",
    "Ziedot →": "Donate →",

    "🐾 KATRAI ĶEPIŅAI IR SAVS STĀSTS": "🐾 EVERY PAW HAS A STORY",
    "Mazas ķepas.": "Little paws.",
    "Liela mīlestība.": "Lots of love.",
    "Katrs dzīvnieks ir pelnījis mīlošas mājas. Iepazīsties ar mūsu četrkājainajiem draugiem un palīdzi viņiem atrast savu ģimeni!":
      "Every animal deserves a loving home. Meet our four-legged friends and help them find their family!",
    "Kā palīdzēt? ♡": "How to help ♡",
    "Kopā mēs varam mainīt viņu dzīves!": "Together we can change their lives!",
    "🐾 Meklēju mājas!": "🐾 Looking for a home!",
    "IEPAZĪSTIES AR MUMS": "MEET OUR ANIMALS",
    "Meklē savu jauno draugu ♡": "Find your new best friend ♡",
    "Varbūt tieši šeit Tevi gaida Tavs labākais draugs?":
      "Could your new best friend be waiting here?",
    "Visi dzīvnieki →": "All animals →",
    "Dzīvnieki tiek ielādēti no patversmes datubāzes...":
      "Loading animals from the shelter database...",
    "Apskatīt visus dzīvniekus →": "Browse all animals →",
    "Mēs ticam otrajām iespējām ♡": "We believe in second chances ♡",
    "PĒDAS MĀJĀS ir projekts, kura mērķis ir palīdzēt dzīvniekiem atrast mīlošas un drošas mājas.":
      "PĒDAS MĀJĀS is a project that helps animals find loving, safe homes.",
    "Mēs vēlamies padarīt adopcijas procesu vienkāršāku un palīdzēt cilvēkiem iepazīties ar dzīvniekiem, kuri gaida savu ģimeni.":
      "We want to make adoption easier and help people meet animals waiting for a family.",
    "Uzzini vairāk →": "Learn more →",
    "Katrs var palīdzēt 🐾": "Everyone can help 🐾",
    "Pat mazs labais darbs var mainīt kāda dzīvnieka dzīvi.":
      "Even a small act of kindness can change an animal's life.",
    Adoptē: "Adopt",
    "Dāvā dzīvniekam jaunas, mīlošas mājas.":
      "Give an animal a new, loving home.",
    "Apskatīt →": "Browse →",
    "Brīvprātīgais darbs": "Volunteer",
    "Palīdzi rūpēties par mūsu četrkājainajiem draugiem.":
      "Help care for our four-legged friends.",
    "Palīdzi ar lietām": "Donate supplies",
    "Ziedo barību, segas, rotaļlietas un citas lietas.":
      "Donate food, blankets, toys, and other supplies.",
    "Uzzināt vairāk →": "Learn more →",
    Ziedo: "Donate",
    "Atbalsti dzīvnieku aprūpi ar finansiālu ziedojumu.":
      "Support animal care with a financial donation.",
    "Viņi gaida tieši Tevi! ♡": "They're waiting for you! ♡",
    "Iepazīsties ar mūsu dzīvniekiem un palīdzi kādam atrast mājas.":
      "Meet our animals and help one of them find a home.",
    "Atrast savu draugu →": "Find your friend →",

    "SAZINIES AR MUMS": "GET IN TOUCH",
    "Mēs vienmēr esam": "We are always",
    "gatavi palīdzēt ♡": "ready to help ♡",
    "Tev ir jautājumi par adopciju, dzīvniekiem vai brīvprātīgo darbu? Raksti mums!":
      "Have questions about adoption, animals, or volunteering? Send us a message!",
    "Mūsu kontakti": "Our contacts",
    "Izvēlies sev ērtāko saziņas veidu.":
      "Choose the way to contact us that works best for you.",
    Adrese: "Address",
    "Rīga, Latvija": "Riga, Latvia",
    "+371 XX XXX XXX": "+371 XX XXX XXX",
    "info@pedasmajas.lv": "info@pedasmajas.lv",
    "Darba laiks": "Opening hours",
    "P.-Pk. 9:00-18:00": "Mon–Fri, 9:00–18:00",
    "UZRAKSTI MUMS": "WRITE TO US",
    "Nosūti ziņu ♡": "Send a message ♡",
    "Aizpildi formu, un mēs ar Tevi sazināsimies!":
      "Fill out the form and we'll get back to you!",
    "Ziņas tēma": "Message subject",
    "Izvēlies tēmu": "Choose a subject",
    "Dzīvnieku adopcija": "Animal adoption",
    "Cits jautājums": "Other question",
    "Tava ziņa": "Your message",
    "Piekrītu savu datu apstrādei saziņas nolūkos":
      "I agree to the processing of my data so you can contact me.",
    "Nosūtīt ziņu →": "Send message →",
    "Kur mūs atrast? 📍": "Where to find us 📍",
    "Mūsu patversme atrodas Latvijā. Pirms apmeklējuma iesakām sazināties ar mums.":
      "Our shelter is in Latvia. Please contact us before visiting.",
    "Šeit būs patversmes atrašanās vietas karte":
      "A map of the shelter will appear here",
    "Seko mums ♡": "Follow us ♡",
    "Seko mūsu jaunumiem un iepazīsti dzīvniekus, kuri meklē mājas!":
      "Follow our updates and meet animals looking for homes!",
    "📷 Instagram": "📷 Instagram",
    "📘 Facebook": "📘 Facebook",
    "🎵 TikTok": "🎵 TikTok",
    "Katrs labais darbs sākas ar vienu soli 🐾":
      "Every good deed starts with one step 🐾",
    "Palīdzi kādam atrast savas īstās mājas!":
      "Help an animal find its forever home!",

    "Pievienot bildi": "Add photo",
    "Noņemt bildi": "Remove photo",
    "Īss apraksts par sevi": "A short bio",
    "Dzimšanas datums (neobligāts)": "Date of birth (optional)",
    "Mainīt paroli": "Change password",
    Atcelt: "Cancel",
    "Dzēst kontu": "Delete account",
    "Pieteikuma statuss:": "Application status:",
    "Mani favorīti:": "My favorites:",
    "Ziedojumu vēsture:": "Donation history:",
    "Profila dati saglabāti!": "Profile saved!",
    "Izmaiņas tavā profilā ir saglabātas.":
      "Your profile changes have been saved.",
    Labi: "OK",

    "LAIPNI LŪGTI!": "WELCOME!",
    "Pievienojies mūsu": "Join our",
    "ķepu ģimenei ♡": "pawsome family ♡",
    "Izveido savu profilu, iepazīsti mūsu dzīvniekus un sper pirmo soli ceļā uz jaunu draudzību!":
      "Create a profile, meet our animals, and take the first step toward a new friendship!",
    "🐾 Katrs dzīvnieks ir pelnījis mīlestību!":
      "🐾 Every animal deserves love!",
    Reģistrēties: "Sign up",
    "JAUNS LIETOTĀJS": "NEW USER",
    "Izveido kontu ♡": "Create an account ♡",
    "Aizpildi informāciju, lai pievienotos mums.":
      "Fill in your details to join us.",
    Parole: "Password",
    "Vismaz 8 rakstzīmes, burti un cipari.":
      "At least 8 characters, with letters and numbers.",
    "Atkārto paroli": "Confirm password",
    "Piekrītu lietošanas noteikumiem un privātuma politikai":
      "I agree to the terms of use and privacy policy.",
    "Izveidot kontu →": "Create account →",
    "Tev jau ir konts?": "Already have an account?",
    "PRIEKS TEVI REDZĒT": "WELCOME BACK",
    "Laipni lūgts atpakaļ ♡": "Welcome back ♡",
    "Ievadi savu e-pastu un paroli.": "Enter your email and password.",
    "Vēl nav konta?": "Don't have an account yet?",

    "Meklēt pēc ID vai vārda...": "Search by ID or name...",
    "Šķirne...": "Breed...",
    "Veselības stāvoklis...": "Health...",
    "Vecums no": "Age from",
    "Vecums līdz": "Age to",
    "ID, lietotājs vai dzīvnieks...": "ID, user, or animal...",
    "Pieteikuma datums": "Application date",
    "Dzīvnieka ID vai vārds...": "Animal ID or name...",
    "ID, vārds, uzvārds vai e-pasts...": "ID, name, surname, or email...",
    "Meklēt pēc lietotāja vai dzīvnieka...": "Search by user or animal...",
    "Meklēt pēc vārda, e-pasta vai aktivitātes...":
      "Search by name, email, or activity...",
    "Sūtītājs vai tēma...": "Sender or subject...",
    "Ziņojuma datums": "Message date",
    "ID, ziedotājs vai e-pasts...": "ID, donor, or email...",
    "No datuma": "From date",
    "Līdz datumam": "To date",
    "Min. summa": "Min. amount",
    "Maks. summa": "Max. amount",
    Aizvērt: "Close",
    "Piemēram: vēlams aktīvs dzīvesveids un garas pastaigas.":
      "For example: an active lifestyle and long walks are preferred.",
    "Fotogrāfijas priekšskatījums": "Photo preview",
    "Visas šķirnes": "All breeds",
    "Visi stāvokļi": "All health statuses",
    "Meklēt pēc vārda, šķirnes vai apraksta...":
      "Search by name, breed, or description...",
    "Meklēt dzīvniekus": "Search animals",
    "Aizvērt logu": "Close dialog",
    "Dzīvnieka attēls": "Animal photo",
    "Pievienot favorītiem": "Add to favorites",
    "Ievadi savu summu": "Enter your amount",
    "Tavs vārds": "Your first name",
    "Tavs uzvārds": "Your last name",
    "Tavs e-pasts": "Your email",
    "+371 XXXXXXXX": "+371 XXXXXXXX",
    "Pastāsti nedaudz par sevi...": "Tell us a little about yourself...",
    "Raksti savu ziņu...": "Write your message...",
    "Lietotāja profils": "User profile",
    "Profila attēls": "Profile photo",
    "Lietotāja profila attēls": "User profile photo",
    "Dzimšanas diena": "Birth day",
    "Dzimšanas mēnesis": "Birth month",
    "Dzimšanas gads": "Birth year",
    "Izveido paroli": "Create a password",
    "Tava parole": "Your password",
    "Piesakieties administratora kontā.":
      "Please sign in to an administrator account.",
    "Pieprasījums neizdevās.": "The request failed.",
    "Sākuma datums nevar būt vēlāk par beigu datumu.":
      "The start date must not be after the end date.",
    "Rediģēt dzīvnieku": "Edit animal",
    "Izvēlieties JPG, PNG vai WEBP attēlu līdz 2 MB.":
      "Choose a JPG, PNG, or WEBP image up to 2 MB.",
    "Neizdevās nolasīt attēlu.": "Could not read the image.",
    "Pievienojot dzīvnieku, pievienojiet arī tā fotogrāfiju.":
      "Please add a photo when creating an animal record.",
    "Dzīvnieks atjaunināts.": "Animal updated.",
    "Dzīvnieks pievienots.": "Animal added.",
    "Neizdevās saglabāt dzīvnieku.": "Could not save the animal.",
    "Nav norādīts": "Not specified",
    Rediģēt: "Edit",
    "Publiskā karte": "Public listing",
    "Pieteikumu vēsture": "Application history",
    Dzēst: "Delete",
    "Dzīvnieks dzēsts.": "Animal deleted.",
    "Neizdevās dzēst dzīvnieku.": "Could not delete the animal.",
    "Dzīvnieki nav atrasti.": "No animals found.",
    Ziņa: "Message",
    Apstiprināt: "Approve",
    "Apstiprināt šo pieteikumu?": "Approve this application?",
    Noraidīt: "Reject",
    "Norādiet atteikuma iemeslu (neobligāti):":
      "Enter a reason for rejection (optional):",
    "Apstiprināt, ka dzīvnieks nodots jaunajam saimniekam?":
      "Confirm that the animal has been handed over to its new owner?",
    "Pieteikuma statuss atjaunināts.": "Application status updated.",
    "Neizdevās atjaunināt pieteikuma statusu.":
      "Could not update the application status.",
    "Favorīti nav atrasti.": "No favorites found.",
    "Ziņa nav pievienota.": "No message provided.",
    Apstrādāts: "Processed",
    "Atzīmēt kā apstrādātu": "Mark as processed",
    "Brīvprātīgā pieteikuma statuss atjaunināts.":
      "Volunteer application status updated.",
    "Brīvprātīgo pieteikumi nav atrasti.": "No volunteer applications found.",
    Neapstrādāts: "New",
    "Atzīmēt kā neapstrādātu": "Mark as unprocessed",
    "Gaida maksājuma apstiprinājumu": "Awaiting payment confirmation",
    "Nav apstrādāts (demonstrācija)": "Not processed (demo)",
    "Ziedojuma statuss atjaunināts.": "Donation status updated.",
    "Neizdevās atjaunināt ziedojuma statusu.":
      "Could not update the donation status.",
    "Neizdevās atjaunot ziedojumu sarakstu.":
      "Could not refresh the donations list.",
    "Neizdevās ielādēt administratora paneli.":
      "Could not load the admin panel.",
    "Neizdevās izrakstīties.": "Could not log out.",
    "Neizdevās atjaunināt favorītus.": "Could not update favorites.",
    "Nosūta pieteikumu...": "Submitting application...",
    "Neizdevās iesniegt pieteikumu.": "Could not submit the application.",
    "Ielādē dzīvniekus...": "Loading animals...",
    "Neizdevās ielādēt dzīvniekus.": "Could not load animals.",
    "Neizdevās ielādēt favorītus.": "Could not load favorites.",
    "Pašlaik nav pieejamu dzīvnieku adopcijai.":
      "There are currently no animals available for adoption.",
    "Lūdzu, izvēlies summu no 1 līdz 100 000 €.":
      "Please choose an amount between €1 and €100,000.",
    "Kļūda apstrādājot ziedojumu.":
      "There was an error processing the donation.",
    "Neizdevās izveidot savienojumu ar serveri.":
      "Could not connect to the server.",
    "Pieteikums veiksmīgi nosūtīts! Paldies. ♡":
      "Application submitted successfully! Thank you. ♡",
    "Kļūda nosūtot pieteikumu.":
      "There was an error submitting the application.",
    "Ziņa veiksmīgi nosūtīta! Paldies. ♡":
      "Message sent successfully! Thank you. ♡",
    "Kļūda nosūtot ziņu.": "There was an error sending the message.",
    "Palīdzi dzīvniekiem atrast mājas!": "Help animals find a home!",
    "Saite nokopēta!": "Link copied!",
    "Atver dzīvnieku lapu un nokopē tās adresi.":
      "Open an animal page and copy its address.",
    "Lūdzu, piesakieties savā kontā.": "Please sign in to your account.",
    "Mainīt bildi": "Change photo",
    "Norādiet pilnu dzimšanas datumu vai atstājiet visus datumus tukšus.":
      "Enter the complete date of birth or leave all date fields blank.",
    "Ievadiet derīgu dzimšanas datumu.": "Enter a valid date of birth.",
    "Atvērt dzīvnieku": "View animal",
    "Vai tiešām atcelt adopcijas pieteikumu?":
      "Are you sure you want to cancel the adoption application?",
    "Apskatīt dzīvnieku": "View animal",
    "Neizdevās iesniegt adopcijas pieteikumu.":
      "Could not submit the adoption application.",
    "Noņemt no favorītiem": "Remove from favorites",
    "Demonstrācijas ieraksts": "Demo record",
    "Maksājums apstiprināts": "Payment confirmed",
    "Maksājums nav pabeigts": "Payment incomplete",
    "Nezināms maksājuma statuss": "Unknown payment status",
    "Ievadiet pašreizējo paroli:": "Enter your current password:",
    "Ievadiet jaunu paroli (vismaz 8 rakstzīmes, burti un cipari):":
      "Enter a new password (at least 8 characters, letters and numbers):",
    "Atkārtojiet jauno paroli:": "Repeat the new password:",
    "Jaunās paroles nesakrīt.": "The new passwords do not match.",
    "Parole nomainīta.": "Password changed.",
    "Dzēst kontu neatgriezeniski? Tavi pieteikumi un favorīti tiks dzēsti.":
      "Permanently delete your account? Your applications and favorites will also be deleted.",
    "Ievadiet paroli, lai dzēstu kontu:":
      "Enter your password to delete the account:",
    "Neizdevās pārbaudīt lietotāja sesiju.":
      "Could not check the user session.",
    "Servera atbilde par lietotāja sesiju nav derīga.":
      "The server returned an invalid session response.",
    "Paroles nesakrīt!": "Passwords do not match!",
    "Parolei jābūt vismaz 8 rakstzīmes garai un jāsatur burti un cipari.":
      "Password must be at least 8 characters and contain letters and numbers.",
    "Kļūda reģistrējoties.": "There was an error signing up.",
    "Konts veiksmīgi izveidots! Tagad vari pieteikties. ♡":
      "Account created! You can now sign in. ♡",
    "Nepareizs e-pasts vai parole.": "Incorrect email or password.",
    "Pieteikšanās neizdevās.": "Could not sign in.",
    "Pieteikšanās veiksmīga!": "Signed in successfully!",
  };

  Object.assign(translations, {
    Darbība: "Activity",
    "Kļūsti par brīvprātīgo": "Become a volunteer",
    Administrators: "Administrator",
    "Pēdas Mājās logo": "PĒDAS MĀJĀS logo",
    "Divi suņi": "Two dogs",
    "Draudzīgs suns": "Friendly dog",
    Portrets: "Portrait",
    "Jautri pārsteigta mērkaķīte": "A comically surprised monkey",
    "Ziņkārīga mērkaķīte": "A curious monkey",
    "Žāvājoša mērkaķīte": "A yawning monkey",
    Logo: "Logo",
    "Laimīgi suņi": "Happy dogs",
    "Draudzīgi suņi": "Friendly dogs",
    "Atzīmēt kā jaunu": "Mark as new",
    "Pieteikuma ziņa:": "Application message:",
    "Atteikuma iemesls:": "Reason for rejection:",
    "Tev vēl nav adopcijas pieteikumu.":
      "You have no adoption applications yet.",
    "Tev vēl nav saglabātu favorītu.": "You have no saved favorites yet.",
    "Nav ar tavu kontu saistītu ziedojumu.":
      "There are no donations linked to your account.",
    "Mani brīvprātīgo pieteikumi:": "My volunteer applications:",
    "Tu vēl neesi aizpildījis brīvprātīgā pieteikumu.":
      "You have not submitted a volunteer application yet.",
    "Iesniegts: ": "Submitted: ",
    "Tava ziņa: ": "Your message: ",
    "Administrācija ir apstrādājusi pieteikumu.":
      "The administration has processed the application.",
    "Pieteikums saņemts un gaida izskatīšanu.":
      "Your application has been received and is awaiting review.",
    "Adopcijas pieteikums atcelts.": "Adoption application cancelled.",
    "Adopcijas pieteikums iesniegts.": "Adoption application submitted.",
    "Profila dati saglabāti.": "Profile saved.",
    "Pieteikties adopcijai": "Apply to adopt",
    "Maksājuma veids:": "Payment method:",
    "Atcelt pieteikumu": "Cancel application",
    "Dzīvnieks nav norādīts": "Animal not specified",
    "Kļūsti par brīvprātīgo →": "Become a volunteer →",
    Administrācija: "Administration",
    Rezervets: "Reserved",
    Adoptets: "Adopted",
    "Pārslēgt uz latviešu valodu": "Switch to Latvian",
    "Pārslēgt uz angļu valodu": "Switch to English",
    "Iesniegt adopcijas pieteikumu": "Submit adoption application",
    "Šis dzīvnieks nav pieejams": "This animal is not available",
    "Pieteikums iesniegts": "Application submitted",
    Kucēns: "Puppy",
    Kaķēns: "Kitten",
    Mēnesis: "Month",
    Pieteikums: "Application",
    "Pieejams adopcijai": "Available for adoption",
  });

  const storageKey = "patversme-language";
  let language = "lv";
  try {
    language = localStorage.getItem(storageKey) === "en" ? "en" : "lv";
  } catch (_) {
    // Keep Latvian as the default if storage is disabled.
  }

  const originalText = new WeakMap();
  const originalAttributes = new WeakMap();
  const translatableAttributes = ["placeholder", "aria-label", "alt", "title"];

  function normalized(value) {
    return value.trim().replace(/\s+/g, " ");
  }

  function translatedValue(key) {
    if (language !== "en") return key;
    if (translations[key]) return translations[key];
    let match = key.match(/^Atrasti: (\d+) dzīvnieki$/);
    if (match) return `Found: ${match[1]} animals`;
    match = key.match(/^Iesniegts: (.+)$/);
    if (match) return `Submitted: ${match[1]}`;
    match = key.match(/^Tava ziņa: (.+)$/);
    if (match) return `Your message: ${match[1]}`;
    match = key.match(/^Dzīvnieka statuss: (.+)$/);
    if (match) return `Animal status: ${translatedValue(match[1])}`;
    match = key.match(/^Pieteikuma ziņa: (.+)$/);
    if (match) return `Application message: ${match[1]}`;
    match = key.match(/^Atteikuma iemesls: (.+)$/);
    if (match) return `Reason for rejection: ${match[1]}`;
    match = key.match(/^#(\d+) · (.+) · (.+)$/);
    if (match)
      return `#${match[1]} · ${match[2]} · ${translatedValue(match[3])}`;
    match = key.match(/^#(\d+) • (.+) • (.+)$/);
    if (match)
      return `#${match[1]} • ${translatedValue(match[2])} • ${translatedValue(match[3])}`;
    match = key.match(/^(.+) • (.+) • (.+)$/);
    if (match)
      return `${translatedValue(match[1])} • ${translatedValue(match[2])} • ${translatedValue(match[3])}`;
    match = key.match(/^(.+) · (.+) · (.+) · (.+)$/);
    if (match)
      return `${match[1]} · ${translatedValue(match[2])} · ${match[3]} · ${translatedValue(match[4])}`;
    match = key.match(/^(.+) · (\d+) gadi · (.+)$/);
    if (match)
      return `${translatedValue(match[1])} · ${match[2]} years · ${translatedValue(match[3])}`;
    match = key.match(/^(.+) · (.+)$/);
    if (match)
      return `${translatedValue(match[1])} · ${translatedValue(match[2])}`;
    match = key.match(/^(\d+) (gads|gadi)$/);
    if (match)
      return `${match[1]} ${Number(match[1]) === 1 ? "year" : "years"}`;
    match = key.match(/^(\d+) mēneši$/);
    if (match) return `${match[1]} months`;
    match = key.match(/^(.+) (\d+) (gads|gadi)$/);
    if (match)
      return `${translatedValue(match[1])} ${match[2]} ${Number(match[2]) === 1 ? "year" : "years"}`;
    match = key.match(/^(.+) (\d+) mēneši$/);
    if (match) return `${translatedValue(match[1])} ${match[2]} months`;
    match = key.match(/^Pieteikums #(\d+) iesniegts\. Statuss: (.+)\.$/);
    if (match)
      return `Application #${match[1]} submitted. Status: ${translatedValue(match[2])}.`;
    return key;
  }

  function translateText(node) {
    if (!originalText.has(node)) originalText.set(node, node.nodeValue);
    const source = originalText.get(node);
    const key = normalized(source);
    const translated = translatedValue(key);
    if (!translated || (translated === key && language === "en")) return;
    const leading = source.match(/^\s*/)?.[0] || "";
    const trailing = source.match(/\s*$/)?.[0] || "";
    const output = `${leading}${translated}${trailing}`;
    if (node.nodeValue !== output) node.nodeValue = output;
  }

  function translateElementAttributes(element) {
    if (!originalAttributes.has(element)) originalAttributes.set(element, {});
    const originals = originalAttributes.get(element);
    for (const attribute of translatableAttributes) {
      if (!element.hasAttribute(attribute)) continue;
      if (!(attribute in originals))
        originals[attribute] = element.getAttribute(attribute);
      const source = originals[attribute];
      const key = normalized(source);
      const translated = translatedValue(key);
      if (element.getAttribute(attribute) !== translated) {
        element.setAttribute(attribute, translated);
      }
    }
  }

  function translateTree(root) {
    if (root.nodeType === Node.TEXT_NODE) {
      const parent = root.parentElement;
      if (
        parent &&
        !parent.closest("script, style, noscript, [data-i18n-ignore]")
      ) {
        translateText(root);
      }
      return;
    }
    if (root.nodeType === Node.ELEMENT_NODE) {
      if (root.closest("[data-i18n-ignore]")) return;
      translateElementAttributes(root);
      root.querySelectorAll("*").forEach(translateElementAttributes);
      const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
      let node;
      while ((node = walker.nextNode())) {
        const parent = node.parentElement;
        if (
          parent &&
          !parent.closest("script, style, noscript, [data-i18n-ignore]")
        ) {
          translateText(node);
        }
      }
    }
  }

  function setLanguage(nextLanguage) {
    language = nextLanguage === "en" ? "en" : "lv";
    document.documentElement.lang = language;
    try {
      localStorage.setItem(storageKey, language);
    } catch (_) {
      // Language still changes for the current page if storage is disabled.
    }

    translateTree(document.documentElement);
    const button = document.getElementById("languageSwitch");
    if (button) {
      button.textContent = language === "lv" ? "EN" : "LV";
      button.setAttribute(
        "aria-label",
        language === "lv" ? "Switch to English" : "Pārslēgt uz latviešu valodu",
      );
      button.title =
        language === "lv" ? "Switch to English" : "Pārslēgt uz latviešu valodu";
    }
  }

  function addLanguageButton() {
    const host =
      document.querySelector(".header-buttons") ||
      document.querySelector(".admin-profile");
    if (!host || document.getElementById("languageSwitch")) return;
    const button = document.createElement("button");
    button.type = "button";
    button.id = "languageSwitch";
    button.className = "language-switch";
    button.dataset.i18nIgnore = "true";
    button.addEventListener("click", () =>
      setLanguage(language === "lv" ? "en" : "lv"),
    );
    host.appendChild(button);
  }

  function addStyles() {
    const style = document.createElement("style");
    style.textContent = `
      .language-switch { border: 1px solid #c43e73; border-radius: 999px; padding: 8px 13px; background: #fff; color: #a72f60; font: inherit; font-size: 13px; font-weight: 800; cursor: pointer; }
      .language-switch:hover { background: #fff0f5; }
      .language-switch:focus-visible { outline: 3px solid #d9a1b1; outline-offset: 2px; }
      .admin-profile .language-switch { margin-left: 14px; }
      @media (max-width: 700px) { .header-buttons .language-switch { padding: 7px 10px; } }
    `;
    document.head.appendChild(style);
  }

  function validationMessage(field) {
    const english = language === "en";
    const validity = field.validity;
    if (validity.valueMissing) {
      if (field.type === "checkbox")
        return english
          ? "Please check this box."
          : "Atzīmējiet šo izvēles rūtiņu.";
      if (field instanceof HTMLSelectElement)
        return english
          ? "Please choose an option."
          : "Lūdzu, izvēlieties opciju.";
      return english
        ? "Please fill out this field."
        : "Lūdzu, aizpildiet šo lauku.";
    }
    if (validity.typeMismatch && field.type === "email")
      return english
        ? "Enter a valid email address."
        : "Ievadiet derīgu e-pasta adresi.";
    if (validity.tooShort) {
      const minLength = field.minLength;
      return english
        ? `Enter at least ${minLength} characters.`
        : `Ievadiet vismaz ${minLength} rakstzīmes.`;
    }
    if (validity.rangeUnderflow)
      return english
        ? `Enter a value of at least ${field.min}.`
        : `Ievadiet vērtību, kas nav mazāka par ${field.min}.`;
    if (validity.rangeOverflow)
      return english
        ? `Enter a value no greater than ${field.max}.`
        : `Ievadiet vērtību, kas nepārsniedz ${field.max}.`;
    if (validity.stepMismatch || validity.badInput)
      return english ? "Enter a valid value." : "Ievadiet derīgu vērtību.";
    return "";
  }

  function setupFormValidation() {
    document.addEventListener(
      "invalid",
      (event) => {
        const field = event.target;
        if (!(field instanceof HTMLElement) || !("setCustomValidity" in field))
          return;
        field.setCustomValidity(validationMessage(field));
      },
      true,
    );
    ["input", "change"].forEach((eventName) => {
      document.addEventListener(eventName, (event) => {
        const field = event.target;
        if (field instanceof HTMLElement && "setCustomValidity" in field)
          field.setCustomValidity("");
      });
    });
  }

  function start() {
    addStyles();
    addLanguageButton();
    setupFormValidation();
    setLanguage(language);

    const observer = new MutationObserver((records) => {
      for (const record of records) {
        if (record.type === "characterData") translateTree(record.target);
        for (const node of record.addedNodes) translateTree(node);
        if (record.type === "attributes")
          translateElementAttributes(record.target);
      }
    });
    observer.observe(document.documentElement, {
      subtree: true,
      childList: true,
      characterData: true,
      attributes: true,
      attributeFilter: translatableAttributes,
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", start, { once: true });
  } else {
    start();
  }
})();
