document.addEventListener("DOMContentLoaded", (event) => {
    const editLink = document.getElementById('place-edit');
    document.getElementById('event_place').addEventListener('change', function(){
        const value = this.value;
        editLink.href=`/place/${value}/edit`;
    })
});




