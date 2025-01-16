$('#rebuildMTables').on('click', rebuildMTables);

// this gets called from the menu but also from alerts
function rebuildMTables() {
    $.ajax({
        url: '/run.php',
        data: {
            endpoint: 'MaterialTables'
        },
        method: 'GET', // Specify the HTTP method, default is 'GET'
        success: function (response) {
            console.log('Rebuild Material Tables: successful', response);
            // Handle success logic here
            //alert('Material tables updated successfully!');
            $('#rebuildSuccess').removeClass('d-none');
            $('#rebuildSuccess').addClass('fade');
            $('#rebuildError').addClass('d-none');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Rebuild Material Tables: failed', textStatus, errorThrown);
            //alert('An error occurred while updating material tables.');
            $('#rebuildSuccess').addClass('d-none');
            $('#rebuildError').removeClass('d-none');
        }
    });
}

// Save the working with choices using cookies
$('#tenancy-auckland').change(function () {
    Cookies.set('auckland', $('#tenancy-auckland')[0].checked);
    console.log('auckland cookie');
});
$('#tenancy-waikato').change(function () {
    Cookies.set('waikato', $('#tenancy-waikato')[0].checked);
    console.log('waikato cookie');
});
$('#tenancy-bop').change(function () {
    Cookies.set('bop', $('#tenancy-bop')[0].checked);
    console.log('bop cookie');
});
