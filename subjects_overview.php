<?php 
session_start();
if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}

include('assets/inc/header.php');
?>
<h3>Subjects Overview</h3>
<link rel="stylesheet" href="pagination.css">

<?php
//search bar
include 'db_connect.php';

$search = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = $conn->real_escape_string($_GET['search']);
    $query = "SELECT * FROM jss2_subjects WHERE subject_name LIKE '%$search%' ORDER BY id ASC";
} else {
    $query = "SELECT * FROM jss2_subjects ORDER BY id ASC";
}

$result = $conn->query($query);

if ($result->num_rows == 0) {
    echo "<p class='text-danger'>No subject found for '<strong>" . htmlspecialchars($search) . "</strong>'</p>";
}
?>

<?php
//Pagination

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
$countQuery  = "SELECT COUNT(*) AS total FROM jss2_subjects $where";
$countResult = $conn->query($countQuery);
$totalRows   = $countResult->fetch_assoc()['total'];
$totalPages  = ceil($totalRows / $limit);

// Main query with LIMIT & OFFSET
$query = "SELECT * FROM jss2_subjects $where ORDER BY id ASC LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

if ($result->num_rows == 0 && !empty($search)) {
    echo "<p class='text-danger'>No record found for '<strong>" . htmlspecialchars($search) . "</strong>'</p>";
}

?>



<form action="" method="get">
<div class="input-group mt-4">
    <span class="input-group-text">Search Subjects</span>
    <input type="text" name="search" id="" class="form-control" placeholder="Search By Name or Code....">
    <button type="submit" class="btn btn-primary">Search</button>
</div>

</form>
<div class="mt-5">
  <div class="card mb-4 shadow-sm mt-4">
    <div class="card-header bg-primary text-white">
      <strong>Subjects List</strong>
    </div>
    <div class="card-body">
      <table class="table table-bordered table-striped mb-0" id="my-table-2">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Subject Name</th>
            <th>Code</th>
            <th>Class</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
           <?php
        if ($result->num_rows > 0) {
        $i = $offset + 1;
        $i = 1;
        while ($row = $result->fetch_assoc()) {
        echo "<tr>
        <td>{$i}</td>
        <td>{$row['subject_name']}</td>
        <td>{$row['code']}</td>
        <td>{$row['class']}</td>
        <td>
          <a href='edit_subject.php?id={$row['id']}' class='btn btn-sm btn-outline-warning'>Edit</a>
        </td>
      </tr>";
        $i++;
        }
    } else {
        echo "<tr><td colspan='8'>No subjects found.</td></tr>";
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
    </div>
  </div>
      <button class="btn btn-success mb-4" onclick="exportToExcel('my-table-2', 'Subjects Overview')">Export to Excel</button>
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
