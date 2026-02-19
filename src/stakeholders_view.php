<?php
    include "models/stakeholder.php";
    include "session.php"; 
    include "require_login.php"; 
    include "require_role.php"; 

    //require_role($_SESSION['id'], ['super_admin', 'administrator', 'employee'], 'student registration');

    if (array_key_exists("id", $_GET)) {
      $stakeholder = view_stakeholder($_GET['id']);
    }

?>

<h3 class="d-none" id="page-title">
  Stakeholder Information
</h3>

<table class="table table-head-fixed text-nowrap">
  <tbody>
    <tr>
      <th class="table-light">Name</th>
      <td colspan="3"><?= htmlspecialchars($stakeholder['name'], ENT_QUOTES) ?></td>
    </tr>
    <tr>
      <th class="table-light">Last Updated</th>
      <td> <?= empty($stakeholder['last_updated']) ? 'N/A' : date('M d, Y g:i A', strtotime($stakeholder['last_updated'])) ?></td>
      <th class="table-light">Date Created</th>
      <td><?= date('M d, Y', strtotime($stakeholder['date_created'])) ?></td>
    </tr>
  </tbody>
</table>




