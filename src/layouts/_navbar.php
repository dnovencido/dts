<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item">
        <form id="search_document" action="/search-result" method="get">
          <div class="input-group">
            <input id="search-input" class="form-control" type="search" name="query" placeholder="Search title or document number" aria-label="Search" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES) : '' ?>" autocomplete="off">
            <div class="input-group-append">
              <button type="submit" class="btn btn-outline-secondary">
                <i class="fas fa-search fa-fw"></i>
              </button>
            </div>
          </div>
        </form>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <div class="dropdown">
          <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa-regular fa-circle-user"></i> <?= $_SESSION['fname'] ?>
          </a>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
            <a class="dropdown-item" href="/logout.php?logout=true">Logout</a>
          </div>
      </div>
      </li>
    </ul>
</nav>
<!-- /.navbar -->