<?php if (!empty($errors)) { ?>
    <?php include "layouts/_errors.php" ?>
<?php } ?>
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Details</h3>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" placeholder="Enter name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES) ?>" autofocus>
            </div>
            <div class="form-group">
                <label for="type">Type</label>
                <select class="form-control" id="type" name="type">
                    <option value="">-- Select Type --</option>
                    <option value="incoming" <?= (($_POST['type'] ?? '') == 'incoming') ? 'selected' : '' ?>>Incoming</option>
                    <option value="outgoing" <?= (($_POST['type'] ?? '') == 'outgoing') ? 'selected' : '' ?>>Outgoing</option>
                </select>
            </div>
            <div class="form-group">
                <label for="type">Retention period (In years)</label>
                <input type="text" class="form-control" id="type" placeholder="Enter type" name="retention_period" value="<?= htmlspecialchars($_POST['retention_period'] ?? '', ENT_QUOTES) ?>">
            </div>
            <button type="submit" name="submit" class="btn btn-primary"> <i class="fa-solid fa-floppy-disk"></i> Save</button>
        </form>
    </div>
</div>

