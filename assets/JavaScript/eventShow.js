document.addEventListener("DOMContentLoaded", (e) => {
        // Affichage de la carte
    const datas = document.querySelector('.js-datas');
    const place = JSON.parse(datas.getAttribute('data-place'));

    let lat = place.latitude;
    let lon = place.longitude;

    let map = L.map("map",{
        zoom: 10,
        center:[lat,lon]
    });

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        minZoom: 1,
        maxZoom: 20,
        attribution: 'données © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    // récupération de la position de l'utilisateur
    let originOSM = "";
    let originGoogle = "";
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition((position) => {
            const userLat = position.coords.latitude;
            const userLon = position.coords.longitude;

            originOSM = `${userLat},${userLon}`;
            originGoogle = `&origin=${userLat},${userLon}`;
        }, (error) => {
            console.error("Erreur de géolocalisation : ", error);
        });
    } else {
        console.error("La géolocalisation n'est pas disponible sur ce navigateur.");
    }

    // affichage du marker et création de la popup
    let marker = L.marker([lat,lon]);
    let popup = `<div>
                            <div>
                                <h2 class="text-lg text-center">${place.name}</h2>
                                <p class="my-0">${place.street}</p>
                                <p class="my-0">${place.zipCode} ${place.city}</p>
                                <p class="mb-0 font-semibold">Pour s'y rendre :</p>
                                <p><a href="https://www.openstreetmap.org/directions?engine=osrm_car&route=${originOSM};${lat},${lon}" target="_blank" rel="noopener noreferrer" class="bg-amber-400 rounded-md py-1 px-2 max-w-fit mx-auto hover:opacity-80 js-go-osm">OpenStreetMap</a>
                                <a href="https://www.google.com/maps/dir/?api=1${originGoogle}&destination=${lat},${lon}" target="_blank" rel="noopener noreferrer" class="bg-amber-400 rounded-md py-1 px-2 max-w-fit mx-auto hover:opacity-80 js-go-google">Google</a></p>
                            </div>
                        </div>`;
    marker.bindPopup(popup).addTo(map);
    marker.on('popupopen', function() {
        document.querySelector('.js-go-osm').href=`https://www.openstreetmap.org/directions?engine=osrm_car&route=${originOSM};${lat},${lon}`;
        document.querySelector('.js-go-google').href=`https://www.google.com/maps/dir/?api=1${originGoogle}&destination=${lat},${lon}`;
    });
});



