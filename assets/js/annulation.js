const elements = document.querySelectorAll('[data-bs-target]');

elements.forEach(element => {
    const modalTarget = element.getAttribute('data-bs-target');

    if (modalTarget.startsWith('#annulerModal')) {
        element.addEventListener('click', function() {
            const modalId = modalTarget;
            const modal = document.querySelector(modalId);
            const annulationSelect = modal.querySelector('#annulation_motifAnnulation');
            const autre = modal.querySelector('#annulation_autre');
            const label = modal.querySelector('label[for="annulation_autre"]');
            DisabledInput(annulationSelect, autre, label);

            annulationSelect.addEventListener('change', function() {
                DisabledInput(annulationSelect, autre, label);
            });
        });
    }
});

function DisabledInput(select, autre, label) {
    if (select.value !== 'Autre') {
        autre.disabled = true;
        autre.style.display = 'none';
        label.style.display = 'none';
    } else {
        autre.disabled = false;
        autre.style.display = 'block';
        label.style.display = 'block';
    }
}

