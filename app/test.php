<?php /* simplebar-autoinit.php */ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SimpleBar Auto-Init Demo</title>

    <!-- SimpleBar CSS (must be present) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.css">

    <!-- (Optional) Bootstrap 4 just for styling -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/css/bootstrap.min.css">

    <style>
        /* Your scroll container must have a constrained height */
        #sidebar-scroll {
            max-height: 300px; /* force scroll for demo */
            border: 1px solid #ddd;
            background: #fff;
        }
    </style>
</head>
<body class="p-4">
<h1 class="h3 mb-3">SimpleBar Auto-Init</h1>

<!-- Add data-simplebar to auto-initialize -->
<div id="sidebar-scroll" data-simplebar data-simplebar-auto-hide="true" class="p-3">
    <?php for ($i = 1; $i <= 50; $i++): ?>
        <p class="mb-2">Item <?= $i ?></p>
    <?php endfor; ?>
</div>

<!-- SimpleBar JS (after the container is in the DOM) -->
<script src="https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.js"></script>
</body>
</html>
