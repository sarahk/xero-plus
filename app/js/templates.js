function ns_templates() {
    this.idTag = '#tTemplates';
    this.dataTable;
    this.currentButton = '';
    this.templateModal;
    this.modalTemplateId;
    this.modalMessagetype;
    this.modalTemplateStatus;
    this.modalLabel;
    this.modalSubject;

    this.init_datatable = function () {

        if ($(this.idTag).length > 0) {
            this.dataTable = $(this.idTag).DataTable(this.dataTableOptions);
            tinymce.init({
                selector: '#templatebody',
                plugins: 'anchor autolink charmap emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            });
            this.templateModal = $('#templateModal');
            this.modalTemplateId = $('#template_id');
            this.modalMessagetype = $('#messagetype');
            this.modalTemplateStatus = $('#templatestatus');
            this.modalLabel = $('#templatelabel');
            this.modalSubject = $('#templatesubject');

            this.setListeners();
        }
    };

    this.setListeners = function () {
        this.templateModal.on('show.bs.modal', this.showModal.bind(this));

        //$('#template_save').click(function () {
        $('#saveTemplateButton').on('click', (event) => {
            event.preventDefault();
            this.saveTemplate(event);
        });
        $('#template_editor').on('submit', (event) => {
            event.preventDefault();
            this.saveTemplate(event);
        });
    };


    this.dataTableOptions = {
        ajax: {
            url: "/json.php",
            data: (d) => {
                d.endpoint = 'Templates';
                d.action = 'List';
                d.button = this.currentButton;
            }
        },
        processing: true,
        serverSide: true,
        paging: true,
        stateSave: true,
        columns: [
            {data: "id", name: 'id'},
            {data: "status", name: 'status'},
            {data: "messagetype", name: 'messagetype'},
            {data: "label", name: 'label'},
            {data: "preview", sortable: false}
        ],
        layout: {
            topStart: {
                buttons: ['pageLength', {
                    extend: 'csv',
                    text: 'Export',
                    split: ['copy', 'excel', 'pdf', 'print']
                }, {
                    text: 'All',
                    action: () => {
                        //dt.ajax.reload();
                        this.currentButton = '';
                        this.dataTable.ajax.reload();
                    }
                }, {
                    text: 'Active',
                    action: () => {
                        this.currentButton = 'active';
                        this.dataTable.ajax.reload();
                    }
                },
                    {
                        text: 'Email',
                        action: () => {
                            this.currentButton = 'Email';
                            this.dataTable.ajax.reload();
                        }
                    },
                    {
                        text: 'SMS',
                        action: () => {
                            this.currentButton = 'SMS';
                            this.dataTable.ajax.reload();
                        }
                    }]
            }
        }
    };

    // S H O W   M O D A L
    this.showModal = function (event) {
        // Button that triggered the modal
        let target = event.relatedTarget

        this.modalTemplateId.val('');
        this.modalMessagetype.val('SMS');
        this.modalTemplateStatus.val(1);
        this.modalLabel.val('');
        this.modalSubject.val('');
        tinymce.activeEditor.setContent("");

        let template_id = target.getAttribute('data-template_id');
        if (template_id) {
            // get the existing record
            $.ajax({
                url: "/json.php",
                data: {
                    endpoint: 'Templates',
                    action: 'single',
                    id: template_id,
                },
                context: document.body
            }).done((data) => {
                this.modalTemplateId.val(template_id);
                this.modalMessagetype.val(data.templates.messagetype);
                this.modalTemplateStatus.val(data.templates.status);
                this.modalLabel.val(data.templates.label);
                this.modalSubject.val(data.templates.subject);
                tinymce.activeEditor.setContent(data.templates.body);
            });
        }
    }

    // S A V E   T E M P L A T E
    this.saveTemplate = function (event) {
        console.log('in submit');
        event.preventDefault();

        let data = {
            payload: {
                id: this.modalTemplateId.val(),
                messagetype: this.modalMessagetype.val(),
                status: this.modalTemplateStatus.val(),
                label: this.modalLabel.val(),
                subject: this.modalSubject.val(),
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

        }).done((result) => {
            console.log(['done', result]);
            this.dataTable.ajax.reload();
        });
    };
}

const nsTemplates = new ns_templates();
nsTemplates.init_datatable();
