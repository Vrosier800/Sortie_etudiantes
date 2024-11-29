document.addEventListener("DOMContentLoaded", function () {

    const afficherLinks = document.querySelectorAll('[id^="afficher-"]');

    afficherLinks.forEach(function (link) {
        link.addEventListener("click", function (event) {
            event.preventDefault();
            let url = link.href;

            setTimeout(function () {
                const myModal = bootstrap.Modal.getInstance(
                    document.getElementById("exampleModalToggle")
                );
                if (myModal) myModal.hide();
                window.location.href = url;
            }, 3);
        });
    });
    const modalElement = document.getElementById("exampleModalToggle");
    if (modalElement) {
        const myModal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        myModal.hide();
    }
});