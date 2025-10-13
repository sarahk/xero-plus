<?php

namespace App\Classes;

use App\Classes\ExtraFunctions;
use App\Classes\Utilities;

/*
 * https://developer.xero.com/documentation/guides/how-to-guides/deep-link-xero/
 */

final  class XeroUrl
{
    private array $tenancies;

    public function __construct(
        private string $host = 'go.xero.com'
    )
    {
        $this->tenancies = ExtraFunctions::getTenancyArray();
    }

    private function classic(string $xerotenant_id, string $redirectPath, array $query = []): string
    {
        $short_code = $this->tenancies[$xerotenant_id]['xero_shortcode'];
        $redirect = $redirectPath . ($query ? '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '');
        $qs = http_build_query([
            'shortcode' => $short_code,
            'redirecturl' => $redirect,
        ], '', '&', PHP_QUERY_RFC3986);
        error_log($qs);
        return "https://{$this->host}/organisationlogin/default.aspx?{$qs}";
    }

    public function viewInvoice(string $xerotenant_id, string $invoiceId): string
    {
        return $this->classic($xerotenant_id, '/AccountsReceivable/View.aspx', ['InvoiceID' => $invoiceId]);
    }

    public function editInvoice(string $xerotenant_id, string $invoiceId): string
    {
        return $this->classic($xerotenant_id, '/AccountsReceivable/Edit.aspx', ['InvoiceID' => $invoiceId]);
    }

    public function viewContact(string $xerotenant_id, string $contactId): string
    {
        return $this->classic($xerotenant_id, '/Contacts/View.aspx', ['ContactID' => $contactId]);
    }

    public function getIconLink(string $url): string
    {
        return "<a href='$url' target='_blank'><img src='/images/Xero_software_logo.svg' height='15' width='15' style='margin-left: .5em' alt='Link to record in Xero'></a>";
    }
}