
//Gestion du bouton de réinitialisation
const resetBtn = document.getElementById('reset-filters-btn');

const selectInputs = [...document.querySelectorAll('.js-select-input')];
const nameInput = document.getElementById('nameInput');
const dateInputs = [...document.querySelectorAll('.js-date-input')];
const checkboxes = [...document.querySelectorAll('.js-checkboxes-input')];
const defaultRadioBtn = document.getElementById('registeredAll');

resetBtn.addEventListener('click', handleFormReset)

function handleFormReset(e){
    e.preventDefault();
    selectInputs.forEach(selectInput => selectInput.value = "all");
    nameInput.value = "";
    dateInputs.forEach(dateInput => dateInput.value = "");
    checkboxes.forEach(checkbox=>checkbox.checked = false);
    defaultRadioBtn.checked = true;

    if(plannerCheckbox.disabled) plannerCheckbox.disabled = false;
}


//Gestion de la coche 'Organisateur' obligatoire pour certains statuts (Sauf Admin)
const stateInput = document.getElementById('status');
const plannerCheckbox = document.getElementById('isPlanner');

const isAdmin = !!document.getElementById('isAdmin');


stateInput.addEventListener('change', checkForPlannerCheckbox)

function checkForPlannerCheckbox(e){
    console.log(e);
    //Si l'utilisateur qui clique est admin, on ne coche/disable pas la checkbox.
    if(isAdmin){
        return;
    }
    if(stateInput.value === "Annulée" || stateInput.value === "Créée" || stateInput.value === "Archivée"){
        plannerCheckbox.checked = true;
        plannerCheckbox.disabled = true;
    }else{
        plannerCheckbox.disabled = false;
    }
}

//Au chargement de la page, donc du script on vérifie aussi l'état.
checkForPlannerCheckbox();




//Fonctionnement du bouton de changement de vue
const cardContainer = document.querySelector(".card-container");
const tableContainer = document.querySelector(".table-container");

const vueParameter = document.getElementById("js-vue-params");

const switchBtn = document.getElementById("switch-vue-btn");

switchBtn.addEventListener('click', switchVue);

function switchVue(e){
    e.preventDefault();
    if(cardContainer.classList.contains('grid')){
        cardContainer.classList.remove('grid');
        cardContainer.classList.add('hidden');

        tableContainer.classList.remove('hidden');
        tableContainer.classList.add('block');

        switchBtn.textContent = "Vue cartes"

        vueParameter.value = "table"
        return
    }

    if(tableContainer.classList.contains('block')){
        tableContainer.classList.remove('block');
        tableContainer.classList.add('hidden');

        cardContainer.classList.remove('hidden');
        cardContainer.classList.add('grid');

        switchBtn.textContent = "Vue tableau"

        vueParameter.value = "cards"

        return
    }
}


//Empêcher la modification des inputs dates (dans tableau) sans disabled
const unchangeableDateInputs = [...document.querySelectorAll(".js-unchangeable-date")];

unchangeableDateInputs.forEach(input=>input.addEventListener("change", preventChange));

function preventChange(e){
    e.preventDefault();
    e.target.value = e.target.defaultValue;

}
