
function collectInputsValues(){
    const statusAndSite = [...document.querySelectorAll('.js-select-input')].map(input=>input.value);
    const nameParam = document.getElementById('nameInput').value;
    const beginAndEndDate = [...document.querySelectorAll('.js-date-input')].map(input=>input.value);
    const planner = document.querySelector('.js-checkboxes-input').checked;

    const registeredBtns = [...document.querySelectorAll("input[name='registered']")];

    const checkedRegistered = registeredBtns.filter(input=>!!input.checked)[0].value;


    const vueParam = document.getElementById("js-vue-params").value;

   // console.log([statusAndSite, nameParam, beginAndEndDate, planner, checkedRegistered, vueParam]);

    return [statusAndSite, nameParam, beginAndEndDate, planner, checkedRegistered, vueParam];
}



const userId = document.getElementById('js-user-id').textContent;
const userPseudo = document.getElementById('js-user-pseudo').textContent;
const isUserAnAdmin = !!document.getElementById('isAdmin');


const parametersValues = collectInputsValues();

function buildRequestUrlFromParams(parametersValues){


    let baseUrl = `/api/events`;

    let paramsString = `?status=${parametersValues[0][0]}&siteName=${parametersValues[0][1]}&nameInput=${parametersValues[1]}&beginDate=${parametersValues[2][0]}&endDate=${parametersValues[2][1]}${ parametersValues[3] ? '&isPlanner=on' : ''}&registered=${parametersValues[4]}&vue=${parametersValues[5]}`;

    console.log(baseUrl+paramsString);
    return baseUrl+paramsString;

}


async function fetchEventAPI(url){

    try{
        const response = await fetch(url,{
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        let data = await response.json()

        //console.log("Avant filtre: ", data);

        //On remet le dernier filtre sur l'affichage des éléments 'archived' si on n'est pas admin OU pas l'organisateur.

        if(!isUserAnAdmin){
            data = data.filter(event => (event.state !== 'archived') || (event.state==='archived' && event.planner.pseudo === userPseudo))
        }

       // console.log("Après filtre: ", data);

        return data;

    }catch(err){
        console.log(err);
        return "Gros nul";
    }


}

function fillTableTemplate(eventData) {

    const vueParam = document.getElementById("js-vue-params").value;

    let rawBaseHtml = `
    <div class="table-container max-w-[1200px] mx-auto ${vueParam === 'cards' ? "hidden" : 'block'} mb-10">
        <table class="table w-full">
            <thead>
            <tr class="border border-slate-900 bg-slate-900">
                <th class="th-style">Nom</th>
                <th class="th-style">Date de début</th>
                <th class="th-style">Durée</th>
                <th class="th-style">Clôture</th>
                <th class="th-style">Inscrits/Places</th>
                <th class="th-style">Inscrit</th>
                <th class="th-style">État</th>
                <th class="th-style">Actions</th>
            </tr>
            </thead>
            <tbody>
    `;

    if(eventData.length === 0){
        rawBaseHtml += `<tr><td>Aucun évènements trouvé ! Essayez différents filtres ...</td></tr>`;
    }else{
        eventData.forEach(event=>rawBaseHtml = rawBaseHtml.concat(buildEventTableHtml(event)));
    }

    rawBaseHtml += `</tbody></table></div>`;
    //console.log(rawBaseHtml);

    return rawBaseHtml;
}

//

// {% endfor %}

function buildEventTableHtml(event){

    let eventHtml =
        `<tr class="hover:opacity-80
                ${event.state === 'published' ? 'bg-green-200' :
                event.state === 'past' ? 'bg-gray-100' :
                event.state === 'in_progress' ? 'bg-amber-50' :
                event.state === 'archived' ? 'bg-gray-200 opacity-70' :
                event.state === 'closed' ? 'bg-red-200' :
                event.state === 'full' ? 'bg-stone-300' :
                event.state === 'created' ? 'bg-blue-200' :
                event.state === 'canceled' ? 'bg-gray-500 opacity-70' : ''} ">

                <td class="td-style">${event.name}</td>
                <td class="td-style">
                    <input type="datetime-local" value="${event.dateTimeStart.split('+')[0]}"
                           class="js-unchangeable-date px-0 border-0 bg-inherit">
                </td>
            </td>
            <td class="td-style">${event.duration.split('T')[1].split('+')[0].substring(0,5)}</td>
            <td class="td-style">
                <input type="datetime-local" value="${event.registrationDeadline.split('+')[0]}"
                       class="js-unchangeable-date px-0 border-0 bg-inherit ">
            </td>
            <td class="td-style">${event.registered.length}/${event.maxNbRegistration}</td>
            <td class="td-style">
            
            ${userPseudo === event.planner.pseudo ?
            '<p class="text-blue-500 font-semibold">Organisateur</p>' :
            event.registered.find(user => user.id == userId) ? '<p class="text-green-500 font-semibold">Inscrit</p>' :
               '<p class="text-red-500 font-semibold">Non inscrit</p>'}
            </td>
            
            <td class="td-style">${mapStatusToFrench(event.state)}</td>
            <td class="td-style flex justify-center items-center gap-1">
                <a href="/event/${event.id}"
                   class="py-1 px-2 max-w-fit bg-blue-800 text-white rounded-md shadow-md hover:opacity-80">VOIR
                </a>
              
            ${(userPseudo === event.planner.pseudo && event.state !== 'archived') ? 
            `<a href='/event/${event.id}/edit' class='py-1 px-2 bg-amber-600 text-white rounded-md shadow-md hover:opacity-80'>MODIFIER</a>` : ''}
                
            </td>
            </tr>`;


    //console.log(`Evènement : ${event.name}`,event.registered.find(user => user.id === userId), userId)
    return eventHtml;
}


function fillCardsTemplate(eventData){

    const vueParam = document.getElementById("js-vue-params").value;


    let rawBaseHtml = `<div class="grid-cols-auto-fill-300 gap-4 max-w-[1200px] cards-container mx-auto ${vueParam === 'cards' ? "grid" : "hidden"}">`;

    if(eventData.length === 0){
        rawBaseHtml += `<p>Aucun évènements trouvé ! Essayez différents filtres ...</p>`;
    }else{
        eventData.forEach(event=>rawBaseHtml = rawBaseHtml.concat(buildEventCardHtml(event)));
    }

    rawBaseHtml += '</div>';
    //console.log(rawBaseHtml);

    return rawBaseHtml;
}

function buildEventCardHtml(event){

    let eventHtml =
    `<div class="event-card
        max-h-[300px] flex flex-col mb-4 pt-2 pb-4 px-3 justify-between rounded-md shadow-md hover:scale-105 transition-transform
        ${event.state === 'published' ? 'bg-green-200' :
        event.state === 'past' ? 'bg-gray-100' :
            event.state === 'in_progress' ? 'bg-amber-50' :
                event.state === 'archived' ? 'bg-gray-200 opacity-70' :
                    event.state === 'closed' ? 'bg-red-200' :
                        event.state === 'full' ? 'bg-stone-300' :
                            event.state === 'created' ? 'bg-blue-200' : 
                                event.state === 'canceled' ? 'bg-gray-500 opacity-70' : ''}  ">
        <div class="name-and-date-container flex justify-between items-center">
            <p class="font-semibold text-xl hover:opacity-80">
                <a href="/event/${event.id}">${event.name}</a>
            </p>
            <input type="datetime-local" value="${event.dateTimeStart.split('+')[0]}" class="js-unchangeable-date px-0 border-0 bg-inherit text-sm">
        </div>
        <div class="inscr-container flex justify-between items-center">
            <div class="limit-container ">
                <p class="font-semibold">Date limite : </p>
                <input type="datetime-local" value="${event.registrationDeadline.split('+')[0]}" class="js-unchangeable-date p-0 border-0 bg-inherit text-sm">
            </div>

            ${userPseudo === event.planner.pseudo ?
            '<p class="text-blue-500 font-semibold">Organisateur</p>' :
            event.registered.find(user => user.id == userId) ? '<p class="text-green-500 font-semibold">Inscrit</p>' :
            '<p class="text-red-500 font-semibold">Non inscrit</p>'}

        </div>
        <div class="place-container">
            <p>${event.registered.length}/${event.maxNbRegistration} places</p>
        </div>
        <div class="desc-container">
            <p>${event.infoEvent}</p>
        </div>
        <div class="org-container flex justify-between mb-2">

            <p>Organisateur : <a href="/profil/${event.planner.id}" class="font-semibold hover:opacity-80 ">${event.planner.pseudo}</a></p>
            <p class="font-semibold">${mapStatusToFrench(event.state)}</p>

        </div>
        <div class="actions-container mx-auto max-w-[30%] flex gap-3 ">
            <a href="/event/${event.id}"
                   class="py-1 px-2 max-w-fit bg-blue-800 text-white rounded-md shadow-md hover:opacity-80">VOIR
                </a>
              
            ${(userPseudo === event.planner.pseudo && event.state !== 'archived') ?
        `<a href='/event/${event.id}/edit' class='py-1 px-2 bg-amber-600 text-white rounded-md shadow-md hover:opacity-80'>MODIFIER</a>` : ''}
           
        </div>

    </div>`;

    return eventHtml;
}

function mapStatusToFrench(status){
    let frenchStatus = '';
    switch (status) {
        case 'canceled':
            frenchStatus =  'Annulée';
            break;
        case 'created':
            frenchStatus =  'En création';
            break;
        case 'in_progress':
            frenchStatus =  'En cours';
            break;
        case 'past':
            frenchStatus =  'Passée';
            break;
        case 'published':
            frenchStatus =  'Ouverte';
            break;
        case 'full':
            frenchStatus =  'Complète';
            break;
        case "archived":
            frenchStatus =  'Archivée';
            break;
        case 'closed':
            frenchStatus =  'Fermée';
            break;
    }
    return frenchStatus;
}


document.getElementById('submitBtn').addEventListener('click', e=>fetchAndDisplay())

async function fetchAndDisplay(){

    //e.preventDefault();

    let url = buildRequestUrlFromParams(collectInputsValues());

    const events = await fetchEventAPI(url);

    //console.log(events);

    const tableContainer = document.querySelector('.js-table-container');
    const cardsContainer = document.querySelector('.js-cards-container');

    tableContainer.innerHTML = fillTableTemplate(events);
    cardsContainer.innerHTML = fillCardsTemplate(events);


    //On reselectionne les nouveaux inputs dates non changeant.
    //Et on leur ajoute un écouteur d'évènement.
    const unchangeableDates = [...document.querySelectorAll(".js-unchangeable-date")];
    unchangeableDates.forEach(input=>input.addEventListener("change", preventChange));

}

//On appel l'API au chargement de la page.
fetchAndDisplay();


//Empèche la modification des inputs dates
function preventChange(e){
    e.preventDefault();
    e.target.value = e.target.defaultValue;
}

//PARTIE TEST :
//Rappel API à chaque changement dans les filtres.

const resetButton = document.getElementById('reset-filters-btn');

const statusAndSiteInputs = [...document.querySelectorAll('.js-select-input')];
const nameFilterInput = document.getElementById('nameInput');
const dateFilterInputs = [...document.querySelectorAll('.js-date-input')];
const planner = document.getElementById('isPlanner');
const registeredButtons = [...document.querySelectorAll("input[name='registered']")];


const filtersListeningChange = [...statusAndSiteInputs, nameFilterInput,...dateFilterInputs, planner, ...registeredButtons];

//console.log(filtersListeningChange)
filtersListeningChange.forEach(input=>input.addEventListener('change', e=>fetchAndDisplay()));
resetButton.addEventListener('click', e=>fetchAndDisplay())





