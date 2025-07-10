
<!-- Coding by CodingNepal || www.codingnepalweb.com -->

<?php echo "SIDEBAR TEST"; ?>
<!-- navbar -->
<nav class="navbar">
  <div class="logo_item">
    <i class="bx bx-menu" id="sidebarOpen"></i>
    <img src="/cdms/img/logo.jpg" alt=""></i>CDMS
  </div>
  <div class="search_bar">
    <!--<input type="text" placeholder="Search" />-->
  </div>
  <div class="navbar_content">

    <i class='bx bx-sun' id="darkLight"></i>
    <i class='bx bx-bell' ></i>
    <img src="/cdms/img/user.jpg" alt="" class="profile" />
  </div>
</nav>
<!-- sidebar -->
<nav class="sidebar">
  <div class="menu_content">
    <ul class="menu_items">
      <div class="menu_title "></div>
      <!-- duplicate or remove this li tag if you want to add or remove navlink with submenu -->
      <!-- start -->
      <li class="item">
        <a href="/cdms/dashboard/dash.php" class="nav_link">
          <span class="navlink_icon">
            <i class="bx bx-grid-alt"></i>
          </span>
          <span class="navlink">Dashboard</span>
        </a>
      </li>
      <li class="item">
        <div href="#" class="nav_link submenu_item">
          <span class="navlink_icon">
            <i class="bx bx-book-alt"></i>
          </span>
          <span class="navlink">Reservation</span>
          <i class="bx bx-chevron-right arrow-left"></i>
        </div>
        <ul class="menu_items submenu">
          <a href="#" class="nav_link sublink"><i class="bx bx-calendar-alt"></i>Calendar</a>
          <a href="#" class="nav_link sublink"><i class='bx bx-calendar-event'></i></i>Reservation</a>
          <a href="#" class="nav_link sublink"><i class='bx bx-book-open'></i> Rules & Information</a>
          <a href="#" class="nav_link sublink"><i class='bx bx-history'></i> Reservation History</a>
        </ul>
      </li>
      <!-- end -->
      <!-- duplicate this li tag if you want to add or remove  navlink with submenu -->
      <!-- start -->
      <li class="item">
        <div href="#" class="nav_link submenu_item">
          <span class="navlink_icon">
            <i class='bx bx-user-check'></i> 
          </span>
          <span class="navlink">Accounts</span>
          <i class="bx bx-chevron-right arrow-left"></i>
        </div>
        <ul class="menu_items submenu">
          <a href="#" class="nav_link sublink"><i class='bx bx-user-circle'></i> Staff Accounts</a>
          <a href="#" class="nav_link sublink"><i class='bx bx-user'></i> Guest Accounts</a>
          <a href="#" class="nav_link sublink"><i class='bx bx-user-plus'></i>Create Account</a>
        </ul>
      </li>
      <!-- end -->
    </ul>
    <ul class="menu_items">
      <div class="menu_title "><hr></div>
      <li class="item">
        <div href="#" class="nav_link submenu_item">
          <span class="navlink_icon">
            <i class='bx bx-bar-chart'></i>  
          </span>
          <span class="navlink">Audit & Reports</span>
          <i class="bx bx-chevron-right arrow-left"></i>
        </div>
        <ul class="menu_items submenu">
          <a href="#" class="nav_link sublink"><i class='bx bx-pie-chart'></i> Bills & Expenses</a>
          <a href="#" class="nav_link sublink"><i class='bx bx-file'></i>Audit logs</a>
          <a href="#" class="nav_link sublink"><i class='bx bx-wallet'></i>Income Reports</a>
        </ul>
      </li>
      <!-- end -->
    </ul>

    <ul class="menu_items">
      <!-- Start -->
      <li class="item">
        <a href="/cdms/inventory_cdms/inv_index.php" class="nav_link">
          <span class="navlink_icon">
            <i class='bx bx-box'></i>  
          </span>
          <span class="navlink">Inventory</span>
        </a>
      </li>
      <!-- End -->
      <li class="item">
        <a href="/cdms/employees_cdms/employee_index.php" class="nav_link">
          <span class="navlink_icon">
            <i class='bx bx-group'></i> 
          </span>
          <span class="navlink">Manage Employees</span>
        </a>
      </li>
    </ul>
    <ul class="menu_items">
      
      
      <li class="item">
        <a href="#" class="nav_link">
          <span class="navlink_icon">
            <i class="bx bx-cog"></i>
          </span>
          <span class="navlink">Settings</span>
        </a>
      </li>

    </ul>
    <!-- Sidebar Open / Close -->
    <div class="bottom_content">
      <div class="bottom expand_sidebar">
        <span> Expand</span>
        <i class='bx bx-log-in' ></i>
      </div>
      <div class="bottom collapse_sidebar">
        <span> Collapse</span>
        <i class='bx bx-log-out'></i>
      </div>
    </div>
  </div>
</nav>
