document.addEventListener('DOMContentLoaded', function() {
    const preview = document.getElementById('profile-picture-preview');
    const defaultSrc = preview.getAttribute('data-default-src');
    const form = document.querySelector('form');

    function previewImage(event) {
        const fileInput = event.target;
        const file = fileInput.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
            };

            reader.readAsDataURL(file);
        }
    }

    function removePicture() {
        if (confirm("Êtes-vous sûr de vouloir supprimer votre photo de profil ?")) {
            preview.src = defaultSrc;

            addHiddenInput('remove_picture', '1');
        }
    }

    function addHiddenInput(name, value) {
        let input = document.getElementById(name + '_input');

        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.id = name + '_input';
            form.appendChild(input);
        }
        input.value = value;
    }

    document.querySelector('[name="profilePicture"]').addEventListener('change', previewImage);

    document.getElementById('remove-picture').addEventListener('click', removePicture);
});



