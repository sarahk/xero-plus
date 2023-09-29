<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';
require_once('storage.php');
require_once('utilities.php');
require_once('functions.php');
require_once('models/ContactModel.php');
require_once('models/UserModel.php');
require_once('authorizedXero.php');

$pdo = getPDO();

$message = "no API calls";

$action = filter_input(INPUT_GET, 'action');
$id = (array_key_exists('id', $_GET)) ? intval($_GET['id']) : 0;

if (!isset($userId) || empty($userId)) {
    $user = new UserModel();
    $userId = $_SESSION['user_id'] = $user->getId('user_id', $xeroUserId);
}

switch ($action) {
    case 1:
        // Get Organisation details
        $apiResponse = $apiInstance->getOrganisations($xeroTenantId);
        $message = '<p>Organisation Name: ' . $apiResponse->getOrganisations()[0]->getName();
        $message .= '<p>' . $xeroTenantId;

        $accessToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $storage->getRefreshToken()
        ]);
        $options = [
            'scope' => ['openid email profile offline_access accounting.transactions accounting.settings']
        ];
        $connectionsResponse = $provider->getAuthenticatedRequest(
            'GET',
            'https://api.xero.com/Connections',
            $accessToken->getToken(),
            $options
        );

        $xeroTenantIdArray = $provider->getParsedResponse($connectionsResponse);


        // https://api.xero.com/connections
//Authorization: "Bearer " + access_token
//Content-Type: application/json
        //     exit;
        break;

    case 2:
        // Create Contact
        try {
            $person = new XeroAPI\XeroPHP\Models\Accounting\ContactPerson;
            $person->setFirstName("John")
                ->setLastName("Smith")
                ->setEmailAddress("john.smith@24locks.com")
                ->setIncludeInEmails(true);

            $arr_persons = [];
            array_push($arr_persons, $person);

            $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
            $contact->setName('FooBar')
                ->setFirstName("Foo")
                ->setLastName("Bar")
                ->setEmailAddress("ben.bowden@24locks.com")
                ->setContactPersons($arr_persons);

            $arr_contacts = [];
            array_push($arr_contacts, $contact);
            $contacts = new XeroAPI\XeroPHP\Models\Accounting\Contacts;
            $contacts->setContacts($arr_contacts);

            $apiResponse = $apiInstance->createContacts($xeroTenantId, $contacts);
            $message = 'New Contact Name: ' . $apiResponse->getContacts()[0]->getName();
        } catch (ApiException $e) {
            $error = AccountingObjectSerializer::deserialize(
                $e->getResponseBody(),
                '\XeroAPI\XeroPHP\Models\Accounting\Error',
                []
            );
            $message = "ApiException - " . $error->getElements()[0]["validation_errors"][0]["message"];
        }
        break;

    case 3: // filter invoices
        $if_modified_since = new DateTime("2019-01-02T19:20:30+01:00"); // \DateTime | Only records created or modified since this timestamp will be returned
        $if_modified_since = null;
        $where = 'Type=="ACCREC"'; // string
        $where = null;
        $order = null; // string
        $ids = null; // string[] | Filter by a comma-separated list of Invoice Ids.
        $invoice_numbers = null; // string[] |  Filter by a comma-separated list of Invoice Numbers.
        $contact_ids = null; // string[] | Filter by a comma-separated list of ContactIDs.
        $statuses = array("DRAFT", "SUBMITTED");
        $statuses = [];
        $page = 1; // int | e.g. page=1 – Up to 100 invoices will be returned in a single API call with line items
        $include_archived = null; // bool | e.g. includeArchived=true - Contacts with a status of ARCHIVED will be included
        $created_by_my_app = null; // bool | When set to true you'll only retrieve Invoices created by your app
        $unitdp = null; // int | e.g. unitdp=4 – You can opt in to use four decimal places for unit amounts

        try {
            $apiResponse = $apiInstance->getInvoices($xeroTenantId, $if_modified_since, $where, $order, $ids, $invoice_numbers, $contact_ids, $statuses, $page, $include_archived, $created_by_my_app, $unitdp);
            if (count($apiResponse->getInvoices()) > 0) {
                $message = 'Total invoices found: ' . count($apiResponse->getInvoices());
                //var_export($apiResponse->getInvoices());
            } else {
                $message = "No invoices found matching filter criteria";
            }
        } catch (Exception $e) {
            echo 'Exception when calling AccountingApi->getInvoices: ', $e->getMessage(), PHP_EOL;
        }
        break;

    case 9:
        // don't actually need to do anything?
        break;
    case 4:
        // Create Multiple Contacts
        try {
            $contact = new XeroAPI\XeroPHP\Models\Accounting\Contact;
            $contact->setName('George Jetson')
                ->setFirstName("George")
                ->setLastName("Jetson")
                ->setEmailAddress("george.jetson@aol.com");

            // Add the same contact twice - the first one will succeed, but the
            // second contact will throw a validation error which we'll catch.
            $arr_contacts = [];
            array_push($arr_contacts, $contact);
            array_push($arr_contacts, $contact);
            $contacts = new XeroAPI\XeroPHP\Models\Accounting\Contacts;
            $contacts->setContacts($arr_contacts);

            $apiResponse = $apiInstance->createContacts($xeroTenantId, $contacts, false);
            $message = 'First contacts created: ' . $apiResponse->getContacts()[0]->getName();

            if ($apiResponse->getContacts()[1]->getHasValidationErrors()) {
                $message = $message . '<br> Second contact validation error : ' . $apiResponse->getContacts()[1]->getValidationErrors()[0]["message"];
            }

        } catch (ApiException $e) {
            $error = AccountingObjectSerializer::deserialize(
                $e->getResponseBody(),
                '\XeroAPI\XeroPHP\Models\Accounting\Error',
                []
            );
            $message = "ApiException - " . $error->getElements()[0]["validation_errors"][0]["message"];
        }
        break;


    case 6:

        $jwt = new XeroAPI\XeroPHP\JWTClaims();
        $jwt->setTokenId((string)$storage->getIdToken());
        // Set access token in order to get authentication event id
        $jwt->setTokenAccess((string)$storage->getAccessToken());
        $jwt->decode();

        echo("sub:" . $jwt->getSub() . "<br>");
        echo("sid:" . $jwt->getSid() . "<br>");
        echo("iss:" . $jwt->getIss() . "<br>");
        echo("exp:" . $jwt->getExp() . "<br>");
        echo("given name:" . $jwt->getGivenName() . "<br>");
        echo("family name:" . $jwt->getFamilyName() . "<br>");
        echo("email:" . $jwt->getEmail() . "<br>");
        echo("user id:" . $jwt->getXeroUserId() . "<br>");
        echo("username:" . $jwt->getPreferredUsername() . "<br>");
        echo("session id:" . $jwt->getGlobalSessionId() . "<br>");
        echo("authentication_event_id:" . $jwt->getAuthenticationEventId() . "<br>");

        break;

    case 10:
        // enquiries
        $contact = new ContactModel($pdo);
        $data = $contact->get($id);
        if (array_key_exists('debug', $_GET)) {
            debug($data);
            exit;
        }
        break;

    case 11:
        // map of cabin locations
        // do nothing?

        break;


    default:
        // nothing to do
}


require_once('views/header.php');

?>

<div>
    <?php

    switch ($action) {
        case 1:
            include 'views/organisations_list.php';
            break;
        case 5:
            include 'views/contacts_index.php';
            break;
        case 9:
            include 'views/invoices_index.php';
            break;
        case 10:
            include 'views/enquiry-edit.php';
            break;
        case 11:
            include 'views/cabin-locations.php';
            break;
    }
    ?>
</div>
<?php
require_once('views/footer.php');
?>
