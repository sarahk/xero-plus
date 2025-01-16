$(document).ready(function () {

    if ($('#tCabinTasks').length) {
        let tCabins = $('#tCabinTasks').DataTable({
            ajax: {
                url: "/json.php",
                data: {
                    endpoint: 'Tasks',
                    action: 'List',
                    key: cabin_id,
                }
            },
            processing: true,
            stateSave: true,
            serverSide: true,
            columns: [
                {data: "icon"},
                {data: "id"},
                {data: 'status'},
                {data: "name"},
                {data: "due_date",},
            ],
            paging: true,
        });
    }

    // M O D A L
    if ($('#taskSingle').length) {

        $('#taskSingle').on('show.bs.modal', function (event) {

            $('#modalSpinnerTask').show();

            let button = $(event.relatedTarget) // Button that triggered the modal
            let key = button.data('key') // Extract info from data-* attributes
            let url = "/json.php?endpoint=Tasks&action=Single&key=" + key;
            console.log(url);
            $.getJSON("/json.php?endpoint=Tasks&action=Single&key=" + key, function (data) {
                console.log(data);

                $("#modal-header").removeClass();
                $("#modal-header").addClass("modal-header");
                $("#modal-header").addClass(data.tenancyshortname);


                $('#taskId').html(data.id);

                // if the modal has opened before the list and table will have values, we need to empty them
                $("#taskdetails").empty();
                $("#taskdetails tbody").empty();

                if (data.cabin_id > '') {
                    $("#taskdetails").append($("<li class='list-group-item'>Cabin: " + data.cabin_id + "</li>"));
                }
                $("#taskdetails").append($("<li class='list-group-item'>" + data.name + "</li>"));
                $("#taskdetails").append($("<li class='list-group-item'>Status: " + data.status + "</li>"));
                $("#taskdetails").append($("<li class='list-group-item'>Due Date: " + formatDate(data.due_date) + "</li>"));


                $("#cabindetails").append($("<li class='list-group-item'>Operator: " + data.tenancy + "</li>"));
                $("#cabindetails").append($("<li class='list-group-item'>" + formatDate(data.updated) + "</li>"));


                $('#taskedit').on('click', function () {
                    window.open('/authorizedResource.php?action=14&cabin_id=' + key);
                });
                $('#closetask').on('click', function () {
                    window.open('/authorizedResource.php?action=15&cabin_id=' + key);
                });
            });
            $('#modalSpinnerCabin').hide();

        });
    }
});
