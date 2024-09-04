

const toggleMenuBtn = document.getElementById('menu-btn')
const toggleMenuImg = document.querySelector('#menu-btn img')
const menu = document.getElementById('toggled-menu')

let isFirstCLick = true;

toggleMenuBtn.addEventListener('click', toggleMenu)

function toggleMenu(){
    if(isFirstCLick){
        menu.classList.toggle("duration-300");
        menu.classList.toggle("transition-transform");
        isFirstCLick = !isFirstCLick;
    }
    menu.classList.toggle("max-sm:-translate-y-full");

    if(menu.classList.contains("max-sm:-translate-y-full")){
        toggleMenuImg.setAttribute("src", "/images/menu.svg")
        toggleMenuBtn.setAttribute('aria-expanded', false);
    }else{
        toggleMenuImg.setAttribute("src", "/images/cross.svg")
        toggleMenuBtn.setAttribute('aria-expanded', true);
    }
}

