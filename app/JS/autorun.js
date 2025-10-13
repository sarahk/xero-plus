// Functions to load data from Xero
import {fetchJSON} from "/JS/ui/helpers.js";

class AutoRunXeroImports {

    constructor() {
        this.tenancies = ['auckland', 'waikato', 'bop'];

        this.intervalId = setInterval(() => {
            // let qs = new URLSearchParams({
            //     endpoint: "Xero",
            // });
            //await data = fetchJSON(`/json.php?${qs.toString()}`);

            $.ajax({
                url: "/json.php",
                data: {endpoint: "Xero"}
            })
                .done((data) => {
                    console.log(data);
                    if (!data.result) {
                        clearInterval(this.intervalId);
                    }
                })
                .fail((jqXHR, textStatus, errorThrown) => {
                    console.error('setInterval Ajax request failed:', textStatus, errorThrown);
                    clearInterval(this.intervalId);
                });


            // Load data for each tenancy if the cookie is set

            for (let i = 0; i < this.tenancies.length; i++) {
                if (Cookies.get(this.tenancies[i]) === 'true') {
                    this.loadInvoicesFromXero(this.tenancies[i]);
                    this.loadPaymentsFromXero(this.tenancies[i]);
                }
            }
        }, 100000);
    }

    loadInvoicesFromXero(tenancy) {
        console.log(['loadInvoicesFromXero', tenancy]);
        $('#loadfromxerospinner').show();
        $.ajax({
            url: "/xero.php",
            data: {
                endpoint: 'Invoices',
                action: 'refresh',
                tenancy: tenancy
            },
            type: 'GET',
            complete: function () {
                $('#loadfromxerospinner').hide();
            }
        });
    }


    loadContactsFromXero(tenancy) {
        console.log('loadContactsFromXero: ' + tenancy);
        $.ajax({
            url: "/xero.php",
            data: {
                endpoint: 'Contacts',
                action: 'refresh',
                tenancy: tenancy
            },
            type: 'GET',
        });
    }


    loadPaymentsFromXero(tenancy) {
        console.log('loadPaymentsFromXero: ' + tenancy);
        $.ajax({
            url: "/xero.php",
            data: {
                endpoint: 'Payments',
                action: 'readAll',
                tenancy: tenancy
            },
            type: 'GET',
        });
    }

// Set an interval to check the token every minute


}

const nsAutoRunXeroImports = new AutoRunXeroImports();
