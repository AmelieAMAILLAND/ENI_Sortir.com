document.addEventListener("DOMContentLoaded", (event) => {
    let modal =null;
    const toggleModal = ()=>{
        modalContainer.classList.toggle("hidden");
        modalContainer.classList.toggle("block");
    }

    const openModal = (event) =>{
        event.preventDefault();
        const target = document.querySelector('.modal-container');
        target.classList.remove('hidden');
        target.removeAttribute('aria-hidden');
        target.setAttribute('aria-modal', true);
        modal = target;
    }

    const closeModal = (event) =>{
        if (modal) {
            event.preventDefault();
            const target = document.querySelector('.modal-container');
            target.classList.add('hidden');
            target.setAttribute('aria-hidden', true);
            target.removeAttribute('aria-modal');
            modal = null;
        }
    }

    document.querySelectorAll(".js-modal").forEach(btn => {
        btn.addEventListener("click", openModal);

    })

    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener("click", closeModal);
    })

});




