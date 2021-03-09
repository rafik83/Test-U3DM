function selectChangeListener() {
    $('select').not('.select2entity').change(function(event) {
        var selectValue = $(this).val();
        $(this).children('option').removeAttr('selected');
        $(this).children('option[value='+selectValue+']').attr('selected', '');
    });
}

function select2UnselectListener() {
    $('select.select2entity').on('select2:unselect', function(event) {
        // remove the underlying select option (to allow proper duplication if necessary)
        var valueToUnselect = event.params.data.id;
        $(this).children('option[value='+valueToUnselect+']').remove();
    });
}

$(document).ready(function () {
    var isDuplicate = false;
    var idDuplicated;
    $('.printer-products-collection').collection({
        prefix: 'product',
        children: [{
            selector: '.printer-product-finishings-collection',
            prefix: 'finishing',
            allow_up: false,
            allow_down: false
        }],
        init_with_n_elements: 1,
        min: 1,
        preserve_names: true,
        add: '<a href="#" class="btn btn-rounded"><span class="glyphicon glyphicon-plus"></span> ' + product_add_button_label + '</a>',
        allow_up: false,
        allow_down: false,
        allow_duplicate: true,
        before_duplicate: function(collection, element) {
            isDuplicate = true;
            idDuplicated = element.attr('id');
            // make sure all the current element colors will be duplicated (selected tag must be set to empty)
            $('#'+idDuplicated+' .select2entity option').removeAttr('selected').attr('selected', '');
        },
        after_add: function(collection, element) {
            var idNew = element.attr('id');
            $('#'+idNew+' .select2entity').select2entity();
            if (isDuplicate) {
                // fix the duplicate issues on the new field
                $('#'+idNew+' .select2entity').parent().children('span.select2-container').last().remove();
                // fix the duplicate issues on the duplicated field and reset it
                $('#'+idDuplicated+' .select2entity').select2('destroy');
                $('#'+idDuplicated+' .select2entity').removeAttr('data-select2-id');
                $('#'+idDuplicated+' .select2entity option').removeAttr('selected').attr('selected', '');
                $('#'+idDuplicated+' .select2entity option').removeAttr('data-select2-id');
                $('#'+idDuplicated+' .select2entity').select2entity();
                // reset global var
                isDuplicate = false;
            }
            select2UnselectListener();
            selectChangeListener();
        },
        after_remove: function(collection, element) {
            select2UnselectListener();
            selectChangeListener();
        }
    });
    select2UnselectListener();
    selectChangeListener();
});