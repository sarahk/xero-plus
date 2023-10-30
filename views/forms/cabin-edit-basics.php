<div class="form-group">
    <label for="cabinnumber">Number</label>
    <input type="text" class="form-control" id="cabinnumber" aria-describedby="cabinnumberHelp"
           placeholder="Enter the new cabin's number" value="<?= $data['cabins']['cabinnumber']; ?>">
</div>
<div class="form-group">
    <label for="cabinstyle">Style</label>
    <select id='cabinstyle' name='cabinstyle' class="form-control">
        <?php foreach (lists::getCabinStyles() as $row) {
            echo "<option value='{$row['name']}'>{$row['label']}</option>";
        } ?>
    </select>
</div>
<div class="form-group">
    <label for="cabinstatus">Status</label>
    <select id='cabinstatus' name='cabinstatus' class="form-control">
        <?php foreach (lists::getCabinStatuses() as $row) {
            echo "<option value='{$row['name']}'>{$row['label']}</option>";
        } ?>
    </select>
</div>
<div class="form-group">
    <label for="xerotenant_id">Operator</label>
    <select id='xerotenant_id' name='xerotenant_id' class="form-control">
        <?php foreach (json_decode(TENANCIES, true) as $row) {
            echo "<option value='{$row['tenant_id']}'>{$row['name']}</option>";
        } ?>
    </select>
</div>

<div class="form-group">
    <label for="xerotenant_id">Owner (when not the operator)</label>
    <select id='xerotenant_id' name='xerotenant_id' class="form-control">
        <?php foreach (lists::getOwners() as $k => $v) {
            echo "<option value='{$k}'>{$v}</option>";
        } ?>
    </select>
</div>

<div class="form-group">
    <label for="lastupdated">Last Updated</label>
    <input type="text" class="form-control" id="lastupdated" disabled="disabled">
</div>
