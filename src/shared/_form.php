<?php if (!empty($errors)) { ?>
    <?php include "layouts/_errors.php" ?>
<?php } ?>
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Document Details</h3>
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control" id="title" placeholder="Enter title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES) ?>" autofocus>
            </div>
            <div class="form-group select-group">
                <label for="document_type">Document Type</label>
                <select id="document_type" class="form-control" name="document_type">
                    <option value="">-- Select Document Type --</option>
                    <?php foreach ($document_types as $type): ?>
                        <option value="<?= htmlspecialchars($type['id'], ENT_QUOTES) ?>"
                            <?= (($_POST['document_type'] ?? '') == $type['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['name'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="document_date">Document Date</label>
                <input type="date" class="form-control" id="document_date" placeholder="Enter date" name="document_date" value="<?= htmlspecialchars($_POST['document_date'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div class="form-group">
                <label for="document_number">Document Number</label>
                <input type="text" class="form-control" id="document_number" placeholder="Enter document number" name="document_number" value="<?= htmlspecialchars($_POST['document_number'] ?? '', ENT_QUOTES) ?>">
            </div>
            <div class="form-group">
                <label for="date_received">Date Received</label>
                <input type="date" class="form-control" id="date_received" placeholder="Enter date received" name="date_received" value="<?= htmlspecialchars($_POST['date_received'] ?? date('Y-m-d'), ENT_QUOTES) ?>">
            </div>
            <div class="form-group select-group">
                <label for="concerned_division">Concerned Division</label>
                <select id="concerned_division" class="form-control" name="concerned_division[]"  multiple="multiple">
                    <option value="">-- Select Concerned Division --</option>
                    <?php foreach ($divisions['result'] as $division): ?>
                        <option value="<?= htmlspecialchars($division['id'], ENT_QUOTES) ?>"
                            <?= in_array($division['id'], $_POST['concerned_division'] ?? []) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($division['name'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?> 
                </select>
            </div>
            <div class="form-group select-group">
                <label for="stakeholders">Names of Stakeholders</label>
                <select id="stakeholders" class="form-control" name="names_stakeholders[]"  multiple="multiple">
                    <option value="">-- Select Stakeholders --</option>
                    <?php foreach ($stakeholders['result'] as $stakeholder): ?>
                        <option value="<?= htmlspecialchars($stakeholder['id'], ENT_QUOTES) ?>"
                            <?= in_array($stakeholder['id'], $_POST['names_stakeholders'] ?? []) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($stakeholder['name'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?> 
                </select>
            </div>
            <div class="form-group select-group">
                <label for="receiving_office">Receiving Office</label>
                 <select id="receiving_office" class="form-control" name="receiving_office">
                    <option value="">-- Select Receiving Office --</option>
                    <?php foreach ($receiving_offices['result'] as $office): ?>
                        <option value="<?= htmlspecialchars($office['id'], ENT_QUOTES) ?>"
                            <?= ((string) ($_POST['receiving_office'] ?? '') === (string)$office['id'])
                                ? 'selected'
                                : '' ?>>
                            <?= htmlspecialchars($office['name'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group select-group">
                <label for="signatories">Signatories</label>
                 <select id="signatories" class="form-control" name="signatories[]"  multiple="multiple">
                    <option value="">-- Select Signatory --</option>
                    <?php if(isset($_POST['signatories'])) { ?>
                    <?php
                        // Keep track of printed values
                        $printed = [];
                        /* =========================
                        Print DB signatories first
                        ========================= */
                        foreach ($divisions['result'] as $division):
                            $id   = (string) $division['id'];
                            $name = $division['head'];
                            $isSelected = in_array($id, $_POST['signatories'], true);
                            // Mark as printed
                            $printed[] = $id;
                        ?>
                            <option value="<?= htmlspecialchars($id, ENT_QUOTES) ?>"
                                <?= $isSelected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($name, ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                        <?php
                        /* =========================
                        Print typed / custom values
                        ========================= */
                        foreach ($_POST['signatories'] as $val):
                            // Skip if already printed (DB id)
                            if (in_array((string)$val, $printed, true)) {
                                continue;
                            }
                            // Skip empty values
                            if (trim($val) === '') {
                                continue;
                            }
                        ?>
                            <option value="<?= htmlspecialchars($val, ENT_QUOTES) ?>" selected>
                                <?= htmlspecialchars($val, ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>                        
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <input type="file" name="file" />
                <?php if (!empty($document['file'])): ?>
                <!-- File Preview Link -->
                <div class="mt-3">
                    <label for="file">Document</label>
                    <p>
                        <a href="/view_file.php?id=<?= htmlspecialchars($document_id, ENT_QUOTES) ?>"
                            target="_blank"
                            class="btn btn-outline-primary">
                            <i class="fa-solid fa-file"></i> <?= htmlspecialchars($document['file_name'], ENT_QUOTES) ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select class="form-control" id="status" name="status">
                    <?php foreach ($config['options'] as $value => $label): ?>
                    <option value="<?= $value ?>"
                        <?= (($_POST['status'] ?? '') === $value) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group select-group">
                <label for="filing_location">Filing Location</label>
                <select id="filing_location" class="form-control" name="filing_location">
                    <option value="">-- Select Filing Location --</option>
                    <?php foreach ($filing_locations['result'] as $location): ?>
                        <option value="<?= htmlspecialchars($location['id'], ENT_QUOTES) ?>"
                            <?= (($_POST['filing_location'] ?? '') == $location['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($location['name'], ENT_QUOTES) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>            
            <button type="submit" name="submit" class="btn btn-primary"> <i class="fa-solid fa-floppy-disk"></i> Save</button>
        </form>
    </div>
</div>

