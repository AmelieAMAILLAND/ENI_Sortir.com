document.addEventListener("DOMContentLoaded", (event) => {
    // changement du lien d'édition du lieu en fonction du lieu sélectionné dans le select
    const editLink = document.getElementById('place-edit');
    document.getElementById('place-name').addEventListener('change', function(){
        const value = this.value;
        editLink.href=`/place/${value}/edit`;
    })

    // stockage et récupération des données du formulaire lors de la création ou de la modification d'un lieu

    const destockDatas = () =>{
        if (sessionStorage.getItem('datas')){
            datas=JSON.parse(sessionStorage.getItem("datas"));
            console.log(datas);
            document.getElementById("event_name").value=datas.name;
            document.getElementById("event_dateTimeStart").value=datas.dateTimeStart;
            document.getElementById("event_duration").value=datas.duration;
            document.getElementById("event_registrationDeadline").value=datas.registrationDeadline;
            document.getElementById("event_maxNbRegistration").value=datas.maxNbRegistration;
            document.getElementById("event_infoEvent").value=datas.infoEvent;
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
        const datas = JSON.stringify({'name':name, 'dateTimeStart':dateTimeStart, 'duration':duration, 'registrationDeadLine':registrationDeadline,
        'maxNbRegistration':maxNbRegistration, 'infoEvent':infoEvent})
        sessionStorage.setItem('datas', datas);
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




