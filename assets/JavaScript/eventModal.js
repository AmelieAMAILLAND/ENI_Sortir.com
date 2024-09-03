document.addEventListener("DOMContentLoaded", (e) => {
    // OUverture et fermeture de la modale
    let modal =null;
    const toggleModal = ()=>{
        modalContainer.classList.toggle("hidden");
        modalContainer.classList.toggle("block");
    }

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
            document.querySelectorAll('.js-close-modal').forEach(btn => {
                btn.removeEventListener("click", closeModal);
            })
            modal = null;
        }
    }

    document.querySelectorAll(".js-modal").forEach(btn => {
        btn.addEventListener("click", openModal);

    })


    // Affichage de la carte
    let lat = 47.226586171358186;
    let lon = -1.6207834394808776;
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
        const choseBtn = document.createElement('button')
        let popup = `<div>
                                <div>
                                    <h2 class="text-lg text-center">${place.name}</h2>
                                    <p>${place.street}</p>
                                    <p>${place.zipCode} ${place.city}</p>
                                    <p><a href="/place/${place.id}/edit" class="bg-amber-400 rounded-md py-1 px-2 max-w-fit mx-auto hover:opacity-80 js-place-btn">Modifier</a> <button id="chose-place-${place.id}" class="bg-amber-400 rounded-md py-1 px-2 max-w-fit mx-auto hover:opacity-80">Sélectionner</button></p>
                                </div>
                            </div>`;
        marker.bindPopup(popup).addTo(map);
        marker.on('popupopen', function() {
            document.getElementById(`chose-place-${place.id}`).addEventListener('click', (e) => {
                e.preventDefault();
                currentPlaceName.value = place.name;
                currentEvent.place=place;
                currentPlace.value=place.id;
                console.log(currentEvent);
                closeModal(e);
            });
        });
    })



});




