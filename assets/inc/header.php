<?php 
// require('../libs/config/Dbase.php');
      // $data = new Config;
      // $department = $_SESSION['department'];

 session_start();
if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}
?>




<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
  <head>
    <title>Dashboard | <?php echo $_SESSION['staff_id']; ?> Delsu Staff School</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0,minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <link rel="icon" href="<?php echo 'assets/images/delsu.png'  ?>" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/plugins/jsvectormap.min.css">
    <link href="css2?family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/fonts/phosphor/duotone/style.css">
    <link rel="stylesheet" href="assets/fonts/tabler-icons.min.css">
    <link rel="stylesheet" href="assets/fonts/feather.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome.css">
    <link rel="stylesheet" href="assets/fonts/material.css">
    <link rel="stylesheet" href="assets/css/style.css" id="main-style-link">
  </head>
  <body>
    <!-- [ Pre-loader ] start -->
    <!-- <div class="loader-bg fixed inset-0 bg-white dark:bg-themedark-cardbg z-[1034]">
      <div class="loader-track h-[5px] w-full inline-block absolute overflow-hidden top-0">
        <div class="loader-fill w-[300px] h-[5px] bg-primary-500 absolute top-0 left-0 animate-[hitZak_0.6s_ease-in-out_infinite_alternate]"></div>
      </div>
    </div> -->
    <!-- [ Pre-loader ] End -->
    <!-- [ Sidebar Menu ] start -->
    <nav class="pc-sidebar">
      <div class="navbar-wrapper">
        <div class="m-header flex items-center py-4 px-6 h-header-height">
          <a href="index.php" class="b-brand flex items-center gap-3">
            <!-- ========   Change your logo from here   ============ -->
            <div style="width: 90px; height: auto; padding-top: 15px;">
  <img src="assets/images/delsu.png" alt="DELSU Logo" style="max-width: 100%;">
</div>

          </a>
        </div>
        <div class="navbar-content h-[calc(100vh_-_74px)] py-2.5">
          <ul class="pc-navbar">
            <li class="pc-item pc-caption">
              <label data-i18n="Navigation Menu">Navigation Menu</label>
            </li>
            <!-- <li class="pc-item pc-hasmenu">
              <a href="#!" class="pc-link">
                <span class="pc-micon">
                  <i data-feather="home"></i>
                </span>
                <span class="pc-mtext" data-i18n="Dashboard">Dashboard</span>
                <span class="pc-arrow">
                  <i class="ti ti-chevron-right"></i>
                </span>
                <span class="pc-badge">6</span>
              </a>
              <ul class="pc-submenu">
                <li class="pc-item">
                  <a class="pc-link" href="index.html" data-i18n="Default">Default</a>
                </li>
              </ul>
            </li> -->
            <li class="pc-item">
              <a href="index.php" class="pc-link">
                <span class="pc-micon">
                  <i data-feather="grid"></i>
                </span>
                <span class="pc-mtext" data-i18n="Dashboard">Dashboard</span>
              </a>
            </li>
            <li class="pc-item">
              <a href="calendar.php" class="pc-link">
                <span class="pc-micon">
                  <i data-feather="calendar"></i>
                </span>
                <span class="pc-mtext" data-i18n="Calendar">Calendar</span>
              </a>
            </li>
            <li class="pc-item">
              <a href="timetable.php" class="pc-link">
                <span class="pc-micon">
                  <i data-feather="table"></i>
                </span>
                <span class="pc-mtext" data-i18n="TimeTable">TimeTable</span>
              </a>
            </li>
            <li class="pc-item pc-hasmenu">
              <a href="#!" class="pc-link">
                <span class="pc-micon">
                  <i data-feather="users"></i>
                </span>
                <span class="pc-mtext" data-i18n="Students">Students</span>
              </a>
              <ul class="pc-submenu">
                <li>
                  <a class="pc-link" href="students_overview.php">Students Overview</a>
                </li>
                <li>
                  <a class="pc-link" href="add_students.php">Add Students</a>
                </li>
              </ul>
            </li>
            <li class="pc-item pc-hasmenu">
              <a href="#!" class="pc-link">
                <span class="pc-micon">
                  <i data-feather="book-open"></i>
                </span>
                <span class="pc-mtext" data-i18n="Subjects">Subjects</span>
              </a>
              <ul class="pc-submenu">
                <li>
                  <a class="pc-link" href="subjects_overview.php">Subjects Overview</a>
                </li>
                <li>
                  <a class="pc-link" href="add_subjects.php">Add Subjects</a>
                </li>
              </ul>
            </li>
            <li class="pc-item pc-hasmenu">
              <a href="#!" class="pc-link">
                <span class="pc-micon">
                  <i data-feather="check-circle"></i>
                </span>
                <span class="pc-mtext" data-i18n="Attendance">Attendance</span>
              </a>
              <ul class="pc-submenu">
                <li>
                  <a class="pc-link" href="take_attendance.php">Take Attendance</a>
                </li>
                <li>
                  <a class="pc-link" href="view_attendance.php">View Attendance</a>
                </li>

                <li>
                  <a class="pc-link" href="update_attendance.php">Update Attendance</a>
                </li>
                <li>
                  <a class="pc-link" href="attendance_report.php">View Report</a>
                </li>
              </ul>
            </li>
            <li class="pc-item pc-hasmenu">
              <a href="#!" class="pc-link">
                <span class="pc-micon">
                  <i data-feather="file-text"></i>
                </span>
                <span class="pc-mtext" data-i18n="Results">Results</span>
              </a>
              <ul class="pc-submenu">
                <li>
                  <a class="pc-link" href="add_results.php">Download Results Template</a>
                </li>

                <li>
                  <a class="pc-link" href="upload_results.php">Upload Results</a>
                </li>
                <li>
                  <a class="pc-link" href="view_results.php">View Results</a>
                </li>
                <li>
                  <a class="pc-link" href="broad_sheet.php">View Broad Sheet</a>
                </li>
              </ul>
            </li>
             <li class="pc-item">
              <a href="messages.php" class="pc-link">
                <span class="pc-micon">
                  <i data-feather="mail"></i>
                </span>
                <span class="pc-mtext" data-i18n="Messages">Messages</span>
              </a>
              
            </li>
             <li class="pc-item pc-hasmenu">
              <a href="#!" class="pc-link">
                <span class="pc-micon">
                  <i data-feather="book"></i>
                </span>
                <span class="pc-mtext" data-i18n="Resources">Resources</span>
              </a>
              <ul class="pc-submenu">
                <li>
                  <a class="pc-link" href="resources.php">Add Resources</a>
                </li>
                <li>
                  <a class="pc-link" href="view_resources.php">View Resources</a>
                </li>
              </ul>
            </li>
             
            <!-- <li class="pc-item">
              <a href="medical_form.php" class="pc-link">
                <span class="pc-micon">
                  <i data-feather="file-plus"></i>
                </span>
                <span class="pc-mtext" data-i18n="My Medical Form">My Medical Form</span>
              </a>
            </li> -->
            <li class="pc-item">
              <a href="profile.php" class="pc-link">
                <span class="pc-micon">
                  <i data-feather="user"></i>
                </span>
                <span class="pc-mtext" data-i18n="My Profile">My Profile</span>
              </a>
            </li>
            <li class="pc-item">
              <a href="logout.php" class="pc-link">
                <span class="pc-micon">
                  <i data-feather="log-out"></i>
                </span>
                <span class="pc-mtext" data-i18n="Logout">Logout</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- [ Sidebar Menu ] end -->
    <!-- [ Header Topbar ] start -->
    <header class="pc-header">
      <div class="header-wrapper flex max-sm:px-[15px] px-[25px] grow">
        <!-- [Mobile Media Block] start -->
        <div class="me-auto pc-mob-drp">
          <ul class="inline-flex *:min-h-header-height *:inline-flex *:items-center">
            <!-- ======= Menu collapse Icon ===== -->
            <li class="pc-h-item pc-sidebar-collapse max-lg:hidden lg:inline-flex">
              <a href="#" class="pc-head-link ltr:!ml-0 rtl:!mr-0" id="sidebar-hide">
                <i data-feather="menu"></i>
              </a>
            </li>
            <li class="pc-h-item pc-sidebar-popup lg:hidden">
              <a href="#" class="pc-head-link ltr:!ml-0 rtl:!mr-0" id="mobile-collapse">
                <i data-feather="menu"></i>
              </a>
            </li>
            <!-- <li class="dropdown pc-h-item">
              <a class="pc-head-link dropdown-toggle me-0" data-pc-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <i data-feather="search"></i>
              </a>
              <div class="dropdown-menu pc-h-dropdown drp-search">
                <form class="px-2 py-1">
                  <input type="search" class="form-control !border-0 !shadow-none" placeholder="Search here. . .">
                </form>
              </div>
            </li> -->
          </ul>
        </div>
        <!-- [Mobile Media Block end] -->
        <div class="ms-auto">
          <ul class="inline-flex *:min-h-header-height *:inline-flex *:items-center">
            <li class="dropdown pc-h-item">
              <a class="pc-head-link dropdown-toggle me-0" data-pc-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <i data-feather="settings"></i>
              </a>
             <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
              <a href="logout.php" class="dropdown-item">
                <i data-feather="power"></i>
                <span>Logout</span>
              </a>
            </div>
            </li>
            <li class="dropdown pc-h-item">
              <a class="pc-head-link dropdown-toggle me-0" data-pc-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <i data-feather="bell"></i>
                <span class="badge bg-success-500 text-white rounded-full z-10 absolute right-0 top-0">1</span>
              </a>
              <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown p-2">
                <div class="dropdown-header flex items-center justify-between py-4 px-5">
                  <h5 class="m-0">Notifications</h5>
                  <!-- <a href="#!" class="btn btn-link btn-sm">Mark all read</a> -->
                </div>
                <div class="dropdown-body header-notification-scroll relative py-4 px-5" style="max-height: calc(100vh - 215px)">
                  <p class="text-span mb-3">Today</p>
                  <div class="card mb-2">
                    <div class="card-body">
                      <div class="flex gap-4">
                        <div class="shrink-0">
                          <img class="img-radius w-12 h-12 rounded-0" src="assets/images/user/avatar-2.jpg" alt="Generic placeholder image">
                        </div>
                        <div class="grow">
                          <span class="float-end text-sm text-muted">2 min ago</span>
                          <h5 class="text-body mb-2">Doctor Ovie</h5>
                          <p class="mb-0">Kindly send the Lab Test Results</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="text-center py-2">
                  <a href="#!" class="text-danger-500 hover:text-danger-600 focus:text-danger-600 active:text-danger-600">Clear all Notifications</a>
                </div>
              </div>
            </li>
            <li class="dropdown pc-h-item header-user-profile">
              <a class="pc-head-link dropdown-toggle arrow-none me-0" data-pc-toggle="dropdown" href="#" role="button" aria-haspopup="false" data-pc-auto-close="outside" aria-expanded="false">
                <i data-feather="user"></i>
              </a>
              <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown p-2 overflow-hidden">
                <div class="dropdown-header flex items-center justify-between py-4 px-5 bg-primary-500">
                  <div class="flex mb-1 items-center">
                    <div class="shrink-0">
                      <!-- <img src="assets/images/user/avatar-2.jpg" alt="user-image" class="w-100 rounded-full"> -->
                       <?php include ('inc.php');?>
                      <img src="<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : './assets/images/user/avatar-2.jpg' ?>" alt="Profile Picture" class="w-100 rounded-full" id="profilePic" width="100" height="100">

                    </div>
                    <div class="grow ms-3">
                      <h6 class="mb-1 text-white"><?php echo $_SESSION['staff_id']; ?></h6>
                      <span class="text-white">
                        <h6 class="mb-1 text-white">Hospital No: 4582</h6>
                        <h6 class="mb-1 text-white">Campus: Abraka Campus 2</h6>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="dropdown-body py-4 px-5">
                  <div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 225px)">
                    <a href="profile.php#settings" class="dropdown-item">
                      <span>
                        <svg class="pc-icon text-muted me-2 inline-block">
                          <use xlink:href="#custom-setting-outline"></use>
                        </svg>
                        <span>Settings</span>
                      </span>
                    </a>
                    <a href="profile.php#change_password" class="dropdown-item">
                      <span>
                        <svg class="pc-icon text-muted me-2 inline-block">
                          <use xlink:href="#custom-lock-outline"></use>
                        </svg>
                        <span>Change Password</span>
                      </span>
                    </a>
                    <div class="grid my-3">
                      <a href="logout.php">
                      <button class="btn btn-primary flex items-center justify-center">
                        <svg class="pc-icon me-2 w-[22px] h-[22px]">
                          <use xlink:href="#custom-logout-1-outline"></use>
                        </svg>Logout</button>
                        </a> 
                    </div>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </header>
    <!-- [ Header ] end -->
    <!-- [ Main Content ] start -->
    <div class="pc-container">
      <div class="pc-content">