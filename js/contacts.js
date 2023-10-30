var tContacts = $('#tContacts').DataTable({
    ajax: {
        "url": "/json.php?endpoint=Contacts&action=Read"
    },
    processing: true,
    serverSide: true,
    columns: [
        {data: "checkbox", orderable: false},
        {data: "theyowe", className: 'dt-right'},
        {data: "name"},
        {data: "phone", orderable: false},
        {data: "email"},
        {data: "address", orderable: false},
        {data: "action", orderable: false}
    ],
    paging: true,
    stateSave: true,
    dom: "<'row'<'col-sm-12 col-lg-3' l >" + "<'col-sm-12 col-lg-6' B ><'col-sm-12 col-lg-3' f >>" + "trip",
    buttons: [
        {
            text: 'All',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                //dt.ajax.reload();
                tContacts.ajax.url('/json.php?endpoint=Contacts&action=Read').load();
            }
        }, {
            text: 'Active',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                tContacts.ajax.url('/json.php?endpoint=Contacts&action=Read&button=active').load();
            }
        }, {
            text: 'Archived',
            className: 'btn mr-1',
            action: function (e, dt, node, config) {
                tContacts.ajax.url('/json.php?endpoint=Contacts&action=Read&button=archived').load();
            }
        }]
});


$('#getContactTable').on('draw.dt', function () {
    console.log('Table redrawn');
    $('address').each(function () {
        var link = "<a href='http://maps.google.com/maps?q=" + encodeURIComponent($(this).text()) + "' target='_blank'>" + $(this).text() + "</a>";
        $(this).html(link);
    });
});
