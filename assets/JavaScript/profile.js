function previewImage(event) {
    const fileInput = event.target;
    const file = fileInput.files[0];
    const preview = document.getElementById('profile-picture-preview');

    if (file) {
        const reader = new FileReader();

        reader.onload = function(e) {
            preview.src = e.target.result;
        }

        reader.readAsDataURL(file);
    }
}

function removePicture() {
    const preview = document.getElementById('profile-picture-preview');
    const defaultSrc = preview.getAttribute('data-default-src');
    preview.src = defaultSrc;

    // Ajoute un champ caché pour indiquer que la photo doit être supprimée
    const form = document.querySelector('form');
    let input = document.getElementById('remove_picture_input');

    if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_picture';
        input.id = 'remove_picture_input';
        input.value = '1';
        form.appendChild(input);
    } else {
        input.value = '1';
    }
}



