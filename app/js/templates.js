let currentTemplateButton = '';
let templateModal = document.getElementById('templateModal');
let modalTemplateId = templateModal.querySelector('#template_id');
let modalMessagetype = templateModal.querySelector('#messagetype');
let modalTemplateStatus = templateModal.querySelector('#templatestatus');
let modalLabel = templateModal.querySelector('#templatelabel');
let modalSubject = templateModal.querySelector('#templatesubject');
let $tTemplates;

if ($('#tTemplates').length) {
    $tTemplates = $('#tTemplates').DataTable({
        ajax: {
            url: "/json.php",
            data: function (d) {
                d.endpoint = 'Templates';
                d.action = 'List';
                d.button = currentTemplateButton;
            }
        },
        processing: true,
        serverSide: true,
        paging: true,
        stateSave: true,
        columns: [
            {data: "id"},
            {data: "status"},
            {data: "messagetype"},
            {data: "label"},
            {data: "preview"}
        ],
        layout: {
            topStart: {
                buttons: ['pageLength', {
                    extend: 'csv',
                    text: 'Export',
                    split: ['copy', 'excel', 'pdf', 'print']
                }, {
                    text: 'All',
                    action: function () {
                        //dt.ajax.reload();
                        currentTemplateButton = ''
                        $tTemplates.ajax.reload();
                    }
                }, {
                    text: 'Active',
                    action: function () {
                        currentTemplateButton = 'active';
                        $tTemplates.ajax.reload();

                    }
                }]
            }
        },
    });

    tinymce.init({
        selector: '#templatebody',
        plugins: 'anchor autolink charmap emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',

    });


    templateModal.addEventListener('show.bs.modal', function (event) {
        // Button that triggered the modal
        let target = event.relatedTarget
        // Extract info from data-bs-* attributes
        let template_id = target.getAttribute('data-template_id');

        if (template_id === '0') {

            modalTemplateId.value = ''
            modalMessagetype.value = 'SMS';
            modalTemplateStatus.value = 1;
            modalLabel.value = '';
            modalSubject.value = '';
            tinymce.activeEditor.setContent("");
            //tinymce.get("myTextarea").setContent("<p>Hello world!</p>");
        } else {
            $.ajax({
                url: "/json.php",
                data: {
                    endpoint: 'Templates',
                    action: 'single',
                    id: template_id,
                },
                context: document.body
            }).done(function (data) {
                modalTemplateId.value = template_id;
                modalMessagetype.value = data.templates.messagetype;
                modalTemplateStatus.value = data.templates.status;
                modalLabel.value = data.templates.label;
                modalSubject.value = data.templates.subject;
                tinymce.activeEditor.setContent(data.templates.body);
            });
        }
    });

    //$('#template_save').click(function () {
    $('#saveTemplateButton').on('click', function (e) {
        saveTemplate(e, $tTemplates);
    });
    $('#template_editor').on('submit', function (e) {
        saveTemplate(e, $tTemplates);
    });
}

function saveTemplate(event, dataTable) {
    console.log('in submit');
    event.preventDefault();

    let data = {
        payload: {
            id: modalTemplateId.value,
            messagetype: modalMessagetype.value,
            status: modalTemplateStatus.value,
            label: modalLabel.value,
            subject: modalSubject.value,
            body: tinymce.activeEditor.getContent("template_body")
        },
        endpoint: 'Save',
        form: 'Template'
    };

    console.log(data);
    $.ajax({
        type: "POST",
        url: '/run.php',
        data: data,

    }).done(function (result) {
        console.log(['done', result]);
        dataTable.ajax.reload();
        //templateModal.hide();
    });
}
