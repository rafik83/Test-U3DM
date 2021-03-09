$(document).ready(function () {
    $('form .col-sm-2').addClass('col-sm-3').removeClass('col-sm-2');
    $('form .col-sm-10').addClass('col-sm-9').removeClass('col-sm-10');
    $('.formMakerSwitcher').click(function() {
        $('#formMaker').show();
        $('#formMakerText').show();
        $('#formCustomer').hide();
        $('#formCustomerText').hide();
    });
    $('.formCustomerSwitcher').click(function() {
        $('#formMaker').hide();
        $('#formMakerText').hide();
        $('#formCustomer').show();
        $('#formCustomerText').show();
    });
    $('.formProspectSwitcher').click(function() {
        $(this).parent().parent().siblings('input').attr('checked', true);
    });
    $('#radio_individual:radio').change(function() {
        $('#appbundle_prospect_customer_company').parent().parent().hide();
    });
    $('#radio_company:radio').change(function() {
        $('#appbundle_prospect_customer_company').parent().parent().show();
    });
});