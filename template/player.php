<?php foreach (($queue ?? []) as $track): ?>
    <?php require __DIR__ . '/playlistItem.php' ?>
<?php endforeach; ?>
