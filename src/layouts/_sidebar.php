<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="" class="brand-link">
      <img src="/assets/images/logo.png" />
      <span class="brand-text">DTS</span>
    </a>
    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item"></a>
            <a href="/dashboard/" class="nav-link">
              <i class="nav-icon fa-solid fa-gauge-high"></i>
              <p>
                Dashboard
              </p>
            </a>
          <li class="nav-header">Manage Documents</li>
          <li class="nav-item">
            <a href="/documents/incoming" class="nav-link">
              <i class="nav-icon fa-solid fa-file-arrow-down"></i>
              <p>
                Incoming
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="/documents/outgoing" class="nav-link">
              <i class="nav-icon fa-solid fa-paper-plane"></i>
              <p>
                Outgoing
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="/reports/" class="nav-link">
                <i class="nav-icon fa-solid fa-chart-line"></i>
              <p>
                Reports
              </p>
            </a>
          </li>
          <?php 
            $user_roles = get_user_roles($_SESSION['id'], 'names'); 
            if (count(array_intersect(['super_admin', 'administrator'], $user_roles)) > 0): 
          ?>
          <li class="nav-header">Manage Users</li>
          <li class="nav-item">
            <a href="/users" class="nav-link">
              <i class="nav-icon fa-solid fa-users-gear"></i>
              <p>Users</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-copy"></i>
              <p>
                System Settings
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview" style="display: none;">
              <li class="nav-item">
                <a href="/document_types/" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Document Types</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/divisions/" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Divisions</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/stakeholders/" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Stakeholders</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/receiving_offices/" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Receiving Office</p>
                </a>
              </li>              
              <li class="nav-item">
                <a href="/filing_locations/" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Filing Locations</p>
                </a>
              </li>
            </ul>
          </li>            
          <?php endif; ?>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>