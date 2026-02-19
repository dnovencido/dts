<!-- File Preview / Download -->
<div class="mt-3">
    <label for="fike" class="text-muted mr-2"><strong><i class="fa-solid fa-file"></i> Document: </strong></label>
    <?php
        $fileName = $document['file_name'] ?? '';
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $fileUrl = "/view_file.php?id=" . (int)$document_id;

        // File groups
        $imageTypes = ['jpg','jpeg','png','gif','webp'];
        $pdfTypes   = ['pdf'];
        $officeTypes = ['doc','docx','xls','xlsx','ppt','pptx'];

        // IMAGE → New tab
        if (in_array($ext, $imageTypes)):
    ?>

        <a href="<?= $fileUrl ?>"
        target="_blank"
        class="btn btn-outline-info">

        <i class="fa-solid fa-image"></i>
        <?= htmlspecialchars($fileName) ?>
        </a>

    <?php
        // PDF → Iframe
        elseif (in_array($ext, $pdfTypes)):
    ?>

        <iframe
        src="<?= $fileUrl ?>"
        width="100%"
        height="900"
        style="border:1px solid #ddd;">
        </iframe>

    <?php
        // OFFICE / OTHERS → Download
        else:
    ?>

        <a href="<?= $fileUrl ?>"
        download
        class="btn btn-outline-primary">

        <i class="fa-solid fa-file-arrow-down"></i>
        <?= htmlspecialchars($fileName) ?>
        </a>

    <?php endif; ?>
</div>