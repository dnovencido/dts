<hr>
<p class="text-muted mr-2"><strong><i class="fa-solid fa-clock-rotate-left"></i> Document Status: </strong></p>
<div class="horizontal-timeline">
    <?php foreach($logs as $log): ?>
    <?php
        $color = "primary";

        if($log['status'] == "pending") $color = "warning";
        if($log['status'] == "received") $color = "success";
        if($log['status'] == "archived") $color = "secondary";
    ?>
    <div class="timeline-step">
        <div class="timeline-icon bg-<?= $color ?>">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="timeline-content">
            <?php if($log['status']): ?>
            <div class="timeline-title badge badge-info">
            <?= htmlspecialchars(ucfirst(strtolower($log['status']))) ?>
            </div>
            <?php endif; ?>
            <div class="timeline-meta">
                <?= htmlspecialchars($log['fname'].' '.$log['lname']) ?>
            </div>
            <div class="timeline-date">
                <?= date("M d, Y h:i A", strtotime($log['created_at'])) ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>   