document.addEventListener("DOMContentLoaded", (e) => {
    // OUverture et fermeture de la modale
    let modal = null;

    const openModal = (e) =>{
        e.preventDefault();
        const target = document.querySelector('.modal-container');
        target.classList.remove('hidden');
        target.removeAttribute('aria-hidden');
        target.setAttribute('aria-modal', true);
        document.querySelectorAll('.js-close-modal').forEach(btn => {
            btn.addEventListener("click", closeModal);
        })
        setTimeout(()=>map.invalidateSize(),200);
        modal = target;
    }

    const closeModal = (e) =>{
        if (modal) {
            e.preventDefault();
            const target = document.querySelector('.modal-container');
            target.classList.add('hidden');
            target.setAttribute('aria-hidden', true);
            target.removeAttribute('aria-modal');
            setCurrentPlaceName();
            document.querySelectorAll('.js-close-modal').forEach(btn => {
                btn.removeEventListener("click", closeModal);
            })
            modal = null;
        }
    }

    document.querySelectorAll(".js-modal").forEach(btn => {
        btn.addEventListener("click", openModal);

    })

    // stockage et récupération des données du formulaire et de l'état de la modale lors de la création ou de la modification d'un lieu
    const destockDatas = () =>{
        if (sessionStorage.getItem('datas')){
            const datas=JSON.parse(sessionStorage.getItem("datas"));
            document.getElementById("event_name").value=datas.name;
            document.getElementById("event_dateTimeStart").value=datas.dateTimeStart;
            document.getElementById("event_duration").value=datas.duration;
            document.getElementById("event_registrationDeadline").value=datas.registrationDeadline;
            document.getElementById("event_maxNbRegistration").value=datas.maxNbRegistration;
            document.getElementById("event_infoEvent").value=datas.infoEvent;
           const modalStatus=datas.modalStatus;
            if (modalStatus){
                openModal(e);
            }
            sessionStorage.removeItem("datas");
        }
    }

    destockDatas();

    const stockDatas = () =>{
        const name = document.getElementById("event_name").value;
        const dateTimeStart = document.getElementById("event_dateTimeStart").value;
        const duration = document.getElementById("event_duration").value;
        const registrationDeadline = document.getElementById("event_registrationDeadline").value;
        const maxNbRegistration = document.getElementById("event_maxNbRegistration").value;
        const infoEvent = document.getElementById("event_infoEvent").value;
        const modalStatus = modal?true:false;
        const datas = JSON.stringify({'name':name, 'dateTimeStart':dateTimeStart, 'duration':duration, 'registrationDeadline':registrationDeadline,
        'maxNbRegistration':maxNbRegistration, 'infoEvent':infoEvent, 'modalStatus':modalStatus})
        sessionStorage.setItem('datas', datas);
    }

    const placeBtns = document.querySelectorAll(".js-place-btn");
    placeBtns.forEach((btn)=>{
        btn.addEventListener("click", (e)=>{
            e.preventDefault();
            stockDatas();
            window.location.href=e.target.href;
        });
    })

    // Affichage de la carte
    let lat = 47.22650646550442;
    let lon = -1.6206925419923897;

    const currentPlaceName = document.getElementById('place-name');
    const currentPlace = document.getElementById('event_place')

    let map = L.map("map",{
        zoom: 10,
        center:[lat,lon]
    });

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        minZoom: 1,
        maxZoom: 20,
        attribution: 'données © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);


    const datas = document.querySelector('.js-datas');
    const places = JSON.parse(datas.getAttribute('data-places'));
    const currentEvent = JSON.parse(datas.getAttribute('data-current-event'));

    places.forEach((place) => {
        lat = place.latitude;
        lon = place.longitude;
        if (currentPlaceName.value===place.name){
            map.setView([lat,lon]);
        }
        let marker = L.marker([lat,lon]);
        let popup = `<div>
                                <div>
                                    <h2 class="text-lg text-center">${place.name}</h2>
                                    <p>${place.street}</p>
                                    <p>${place.zipCode} ${place.city}</p>
                                    <p><a href="/place/${place.id}/edit" class="bg-amber-400 rounded-md py-1 px-2 max-w-fit mx-auto text-black hover:opacity-80 js-place-btn" title="Modifier les informations de ce lieu">Modifier</a> 
                                    <button id="chose-place-${place.id}" class="bg-amber-400 rounded-md py-1 px-2 max-w-fit mx-auto text-black hover:opacity-80" title="Choisir ce lieu pour votre sortie">Sélectionner</button></p>
                                </div>
                            </div>`;
        marker.bindPopup(popup).addTo(map);
        marker.on('popupopen', function() {
            document.getElementById(`chose-place-${place.id}`).addEventListener('click', (e) => {
                e.preventDefault();
                currentPlaceName.value = place.name;
                // currentEvent.place=place;
                currentPlace.value=place.id;
                console.log(currentEvent);
                closeModal(e);
            });
            document.querySelector('.js-place-btn').addEventListener('click', (e)=>{
                e.preventDefault();
                stockDatas();
                window.location.href=e.target.href;
            })
        });
    })

    const setCurrentPlaceName=() => {
        currentPlaceName.value =currentEvent.place.name;
    }
});



