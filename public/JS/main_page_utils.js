

// PARTIE FIXE DONC PAS BESOIN DE REFRESH LES SELECTIONS

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




//PARTIE GÉRANT LA VUE DONC SELECTION D'ELEMENTS changeant.


function refreshSelection(){

    const cardContainer = document.querySelector(".cards-container");
    const tableContainer = document.querySelector(".table-container");

    const vueParameter = document.getElementById("js-vue-params");

    return {cardContainer, tableContainer, vueParameter}
}

//Fonctionnement du bouton de changement de vue
const switchBtn = document.getElementById("switch-vue-btn");

switchBtn.addEventListener('click', switchVue);

function switchVue(e){
    e.preventDefault();

    const {cardContainer, tableContainer, vueParameter} = refreshSelection()

    if(cardContainer.classList.contains('grid')){
        cardContainer.classList.remove('grid');
        cardContainer.classList.add('hidden');

        tableContainer.classList.remove('hidden');
        tableContainer.classList.add('block');

        switchBtn.textContent = "Vue cartes"

        vueParameter.value = "table"

        console.log(vueParameter)
        return
    }

    if(tableContainer.classList.contains('block')){
        tableContainer.classList.remove('block');
        tableContainer.classList.add('hidden');

        cardContainer.classList.remove('hidden');
        cardContainer.classList.add('grid');

        switchBtn.textContent = "Vue tableau"

        vueParameter.value = "cards"
        console.log(vueParameter)

        return
    }
}


