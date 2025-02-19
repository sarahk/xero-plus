<?php

namespace App\Models\Query\Traits;

use App\Models\ActivityModel;
use App\Models\ContactModel;

trait BadDebtsTrait
{

    protected function getSimpleContactCell(array $row): string
    {
        $output = [];
        $name = $row['name'] ?? !empty($row['first_name'] . $row['last_name']) ? "{$row['first_name']} {$row['last_name']}" : "#{$row['contact_id']}";

        $output[] = $this->getContractOverviewLink('91',
                ['contract_id' => $row['contract_id'], 'contact_id' => $row['contact_id']]
            )
            . "{$name}</a>";
        $output[] = $this->getPhoneNumbers($row['contact'] ?? []);
        return implode('<br>', $output);
    }

    /**
     * @param $row
     * @return string
     */
    protected function getFormattedContactCell($row): string
    {
        $output = $icons = [];
        if (empty($row['name'])) $row['name'] = $row['contact_id'];
        $name = $row['name'] ?? "{$row['first_name']} {$row['last_name']}" ?? "#{$row['contact_id']}";

        $contacts = new ContactModel($this->pdo);


        $link = $this->getContractOverviewLink('91',
            ['contract_id' => $row['contract_id'], 'contact_id' => $row['contact_id']]
        );
        $output[] = "$link$name</a>";


        $contact = $contacts->get('contact_id', $row['contact_id']);
        $this->logInfo('BadDebtsTrait getFormattedContactCell contact', $contact);
        if (count($contact) && !empty($contact['id'])) {
            $output[] = "<i class='fa-solid fa-at'></i> <a href='mailto:{$contact['contacts']['email_address']}'>{$contact['contacts']['email_address']}</a>";
            $output[] = $this->getPhoneNumbers($contact);
        }
        // should activity be saved instead of recreated
        $activity = new ActivityModel($this->pdo);

        $output[] = "<i class='fa-solid fa-comment-sms'></i> " . $this->readableDate($activity->getLastMessageDate($row['contact_id']));

        return implode('<br/>', $output);
    }

    protected function getPhoneNumbers(array $contact): string
    {
        $output = [];
        if (isset($contact['Phone']) && count($contact['Phone'])) {
            foreach ($contact['Phone'] as $phone) {
                if ($phone['phone_type'] === 'MOBILE') {
                    $icon = '<i class="fa-solid fa-mobile-screen-button"></i>';
                } else {
                    $icon = '<i class="fa-solid fa-phone"></i>';
                }
                if (!empty($phone['phone_number'])) {
                    $output[] = "$icon <a href='tel:{$phone['phone_area_code']}{$phone['phone_number']}'>({$phone['phone_area_code']}) {$phone['phone_number']}</a>";
                }
            }
        }
        return implode('<br/>', $output);
    }

    protected function getSentToday($contact_id): string
    {
        // should activity be saved instead of recreated
        $activity = new ActivityModel($this->pdo);

        $sentToday = $activity->getSentToday($contact_id);
        if ($sentToday) {
            return "<span class='text-success'>" . $activity->getSentToday($contact_id) . '</span>';
        }
        return '';
    }


}
