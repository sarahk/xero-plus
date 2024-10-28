<?php

namespace App\Views\Widgets;

use App\ExtraFunctions;


class FormBuilder
{


    /**
     * @param string $id
     * @param string $name
     * @param string $label
     * @param bool $required
     * @param string $type
     * @param string $value
     * @return void
     */
    static function input(string $id, string $name, string $label, bool $required = false, string $type = 'text', string $value = ''): void
    {
        ?>
        <div class='form-group'>
            <label class='form-label' for='<?= $id; ?>'><?= $label; ?></label>
            <?php self::inputOnly($id, $name, $label, $required, $type, $value); ?>
        </div>
        <?php
    }

    static function inputOnly(string $id, string $name, string $placeholder, bool $required, string $type = 'text', string $value = ''): void
    {
        ?>
        <input class="form-control" id='<?= $id; ?>'
               name='<?= $name; ?>' placeholder="<?= $placeholder; ?>"
               type="<?= $type; ?>" value="<?= $value; ?>" <?= ($required ? 'required' : ''); ?>>
        <?php
    }

    /**
     * @param $id String
     * @param $name String
     * @param $value String the starting value of the hidden field
     * @return void
     */
    static function hidden(string $id, string $name, null|string $value = ''): void
    {
        ?>
        <input type="hidden" id="<?= $id; ?>" name="<?= $name; ?>" value="<?= $value; ?>">
        <?php
    }

    static function inputs(string $label, array $fields, bool $required = false): void
    {

        ?>
        <label class='form-label' for='<?= $fields[0]['name']; ?>'><?= $label; ?></label>
        <div class="form-row">
            <div class='form-group col-md-6'>
                <div class="form-group">
                    <?php self::inputsOnly($fields, $required); ?>
                </div>
            </div>
        </div>
        <?php
    }

    static function InputsOnly(array $fields, bool $required = false): void
    {
        foreach ($fields as $row):
            self::inputOnly($row['id'], $row['name'], $row['placeholder'], $required, $row['type'], $row['value']);
        endforeach;
    }

    static function textarea($id, $name, $label, $value = ''): void
    {
        ?>
        <div class="form-group">
            <label class="form-label" for="<?= $id; ?>"><?= $label; ?></label>
            <textarea class="form-control" id='<?= $id; ?>' name='<?= $name; ?>'
                      placeholder="<?= $label; ?>" rows="3"
                      spellcheck="false"><?= $value; ?></textarea>
        </div>

        <?php
    }

    /**
     * @param string $name
     * @param string $label
     * @param string $choices
     * @param string $value
     * @return void
     */
    static function radio(string $name, string $label, array $choices, null|string $value = ''): void
    {
        ?>
        <div class="form-group ">
            <div class="form-label"><?= $label; ?></div>
            <div class="custom-controls-stacked">
                <?php foreach ($choices as $row): ?>
                    <label class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" name="<?= $name; ?>"
                               value="<?= $row['name']; ?>"
                            <?= ($row['name'] == $value ? 'checked=""' : ''); ?>>
                        <span class="custom-control-label"><?= (array_key_exists('label', $row) ? $row['label'] : $row['name']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    static function select(string $id, string $name, string $label, string $choices): void
    {
        ?>
        <div class='form-group'>
            <label class="form-label" for='<?= $id; ?>'><?= $label; ?></label>
            <?php self::selectOnly($id, $name, $choices); ?>
        </div>
        <?php
    }

    static function selectOnly(string $id, string $name, string $choices): void
    {
        ?>
        <select class="form-control" id='<?= $id; ?>' name='<?= $name; ?>'
                data-bs-placeholder="Choose One" tabindex="-1">
            <option label="Choose one"></option>
            <?php echo $choices; ?>
        </select>
        <?php
    }


    static function checkbox($id, $name, $label, $valueWhenChecked, $value): void
    {
        $checked = ($value === $valueWhenChecked) ? ' checked="checked" ' : '';
        ?>
        <label class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input"
                   name="<?= $name; ?>"
                   id="<?= $id; ?>"
                   value="<?= $valueWhenChecked; ?>"
                <?= $checked; ?>>
            <span class="custom-control-label"><?= $label; ?></span>
        </label>
        <?php
    }

    static function splitName(string $what, string $name): string
    {
        if (empty($name)) return '';

        $bits = explode(' ', $name);

        if ($what === 'first') {
            return match (count($bits)) {
                default => $bits[0]
            };
        }
        return match (count($bits)) {
            1 => $bits[0],
            3 => $bits[1] . ' ' . $bits[2],
            default => $bits[1]
        };
    }

    /**
     * @param string $id
     * @param string $name
     * @param string $label
     * @param string|null $value
     * @return void
     */
    static function datePicker(string $id, string $name, string $label, null|string $value = ''): void
    {
        ?>
        <div class='form-group'>
            <label class='form-label' for='scheduled-delivery-date'><?= $label ?></label>
            <?php self::datePickerOnly($id, $name, $value); ?>
        </div>
        <?php
    }

    static function datePickerOnly(string $id, string $name, null|string $value = ''): void
    {
        ?>
        <div class="input-group">
            <div class="input-group-text">
                <span class="fa fa-calendar tx-16 lh-0 op-6"></span>
            </div>
            <input class="form-control hasDatepicker" id="<?= $id ?>"
                   name='<?= $name ?>' placeholder="DD/MM/YYYY"
                   value='<?= $value ?>'
                   type="text">
        </div>

        <?php
    }

    static function buttonRadioButtons(array $data, string $tenancies): void
    {

        $xerotenant_id = (array_key_exists('xerotenant_id', $data['Contract'])) ? $data['Contract']['xerotenant_id'] : '';
        $styles = $inputs = $labels = [];
        foreach (json_decode($tenancies, true) as $row):
            $checked = ($xerotenant_id === $row['tenant_id'] ? ' checked ' : '');
            $backgroundColor = "var(--bs-{$row['colour']}";
            $styles[] = "
                        .btn-check:checked + .btn.{$row['shortname']} {
                            background-color: $backgroundColor );
                            color: white;
                        }";
            $inputs[] = "<input type='radio' class='btn-check'
                           name='data[contract][xerotenant_id]'
                           id='enquiry-tenancy-{$row['shortname']}'
                           value='{$row['tenant_id']}'
                           autocomplete='off' $checked>
                         <label class='btn btn-outline-primary {$row['shortname']}'
                           for='enquiry-tenancy-{$row['shortname']}'>{$row['name']}</label>";

        endforeach;

        //class="btn-group"
        ?>

        <style>
            <?php echo implode(PHP_EOL, $styles) ?>
        </style>
        <div class="form-label">Region:</div>

        <div role="group" aria-label="Select the company">
            <?php
            echo implode(PHP_EOL, $inputs);
            ?>
        </div>

        <?php
    }
}
