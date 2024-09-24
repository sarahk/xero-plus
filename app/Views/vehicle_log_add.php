<?php
$now = date('Y-m-d H:i:s');
?>
<form>

    <div class="form-group">
        <label for="vehicle_id">Vehicle</label>
        <select name='vehicle_id' class="form-control">
            <?php
            foreach ($vehicles as $row) {
                var_export($row);
                echo "<option value='{$row['id']}'>{$row['numberplate']}</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="date">Driver</label>
        <input type="text" class="form-control" id="date" name="date" disabled value="" disabled>
    </div>
    <div class="form-group">
        <label for="start_time">Start Time</label>
        <input type="text" class="form-control" id="start_time" name="start_time" disabled value="<?php echo $now; ?>">
    </div>
    <div class="form-group">
        <label for="start_kilometres">Starting Ks</label>
        <input type="number" class="form-control" id="start_kilometres" name="start_kilometres" required>
    </div>
    <div class="form-group">
        <label for="end_kilometres">Ending Ks</label>
        <input type="number" class="form-control" id="end_kilometres" name="end_kilometres" required>
    </div>
    <div class="form-group">
        <label for="used_for">Use Category</label>
        <select class="form-control" id="used_for" name="used_for">
            <option value='Business'>Business</option>
            <option value='Personal'>Personal</option>
        </select>
    </div>
    <div class="form-group">
        <label for="purpose">Notes</label>
        <textarea class="form-control" id="purpose" name="purpose" rows="3" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>

<script>

function changeMinMax() {
      // Change the min and max attributes using jQuery
      $("#myNumber").prop("min", 10);
      $("#myNumber").prop("max", 50);
    }


    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition, showError);
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }

    function showPosition(position) {
        var latitude = position.coords.latitude;
        var longitude = position.coords.longitude;
        var locationMessage = "Latitude: " + latitude + "<br>Longitude: " + longitude;
        document.getElementById("location").innerHTML = locationMessage;
    }

    function showError(error) {
        switch (error.code) {
            case error.PERMISSION_DENIED:
                alert("User denied the request for Geolocation.");
                break;
            case error.POSITION_UNAVAILABLE:
                alert("Location information is unavailable.");
                break;
            case error.TIMEOUT:
                alert("The request to get user location timed out.");
                break;
            case error.UNKNOWN_ERROR:
                alert("An unknown error occurred.");
                break;
        }
    }
</script>