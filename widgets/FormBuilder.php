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
    static function inputs($label, $fields, $required = false)
    {
        ?>
        <label class='form-label' for='<?= $fields[0]['name']; ?>'><?= $label; ?></label>
         <div class="form-row">
            <?php foreach($fields as $row): ?>
                <div class='form-group col-md-6' >
                    <div class="form-group">
                        <input class="form-control" id='<?= $row['name']; ?>'
                            name='data[<?= $row['name']; ?>]'
                            placeholder="<?= $row['name']; ?>"
                            type="<?= $row['type']; ?>"
                            value="<?= $row['value']??''; ?>"
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

    static function radio($name, $label, $choices, $value='')
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
                        <span class="custom-control-label"><?= $row['label']; ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}