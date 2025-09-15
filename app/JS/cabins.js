$(document).ready(function () {

    $(document).ajaxStart(function () {
        $("#modalSpinner").show();
    });
    $(document).ajaxStop(function () {
        $("#modalSpinner").hide();
    });

    if ($('#tCabins').length) {
        var tCabins = $('#tCabins').DataTable({
            ajax: {
                url: "/json.php?endpoint=Cabins&action=Read",
            },
            processing: true,
            stateSave: true,
            serverSide: true,
            columns: [
                {data: "number"},
                {data: "style"},
                {data: "status"},
                {data: "contact",},
                {data: "paintinside"},
                {data: "actions"},
            ],
            paging: true,
            dom: "<'row'<'col-sm-12 col-lg-3' l >" + "<'col-sm-12 col-lg-6' B ><'col-sm-12 col-lg-3' f >>" + "trip",
            createdRow: (row, data, index) => {
                row.classList.add('bar-' + data.colour);
            },
            buttons: [{
                text: 'All',
                className: 'btn mr-1',
                action: function () {
                    //dt.ajax.reload();
                    tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read').load();
                }
            }, {
                text: 'New',
                className: 'btn mr-1',
                action: function () {
                    tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=new').load();
                }
            }, {
                text: 'Active',
                className: 'btn mr-1',
                action: function () {
                    tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=active').load();
                }
            }, {
                text: 'Repairs',
                className: 'btn mr-1',
                action: function (e, dt, node, config) {
                    tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=repairs').load();
                }
            }, {
                text: 'Disposed',
                className: 'btn mr-1',
                action: function (e, dt, node, config) {
                    tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=disposed').load();
                }
            }, {
                text: 'Yard',
                className: 'btn mr-1',
                action: function (e, dt, node, config) {
                    tCabins.ajax.url('/json.php?endpoint=Cabins&action=Read&button=yard').load();
                }
            }]

        });
    }
    // M O D A L
    if ($('#cabinSingle').length) {

        $('#cabinSingle').on('show.bs.modal', function (event) {

            $('#modalSpinnerCabin').show();

            let button = $(event.relatedTarget) // Button that triggered the modal
            let key = button.data('key') // Extract info from data-* attributes
            let url = "/json.php?endpoint=Cabins&action=Single&key=" + key;
            console.log(url);
            $.getJSON("/json.php?endpoint=Cabins&action=Single&key=" + key, function (data) {
                console.log(data);

                $("#modal-header").removeClass();
                $("#modal-header").addClass("modal-header");
                $("#modal-header").addClass(data.tenancyshortname);


                $('#cabinnumber').html(data.cabinnumber);

                // if the modal has opened before the list and table will have values, we need to empty them
                $("#cabindetails").empty();
                $("#cabintasks tbody").empty();

                $("#cabindetails").append($("<li class='list-group-item'>Style: " + data.cabinstyle + "</li>"));
                $("#cabindetails").append($("<li class='list-group-item'>Status: " + data.status + "</li>"));
                $("#cabindetails").append($("<li class='list-group-item'>WOF: " + formatDate(data.wof) + "</li>"));

                if (data.owner !== '---') $("#cabindetails").append($("<li class='list-group-item'>Owner: " + data.ownername + "</li>"));

                $("#cabindetails").append($("<li class='list-group-item'>Operator: " + data.tenancy + "</li>"));
                $("#cabindetails").append($("<li class='list-group-item'>" + data.updated + "</li>"));

                if (data.tasklist.length > 0) {
                    data.tasklist.forEach((task) => {
                        console.log(task);
                        let newRow = $("<tr></tr>");

                        // Add table data (columns) to the row
                        newRow.append("<td><a href='/page.php?action=15&id=" + key + "'>" + task.name + "</a></td>");
                        newRow.append("<td>" + task.status + "</td>");
                        newRow.append("<td>" + formatDate(task.due_date) + "</td>");
                        newRow.append("<td><button id='closetask" + key + "'>Close</button></td>");
                        // Append the new row to the table's <tbody>
                        $("#cabintasks tbody").append(newRow);
                        $('#closetask' + key).on('click', function () {
                            $.getJSON("/json.php?endpoint=Tasks&action=Close&key=" + task.id, function (data) {
                                swal("Good job!", "You clicked the button!", "success");
                            });
                        });
                    });
                } else {
                    let newRow = $("<tr></tr>");
                    newRow.append("<td colspan='4'>No outstanding tasks</td>");
                    // Append the new row to the table's <tbody>
                    $("#cabintasks tbody").append(newRow);
                }
                $('#cabinedit').on('click', function () {
                    window.open('/page.php?action=14&cabin_id=' + key);
                });
                $('#addtask').on('click', function () {
                    window.open('/page.php?action=15&cabin_id=' + key);
                });
            });
            $('#modalSpinnerCabin').hide();

        });
    }
});
