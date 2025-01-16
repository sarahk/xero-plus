function ns_contacts() {
    this.idTag = '#tContacts';
    this.dataTable;
    this.currentButton = '';

    this.init_datatable = function () {

        if ($(this.idTag).length > 0) {
            this.dataTable = $(this.idTag).DataTable(this.dataTableOptions);
            const searchTermFromUrl = new URLSearchParams(window.location.search).get('search_term');
            if (searchTermFromUrl) {
                this.dataTable.search(searchTermFromUrl); // Set search term in DataTable if available from URL
                this.dataTable.draw();
            }
            this.setListeners();
        }
    };

    this.setListeners = function () {
        this.dataTable.on('draw.dt', () => {
            console.log('Table redrawn');
            $('.address').each(function () {
                let link = "<a href='https://maps.google.com/maps?q=" + encodeURIComponent($(this).text()) + "' target='_blank'>" + $(this).text() + "</a>";
                $(this).html(link);
            });
        });
    }


    this.dataTableOptions = {
        ajax: {
            url: "/json.php",
            data: (d) => {
                // Make sure this is updating every time `ajax.reload()` is called
                d.button = this.currentButton;
                d.endpoint = 'Contacts';
                d.action = 'List';
            }
        },
        processing: true,
        serverSide: true,
        columns: [
            {data: 'life_cycle', orderable: false},
            {data: 'total_due_icon', orderable: false, className: 'dt-right'},
            {data: "total_due", className: 'dt-right'},
            {data: "name", name: 'name'},
            {data: "phone_number", orderable: false},
            {data: "email_address", orderable: false},
            {data: "address_line1", orderable: false},
            {data: "action", orderable: false}
        ],
        paging: true,
        stateSave: true,
        layout: {
            topStart: {
                buttons: [
                    'pageLength',
                    {
                        extend: 'csv',
                        text: 'Export',
                        split: ['copy', 'excel', 'pdf', 'print']
                    }, {
                        text: 'All',
                        action: () => {
                            this.currentButton = 'All';
                            this.dataTable.ajax.reload();

                        }
                    }, {
                        text: 'Current',
                        action: () => {
                            this.currentButton = 'Current';
                            this.dataTable.ajax.reload();
                        }
                    }
                ]
            },
        },
        createdRow: (row, data, index) => {
            row.classList.add('bar-' + data.colour);
        }
    };
}

const nsContacts = new ns_contacts();
nsContacts.init_datatable();


function ns_contactModal() {
    this.idTag = '';
    this.init = function () {
        console.log('init');
        this.setListeners();
    };

    this.setListeners = function () {

        $('#contactSingle').on('show.bs.modal', (event) => {
            let button = $(event.relatedTarget);
            let contactId = button.attr('data-contactid'); // Extract info from data-* attributes
            let contractId = button.attr('data-contractid');

            this.getContactData(contactId, this.populateContactSingleModal);
            this.getChart(contractId);

            $('#refreshXero').prop('disabled', false)
                .html('Refresh/Xero');
        });

        $('#refreshXero').on('click', (event) => {
            event.preventDefault();
            this.refreshXero(event);
        });
    };

    this.getChart = function (contractId) {
        $('#imgBadDebts').attr('src',
            '/run.php?endpoint=image&imageType=baddebt&contract_id=' + contractId);
    }

    this.getContactData = (contactid, callbackfn) => {
        console.log('getContactData');
        console.log(contactid);
        let complete = false;
        setTimeout(function () {
            if (!complete) {
                $('#modalSpinnerContact').show();
            }
        }, 1000);

        let payload = {
            endpoint: 'Contacts',
            action: 'Singleton',
            contact_id: contactid,
        };

        $.getJSON("/json.php", payload, (data) => {
            callbackfn(data, contactid);
        }).done(function (data) {
            complete = true;
            $('#modalSpinnerContact').hide();
        });
    }

    this.populateContactSingleModal = (data, contactid) => {
        console.log('populateContactSingleModal');
        console.log(contactid);
        console.log(data.contacts);
        $('#contactSingleId').val(contactid);

        $("#refreshXero").attr("data-contactid", contactid)
            .attr("data-xeroTenantId", data.contacts.xerotenant_id);

        $('#internalData').html(data.contacts.xero_status + '<br>' + data.contacts.id);
        $('#contactName').val(data.contacts.name);
        $('#contactFirstName').val(data.contacts.first_name);
        $('#contactLastName').val(data.contacts.last_name);
        $('#contactEmail').val(data.contacts.email_address);
        $('#contactMobile').val(this.getMobile(data));
        //$('#contactPhone').html(data.contacts.phone);
        //$('#contactAddress').html(data.contacts.address);
        //$('#contactUpdate').text(data.contacts.updated_date_utc);
        $('#contactSingleLabel').text('Contact: ' + data.contacts.name);

        // button to go to the xero website
        $('#goXero').unbind();
        $('#goXero').on('click', function () {
            window.open('https://go.xero.com/Contacts/View/' + contactid, '_blank');
        });

    };

    this.getMobile = function (data) {

        // Default return values if no matching phone found
        let result = {
            phone_area_code: '',
            phone_number: ''
        };

        // Check if Phone array exists and has entries
        if (!data.Phone || !Array.isArray(data.Phone) || data.Phone.length === 0) {
            return '';
        }

        // First try to find MOBILE
        let mobilePhone = data.Phone.find(phone => phone.phone_type === 'MOBILE');

        // If no mobile, try to find DEFAULT
        if (!mobilePhone) {
            mobilePhone = data.Phone.find(phone => phone.phone_type === 'DEFAULT');
        }

        // If we found either MOBILE or DEFAULT, return those details
        if (mobilePhone) {
            result.phone_area_code = mobilePhone.phone_area_code || '';
            result.phone_number = mobilePhone.phone_number || '';
        }

        return result.phone_area_code + ' ' + result.phone_number;
    };


    this.refreshXero = function (event) {
        console.log('refreshXero');

        let button = $('#refreshXero');

        let payload = {                          // Data to send as query parameters
            endpoint: "Contact",
            action: "Refresh",
            xeroTenantId: button.attr('data-xerotenantid'),
            contact_id: button.attr('data-contactid'),
        };

        console.log(['refresh payload', payload]);

        $.ajax({
            url: "/xero.php",
            type: "GET",
            data: payload,
            success: (response) => {
                // Handle success
                this.getContactData(button.attr('data-contactid'), this.populateContactSingleModal);

                console.info('in the promise then statement')
                button.html('<i class="fa-solid fa-check"></i>')
                    .prop("disabled", true);
            },
            error: function (xhr, status, error) {
                // Handle error
                console.error("Error:", error);
            }
        });
    };
}

const nsContactModal = new ns_contactModal();
nsContactModal.init();
