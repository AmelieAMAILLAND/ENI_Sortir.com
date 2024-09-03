document.addEventListener("DOMContentLoaded", (e) => {
        // Affichage de la carte
    const datas = document.querySelector('.js-datas');
    const place = JSON.parse(datas.getAttribute('data-place'));

    let lat = place.latitude;
    let lon = place.longitude;

    console.log(lat, lon);

    let map = L.map("map",{
        zoom: 10,
        center:[lat,lon]
    });

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        minZoom: 1,
        maxZoom: 20,
        attribution: 'données © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    let marker = L.marker([lat,lon]);
    let popup = `<div>
                            <div>
                                <h2 class="text-lg text-center">${place.name}</h2>
                                <p>${place.street}</p>
                                <p>${place.zipCode} ${place.city}</p>
                                <p><a href="https://www.openstreetmap.org/directions?engine=osrm_car&route=;${lat},${lon}" target="_blank" rel="noopener noreferrer" class="bg-amber-400 rounded-md py-1 px-2 max-w-fit mx-auto hover:opacity-80 js-place-btn">S'y rendre</a></p>
                            </div>
                        </div>`;
    marker.bindPopup(popup).addTo(map);
    marker.on('popupopen', function() {
        document.getElementById("go").addEventListener('click', (e) => {
            e.preventDefault();


        });
    });

});



