<?php
include('assets/inc/header.php');
// $data->pageContent($_SESSION['role']);


?>


<link rel="stylesheet" href="assets/css/index.css">
<link rel="stylesheet" href="index.css">
<h3>Dashboard</h3>
<span class="text-muted"> <?php include('name.php'); ?></span>
<span><p class="text-end">Date: <?= date('l, F j, Y') ?></p></span>
<div class="dashboard row mt-4">
      <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="card bg-comman w-100">
                  <div class="card-body">
                        <div class="db-widgets d-flex justify-content-between align-items-center">
                              <div class="db-info">
                                    <h6>Students</h6>
                                    <h3><?php include('card.php'); echo $total_students; ?></h3>
                              </div>
                              <div class="db-icon">
                                    <img src="assets/img/icons/dash-icon-01.svg" alt="Dashboard Icon">
                              </div>
                        </div>
                  </div>
            </div>
      </div>
      <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="card bg-comman w-100">
                  <div class="card-body">
                        <div class="db-widgets d-flex justify-content-between align-items-center">
                              <div class="db-info">
                                    <h6>Staff Number</h6>
                                    <h3><?php echo $_SESSION['staff_id']?></h3>
                              </div>
                              <div class="db-icon">
                                    <img src="assets/img/icons/dash-icon-02.svg" alt="Dashboard Icon">
                              </div>
                        </div>
                  </div>
            </div>
      </div>
      <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="card bg-comman w-100">
                  <div class="card-body">
                        <div class="db-widgets d-flex justify-content-between align-items-center">
                              <div class="db-info">
                                    <h6>Class</h6>
                                    <h3>Jss2</h3>
                              </div>
                              <div class="db-icon">
                                    <img src="assets/img/icons/dash-icon-03.svg" alt="Dashboard Icon">
                              </div>
                        </div>
                  </div>
            </div>
      </div>
      <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="card bg-comman w-100">
                  <div class="card-body">
                        <div class="db-widgets d-flex justify-content-between align-items-center">
                              <div class="db-info">
                                    <h6>Subject</h6>
                                    <h3>English</h3>
                              </div>
                              <div class="db-icon">
                                    <img src="assets/img/icons/award-icon-01.svg" alt="Dashboard Icon">
                              </div>
                        </div>
                  </div>
            </div>
      </div>
</div>

<h3>Quick Links</h3>

    <br>
  <div class="quick-links dashboard">
    <a href="todays_classes.php">
      <div class="card blue">

      <h2>View Todays Classes</h2>

      <div class="number"></div>

      <div class="desc text-white text-center">View Today's Classes</div>

    </div>
    </a>
    <a href="view_attendance.php">
      <div class="card blue">

      <h2>View Attendance</h2>

      <div class="number"></div>

      <div class="desc text-white text-center">View Students Attendance</div>

    </div>
    </a>
    <a href="view_results.php">
      <div class="card blue">

      <h2>View Students Results</h2>

      <div class="number"></div>

      <div class="desc text-white text-center">View Results</div>

    </div>
    </a>
    <a href="messages.php">
      <div class="card blue">

      <h2>Messages</h2>

      <div class="number"></div>

      <div class="desc text-white text-center">View Your Messages</div>

    </div>
    </a>
  </div>
<?php

include('assets/inc/footer.php');
?>