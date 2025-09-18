<?php 
session_start();
include('assets/inc/header.php');

if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}
 ?>
<h3>Students Overview</h3>

<?php
//search bar
include 'db_connect.php';

$search = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = $conn->real_escape_string($_GET['search']);
    $query = "SELECT * FROM jss2_students_records WHERE name LIKE '%$search%' ORDER BY id ASC";
} else {
    $query = "SELECT * FROM jss2_students_records ORDER BY id ASC";
}

$result = $conn->query($query);

if ($result->num_rows == 0) {
    echo "<p class='text-danger'>No record found for '<strong>" . htmlspecialchars($search) . "</strong>'</p>";
}
?>
<form action="" method="get">
<div class="container input-group mt-4">
    <span class="input-group-text">Search Students</span>
    <input type="text" name="search" id="" class="form-control" placeholder="Search By Name....">
    <button type="submit" class="btn btn-primary">Search</button>
</div>
</form>
<div class="container-fluid mt-5">
  <div class="card mb-4 shadow-sm mt-4">
    <div class="card-header bg-primary text-white">
      <strong>Student Records</strong>
    </div>
    <div class="card-body">
      <table class="table table-bordered table-striped mb-0" id="my-table">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Profile</th>
            <th>Student ID</th>
            <th>Date of Birth</th>
            <th>Age</th>
            <th>Parent Name</th>
            <th>Parent Number</th>
            <th>Address</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
        
    if ($result->num_rows > 0) {
        $i = 1;
        while ($row = $result->fetch_assoc()) {
        $dob = new DateTime($row['date_of_birth']); //format dob 
        $today = new DateTime();
         // Calculate age
        $age = $today->diff($dob)->y;
        echo "
       <tr>
        <td>{$i}</td>
        <td>{$row['name']}</td>
        <td> <img src='" . (!empty($row['profile_picture']) ? htmlspecialchars($row['profile_picture']) : "./assets/images/user/avatar-2.png") . "' alt='Profile Picture' width='50' height='50' class='rounded-circle'></td>
        <td>{$row['student_id']}</td>
        <td>{$dob->format('d-m-Y')}</td>
        <td>{$age}</td>
        <td>{$row['parent_name']}</td>
        <td>{$row['mobile_number']}</td>
        <td>{$row['address']}</td>
        <td>
          <a href='view_student.php?id={$row['student_id']}' class='btn btn-sm btn-outline-primary'>View</a>
          <a href='edit_student.php?id={$row['student_id']}' class='btn btn-sm btn-outline-warning'>Edit</a>
        </td>
      </tr>";
      $i++;
        }
    } else {
        echo "<tr><td colspan='8'>No records found.</td></tr>";
    }
    ?> 
    </tbody>
      </table>
          <button class='btn btn-success mt-4 mb-4' onclick="exportToExcel('my-table', 'Students Overview')">Export to Excel</button>
    </div>
  </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script>
  function exportToExcel(tableID, filename = '') {
  const table = document.getElementById(tableID);
  if (!table) {
    console.error(`Table with ID '${tableID}' not found.`);
    return;
  }

  // Convert the HTML table to a workbook object
  const workbook = XLSX.utils.table_to_book(table);

  // Write the workbook to an XLSX file and trigger the download
  XLSX.writeFile(workbook, `${filename}.xlsx`);
}
</script>

<?php include('assets/inc/footer.php'); ?>
