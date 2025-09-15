<?php include('assets/inc/header.php'); ?>
<h3>Subjects Overview</h3>
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
