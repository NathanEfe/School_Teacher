<?php 
session_start();
if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}

include('assets/inc/header.php');
?>
<h3>Students Overview</h3>
<link rel="stylesheet" href="pagination.css">
<?php
include 'db_connect.php';

// Pagination setup
$limit = 10; // records per page
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Search handling
$search = '';
$where = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = $conn->real_escape_string($_GET['search']);
    $where  = "WHERE name LIKE '%$search%'";
}

// Count total records (for pagination)
$countQuery  = "SELECT COUNT(*) AS total FROM jss2_students_records $where";
$countResult = $conn->query($countQuery);
$totalRows   = $countResult->fetch_assoc()['total'];
$totalPages  = ceil($totalRows / $limit);

// Main query with LIMIT & OFFSET
$query = "SELECT * FROM jss2_students_records $where ORDER BY id ASC LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

if ($result->num_rows == 0 && !empty($search)) {
    echo "<p class='text-danger'>No record found for '<strong>" . htmlspecialchars($search) . "</strong>'</p>";
}
?>

<!-- Search Form -->
<form action="" method="get">
<div class="container input-group mt-4">
    <span class="input-group-text">Search Students</span>
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search By Name....">
    <button type="submit" class="btn btn-primary">Search</button>
</div>
</form>

<div class="container-fluid mt-5">
  <div class="card mb-4 shadow-sm mt-4">
    <div class="card-header bg-primary text-white">
      <strong>Student Records</strong>
    </div>
    <div class="table-responsive">
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
              $i = $offset + 1;
              while ($row = $result->fetch_assoc()) {
                  $dob = new DateTime($row['date_of_birth']); 
                  $today = new DateTime();
                  $age = $today->diff($dob)->y;

                  echo "
                  <tr>
                    <td>{$i}</td>
                    <td>{$row['name']}</td>
                    <td><img src='" . (!empty($row['profile_picture']) ? htmlspecialchars($row['profile_picture']) : "./assets/images/user/avatar-2.png") . "' alt='Profile Picture' width='50' height='50' class='rounded-circle'></td>
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
              echo "<tr><td colspan='10'>No records found.</td></tr>";
          }
          ?> 
        </tbody>
      </table>

      <!-- Pagination -->
      <nav class="mt-3">
        <ul class="pagination justify-content-center">
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
            </li>
          <?php endif; ?>

          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <li class="page-item">
              <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>

      <button class='btn btn-success mt-4 mb-4' onclick="exportToExcel('my-table', 'Students Overview')">Export to Excel</button>
    </div>
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
    const workbook = XLSX.utils.table_to_book(table);
    XLSX.writeFile(workbook, `${filename}.xlsx`);
  }
</script>

<?php include('assets/inc/footer.php'); ?>
