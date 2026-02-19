<?php if (isset($_SESSION['flash_message'])): ?>
    <?php $msg = $_SESSION['flash_message']; ?>
    <div class="alert alert-<?= $msg['type'] ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <?= $msg['text'] ?>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>
