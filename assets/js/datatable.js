$(document).ready(function () {

    let visibleColumns = [3, 4, 5];
    let superAdmin = document.getElementById('admin') ? document.getElementById('admin') : null;
    if (superAdmin !== null) {
        visibleColumns.push(6);
    }

    var table = new DataTable('#myTable', {
        responsive: true,
        paging: true,
        searching: true,
        ordering: true,
        stateSave: true,
        language: {
            "sSearch": "Rechercher:",
            "sLengthMenu": "Afficher _MENU_ entrées par page",
            "sInfo": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
            "sInfoEmpty": "Affichage de 0 à 0 sur 0 entrées",
            "sInfoFiltered": "(filtré à partir de _MAX_ entrées)",
            "oPaginate": {
                "sFirst": "Premier",
                "sPrevious": "Précédent",
                "sNext": "Suivant",
                "sLast": "Dernier"
            }
        },
        columnDefs: [

            { targets: visibleColumns, responsivePriority: 1, visible: true }
        ],
        drawCallback: function(settings) {
            reattachCheckboxEvents();
        }
    });

    function reattachCheckboxEvents() {
        $('.disable-checkbox, .delete-checkbox').off('change').on('change', function () {
            if ($(this).is(':checked')) {
                console.log('Checkbox activée : ' + $(this).attr('name'));
            } else {
                console.log('Checkbox désactivée : ' + $(this).attr('name'));
            }
        });
    }

    table.on('draw', function () {

        reattachCheckboxEvents();

    });

    reattachCheckboxEvents();

    function toggleRowClickEvent() {
        if ($(window).width() < 768) {
            $('#myTable tbody').on('click', 'tr', function (event) {
                if ($(event.target).is('input[type="checkbox"]')) {
                    // Empêcher l'action de l'événement de clic de la ligne
                    return;
                }

                var row = table.row(this);
                console.log(row.child.isShown());

                // Délayer l'ouverture/fermeture de la ligne pour éviter la fermeture immédiate
                setTimeout(function () {
                    if (row.child.isShown()) {
                        row.child.hide();
                    } else {
                        row.child(format(row.data())).show();
                        reattachCheckboxEvents();
                    }
                }, 100);  // Délai de 100ms, ajustez selon vos besoins
            });
        } else {
            $('#myTable tbody').off('click');
        }
    }


    toggleRowClickEvent();
    $(window).resize(toggleRowClickEvent);


    function format(d) {
        // Construction dynamique de la ligne déroulante
        let editUrl = d[6]; // Colonne cachée pour l'URL d'édition
        let editRow = '';

        // Si l'URL d'édition est définie, ajoutez une ligne pour l'édition
        if (editUrl) {
            editRow = `
            <tr>
                <td><strong>Modifier utilisateur:</strong></td>
                <td>${editUrl}</td>
            </tr>`;
        }

        return `
        <table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">
            <tr>
                <td><strong>Campus:</strong></td>
                <td>${d[2]}</td>
            </tr>
            <tr>
                <td><strong>Statut:</strong></td>
                <td>${d[3]}</td>
            </tr>
            <tr>
                <td><strong>Désactiver:</strong></td>
                <td>${$(d[4]).html()}</td>
            </tr>
            <tr>
                <td><strong>Supprimer:</strong></td>
                <td>${$(d[5]).html()}</td>
            </tr>
            ${editRow}
        </table>`;
    }


    function updateColumnVisibility() {
        var isMobile = $(window).width() < 768;
        table.column(3).visible(!isMobile);
        table.column(4).visible(!isMobile);
        table.column(5).visible(!isMobile);
        table.column(6).visible(!isMobile);
    }


    updateColumnVisibility();
    $(window).resize(updateColumnVisibility);
});