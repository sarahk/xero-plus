$(document).ready(function () {

    if ($('#tHomeTasks').length) {
        let tCabins = $('#tHomeTasks').DataTable({
            ajax: {
                url: "/json.php?endpoint=Tasks&action=ListHome",
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
});
