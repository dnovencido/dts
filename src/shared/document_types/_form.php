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
                <input type="text" class="form-control" id="type" placeholder="Enter type" name="type" value="<?= htmlspecialchars($_POST['type'] ?? '', ENT_QUOTES) ?>">
            </div>
            <button type="submit" name="submit" class="btn btn-primary"> <i class="fa-solid fa-floppy-disk"></i> Save</button>
        </form>
    </div>
</div>

