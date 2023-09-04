<?php

class FormBuilder
{
    static function input($name, $label, $required = false, $type = 'text', $value = '')
    {
        ?>
        <div class='form-group'>
            <label class='form-label' for='<?= $name; ?>'><?= $label; ?></label>
            <input class="form-control" id='<?= $name; ?>'
                   name='data[<?= $name; ?>]' placeholder="<?= $label; ?>"
                   type="<?= $type; ?>" value="<?= $value; ?>" <?= ($required ? 'required' : ''); ?>>
        </div>
        <?php
    }

    /* G E T   T I M E S
       The range of times we can deliver
    */
    static function getTimes()
    {
        return [
            ['value' => '9:00'],
            ['value' => '9:30'],
            ['value' => '10.00'],
            ['value' => '10:30'],
            ['value' => '11:00'],
            ['value' => '11:30'],
            ['value' => '12:00'],
            ['value' => '12:30'],
            ['value' => '13:00', 'label' => '1:00'],
            ['value' => '13:30', 'label' => '1:30'],
            ['value' => '14:00', 'label' => '2:00'],
            ['value' => '14:30', 'label' => '2:30']
        ];
    }

    static function getStatusOptions()
    {
        return [
            ['value' => 'New'],
            ['value' => 'Maybe'],
            ['value' => 'Yes'],
            ['value' => 'No'],
            ['value' => 'Call', 'label' => 'Call Back']
        ];
    }

    static function hidden($name, $value = '')
    {
        ?>
        <input type="hidden" id="<?=$name; ?>" name="data[<?= $name; ?>]" value="<?= $value; ?>">
        <?php
    }

    static function inputs($label, $fields, $required = false)
    {
        ?>
        <label class='form-label' for='<?= $fields[0]['name']; ?>'><?= $label; ?></label>
        <div class="form-row">
            <?php foreach ($fields as $row): ?>
                <div class='form-group col-md-6'>
                    <div class="form-group">
                        <input class="form-control" id='<?= $row['name']; ?>'
                               name='data[<?= $row['name']; ?>]'
                               placeholder="<?= $row['name']; ?>"
                               type="<?= $row['type']; ?>"
                               value="<?= $row['value'] ?? ''; ?>"
                            <?= ($required ? 'required' : ''); ?> >
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    static function textarea($name, $label, $value = '')
    {
        ?>
        <div class="form-group">
            <label class="form-label" for="<?= $name; ?>"><?= $label; ?></label>
            <textarea class="form-control" id='<?= $name; ?>' name='data[<?= $name; ?>]'
                      placeholder="<?= $label; ?>" rows="3"
                      spellcheck="false"><?= $value; ?></textarea>
        </div>

        <?php
    }

    static function radio($name, $label, $choices, $value = '')
    {
        ?>
        <div class="form-group ">
            <div class="form-label"><?= $label; ?></div>
            <div class="custom-controls-stacked">
                <?php foreach ($choices as $row): ?>
                    <label class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" name="data[<?= $name; ?>]"
                               value="<?= $row['name']; ?>"
                            <?= ($row['name'] == $value ? 'checked=""' : ''); ?>>
                        <span class="custom-control-label"><?= (array_key_exists('label', $row) ? $row['label'] : $row['name']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    static function select($name, $label, $choices, $value = '')
    {
        ?>
        <div class='form-group'>
            <label class="form-label" for='status'><?= $label; ?></label>
            <select class="form-control" id='<?= $name; ?>' name='data[<?= $name; ?>]'
                    data-bs-placeholder="Choose One" tabindex="-1">
                <option label="Choose one"></option>
                <?php foreach ($choices as $row): ?>
                    <option value="<?= $row['value']; ?>" <?= ($row['value'] == $value ? 'selected' : ''); ?>>
                        <?= (array_key_exists('label', $row) ? $row['label'] : $row['value']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

        </div>
        <?php
    }
}