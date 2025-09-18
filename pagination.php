<?php
include 'db_connect.php'; // Your DB connection

// 1. Setup pagination variables
$limit = 2; // rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

// 2. Get total rows
$totalQuery = $conn->query("SELECT COUNT(*) AS total FROM jss2_students_records");
$totalRows = $totalQuery->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// 3. Fetch current page rows
$result = $conn->query("SELECT * FROM jss2_students_records LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Students Table</title>
  <style>
    table { border-collapse: collapse; width: 80%; margin: 20px auto; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    .pagination { text-align: center; margin: 20px; }
    .pagination a { margin: 0 5px; padding: 6px 12px; border: 1px solid #ccc; text-decoration: none; }
    .pagination a.active { background: #007BFF; color: #fff; }
  </style>
</head>
<body>
  <table>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Class</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['name'] ?></td>
        <td><?= $row['class_id'] ?></td>
      </tr>
    <?php endwhile; ?>
  </table>

  <!-- Pagination Links -->
  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page - 1 ?>">« Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>">
        <?= $i ?>
      </a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page + 1 ?>">Next »</a>
    <?php endif; ?>
  </div>
</body>
</html>
