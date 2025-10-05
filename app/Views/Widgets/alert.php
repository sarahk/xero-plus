<?php
// Display and clear the notification on the next page

use App\Classes\StorageClass;

if (!isset($storage) || !$storage instanceof StorageClass) {
    $storage = new StorageClass(); // Instantiate if not set
}
$notification = $storage->getNotification();

if (!empty($_SESSION['notification'])) {
    ?>
    <div class="alert alert-<?= $notification['class'] ?? 'info'; ?> alert-dismissible fade show"
         role="alert"
         style="border-radius: 5px; font-size: 1rem; margin: 1rem;">
        <?php
        echo htmlspecialchars($_SESSION['notification']['message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span></button>
    </div>
    <?php
}
