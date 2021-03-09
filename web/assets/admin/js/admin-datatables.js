var App = (function () {
    'use strict';

    App.dataTables = function( ){

        // Common styling
        $.extend( true, $.fn.dataTable.defaults, {
            dom:
            "<'row mai-datatable-header'<'col-sm-12'f>>" +
            "<'row mai-datatable-body'<'col-sm-12'tr>>" +
            "<'row mai-datatable-footer'<'col-sm-3'i><'col-sm-6'p><'col-sm-3'l>>"
        } );

        $.extend( $.fn.dataTable.ext.classes, {
            sFilterInput:  "form-control form-control-sm",
            sLengthSelect: "form-control form-control-sm"
        } );

        // Simple ref list table (3 columns: id / value / action)
        $("#table-ref-list").dataTable({
            "order": [[ 1, 'asc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [0, 2] },
                { "orderable": false, "targets": [2] }
            ]
        });

        // Material list table
        $("#table-material-list").dataTable({
            "order": [[ 1, 'asc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [0, 3] },
                { "orderable": false, "targets": [2, 3] }
            ]
        });

        // Color list table
        $("#table-color-list").dataTable({
            "order": [[ 1, 'asc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [0, 2, 3] },
                { "orderable": false, "targets": [2, 3] }
            ]
        });

        // Tag list table
        $("#table-tag-list").dataTable({
            "order": [[ 2, 'asc' ], [ 1, 'asc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [0, 3] },
                { "orderable": false, "targets": [4] }
            ]
        });

        // User list table
        $("#table-user-list").dataTable({
            "order": [[ 3, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [0, 3, 4, 5] },
                { "orderable": false, "targets": [5] }
            ]
        });

        // Prospect list table
        $("#table-prospect-list").dataTable({
            "order": [[ 4, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [0, 4, 5] },
                { "orderable": false, "targets": [5] }
            ]
        });

        // Setting list table
        $("#table-setting-list").dataTable({
            "pageLength": 50,
            "order": [[ 0, 'asc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [0, 2, 3] },
                { "orderable": false, "targets": [2, 3] }
            ]
        });

        // Coupon list table
        $("#table-coupon-list").dataTable({
            "order": [[ 1, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [0, 3, 4, 5, 7] },
                { "orderable": false, "targets": [6, 7] }
            ]
        });

        // Printer list table
        $("#table-printer-list").dataTable({
            "order": [[ 4, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [2, 3, 4, 5] },
                { "orderable": false, "targets": [5] }
            ]
        });

        // Scanner list table
        $("#table-scanner-list").dataTable({
            "order": [[ 7, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [6, 7] },
                { "orderable": false, "targets": [] }
            ]
        });

        // Order list table
        $("#table-order-list").dataTable({
            "order": [[ 1, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [0, 1, 7, 8] },
                { "orderable": false, "targets": [2, 8] }
            ]
        });

        // Rating list table
        $("#table-rating-list").dataTable({
            "order": [[ 1, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [0, 1, 5] },
                { "orderable": false, "targets": [2] }
            ]
        });

        // Signal list table
        $("#table-signal-list").dataTable({
            "order": [[ 1, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [0, 5] },
                { "orderable": false, "targets": [5] }
            ]
        });

        // Messages list table
        $("#table-message-list").dataTable({
            "order": [[ 0, 'asc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [6] },
                { "orderable": false, "targets": [6] }
            ]
        });

        // Model list table
        $("#table-model-list").dataTable({
            "order": [[ 1, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [0, 1, 7, 9] },
                { "orderable": false, "targets": [] }
            ]
        });

        // Project list table
        $("#table-project-list").dataTable({
            "order": [[ 2, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [9] },
                { "orderable": false, "targets": [9] }
            ],
            "language": {
                "lengthMenu": "Afficher _MENU_ devis par page",
                "zeroRecords": "Aucun résultat",
                "info": "Page _PAGE_ sur _PAGES_",
                "search": "Rechercher",
                "infoEmpty": "Aucun résultat disponible",
                "infoFiltered": "(filtre sur un total de _MAX_ résultat(s)",
                "paginate": {
                    "previous": "Précédent",
                    "next": "Suivant"
                }
            }
        });

        // Admin Quotation list table
        $("#table-admin-quotation-list").dataTable({
            "order": [[ 6, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [9] },
                { "orderable": false, "targets": [9] }
            ]
        });

        // Quotation list table
        $("#table-quotation-list").dataTable({
            "order": [[ 0, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [5] },
                { "orderable": false, "targets": [5] }
            ],
            "language": {
                "lengthMenu": "Afficher _MENU_ devis par page",
                "zeroRecords": "Aucun résultat",
                "info": "Page _PAGE_ sur _PAGES_",
                "search": "Rechercher",
                "infoEmpty": "Aucun résultat disponible",
                "infoFiltered": "(filtre sur un total de _MAX_ résultat(s)",
                "paginate": {
                    "previous": "Précédent",
                    "next": "Suivant"
                }
            }
        });

        // Quotation list table
        $("#table-creation-list").dataTable({
            "order": [[ 0, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [5] },
                { "orderable": false, "targets": [5] }
            ],
            "language": {
                "lengthMenu": "Afficher _MENU_ modèles par page",
                "zeroRecords": "Aucun résultat",
                "info": "Page _PAGE_ sur _PAGES_",
                "search": "Rechercher",
                "infoEmpty": "Aucun résultat disponible",
                "infoFiltered": "(filtre sur un total de _MAX_ résultat(s)",
                "paginate": {
                    "previous": "Précédent",
                    "next": "Suivant"
                }
            }
        });

        // Order list table
        $("#table-command-list").dataTable({
            "order": [[ 0, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [5] },
                { "orderable": false, "targets": [5] }
            ],
            "language": {
                "lengthMenu": "Afficher _MENU_ commandes par page",
                "zeroRecords": "Aucun résultat",
                "info": "Page _PAGE_ sur _PAGES_",
                "search": "Rechercher",
                "infoEmpty": "Aucun résultat disponible",
                "infoFiltered": "(filtre sur un total de _MAX_ résultat(s)",
                "paginate": {
                    "previous": "Précédent",
                    "next": "Suivant"
                }
            }
        });

        // Order ModerationRule Table
        $("#table-moderation-rule-list").dataTable({
            "order": [[ 0, 'asc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [] },
                { "orderable": false, "targets": [0,1,2,3] }
            ]
        });

        // Project list table
        $("#table-project-customer-list").dataTable({
            "order": [[ 0, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [6] },
                { "orderable": false, "targets": [6] }
            ],
            "language": {
                "lengthMenu": "Afficher _MENU_ projets par page",
                "zeroRecords": "Aucun résultat",
                "info": "Page _PAGE_ sur _PAGES_",
                "search": "Rechercher",
                "infoEmpty": "Aucun résultat disponible",
                "infoFiltered": "(filtre sur un total de _MAX_ résultat(s)",
                "paginate": {
                    "previous": "Précédent",
                    "next": "Suivant"
                }
            }
        });

        // User order list table
        $("#table-user-order-list").dataTable({
            "order": [[ 1, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [0, 1, 5, 6] },
                { "orderable": false, "targets": [2, 6] }
            ]
        });

        // User project list table
        $("#table-user-project-list").dataTable({
            "order": [[ 1, 'desc' ]],
            "columnDefs": [
                { "searchable": false, "targets": [8] },
                { "orderable": false, "targets": [8] }
            ]
        });

    };

    return App;
})(App || {});