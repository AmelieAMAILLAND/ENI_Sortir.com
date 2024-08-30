document.addEventListener("DOMContentLoaded", (event) => {
    // changement du lien d'édition du lieu en fonction du lieu sélectionné dans le select
    const editLink = document.getElementById('place-edit');
    document.getElementById('event_place').addEventListener('change', function(){
        const value = this.value;
        editLink.href=`/place/${value}/edit`;
    })

    // stockage et récupération des données du formulaire lors de la création ou de la modification d'un lieu

    const destockDatas = () =>{
        if (sessionStorage.getItem("name")){
            document.getElementById("event_name").value=sessionStorage.getItem("name");
            document.getElementById("event_dateTimeStart").value=sessionStorage.getItem("dateTimeStart");
            document.getElementById("event_duration").value=sessionStorage.getItem("duration");
            document.getElementById("event_registrationDeadline").value=sessionStorage.getItem("registrationDeadline");
            document.getElementById("event_maxNbRegistration").value=sessionStorage.getItem("maxNbRegistration");
            document.getElementById("event_infoEvent").value=sessionStorage.getItem("infoEvent");
            sessionStorage.removeItem("name");
            sessionStorage.removeItem("dateTimeStart");
            sessionStorage.removeItem("duration");
            sessionStorage.removeItem("registrationDeadline");
            sessionStorage.removeItem("maxNbRegistration");
            sessionStorage.removeItem("infoEvent");
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
        sessionStorage.setItem("name",name);
        sessionStorage.setItem("dateTimeStart",dateTimeStart);
        sessionStorage.setItem("duration",duration);
        sessionStorage.setItem("registrationDeadline",registrationDeadline);
        sessionStorage.setItem("maxNbRegistration",maxNbRegistration);
        sessionStorage.setItem("infoEvent",infoEvent);
    }

    const placeBtns = document.querySelectorAll(".js-place-btn");
    placeBtns.forEach((btn)=>{
        btn.addEventListener("click", (event)=>{
            event.preventDefault();
            stockDatas();
            window.location.href=event.target.href;
        });
    })
});




