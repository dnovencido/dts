<?php
    include "models/audit.php";
    include "session.php"; 
    include "require_login.php"; 
    include "require_role.php"; 

    require_role($_SESSION['id'], ['super_admin', 'administrator'], 'audit trail management');

    if (array_key_exists("id", $_GET)) {
      $audit_trail = view_audit_trail($_GET['id']);
    }

?>

<h3 class="d-none" id="page-title">
  Audit Trail Information
</h3>
<div class="table-responsive">
  <table class="table table-head-fixed text-nowrap">
    <tbody>
      <tr>
        <th class="table-light">Name</th>
        <td><?= htmlspecialchars($audit_trail['name'], ENT_QUOTES) ?></td>
      </tr>
      <tr>
        <th class="table-light">Action</th>
        <td><?= htmlspecialchars($audit_trail['action'], ENT_QUOTES) ?></td>
      </tr>
      <tr>
        <th class="table-light">Record ID</th>
        <td><?= htmlspecialchars($audit_trail['record_id'], ENT_QUOTES) ?></td>
      </tr>
      <tr>
        <th class="table-light">Table</th>
        <td> <?= empty($audit_trail['table_name']) ? 'N/A' : htmlspecialchars($audit_trail['table_name'], ENT_QUOTES) ?></td>
      </tr>
      <tr>
        <th class="table-light">IP Address</th>
        <td><?= htmlspecialchars($audit_trail['ip_address'], ENT_QUOTES) ?></td>
      </tr>
      <tr>
         <th class="table-light">User Agent</th>
        <td> <?= empty($audit_trail['user_agent']) ? 'N/A' : htmlspecialchars($audit_trail['user_agent'], ENT_QUOTES) ?></td>
      </tr>
      <tr>
        <th class="table-light">Date</th>
        <td><?= date('M d, Y @ h:i a', strtotime($audit_trail['created_at'])) ?></td>
      </tr>
    </tbody>
  </table>
</div>

