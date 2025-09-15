<?php include('assets/inc/header.php'); ?>
<h3>View Results</h3>

<?php
include 'db_connect.php'; // db connection

// ================= FILTER HANDLING ===================
$class_id   = $_GET['class_id']   ?? '';
$subject_id = $_GET['subject_id'] ?? '';
$session    = $_GET['session']    ?? '';
$term       = $_GET['term']       ?? '';
$student_id = $_GET['student_id'] ?? '';

// Build WHERE condition dynamically
$where = [];
if ($class_id != '')   $where[] = "r.class = '$class_id'";
if ($subject_id != '') $where[] = "r.subject = '$subject_id'";
if ($session != '')    $where[] = "r.session = '$session'";
if ($term != '')       $where[] = "r.term = '$term'";
if ($student_id != '') $where[] = "r.student_id = '$student_id'";

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

// ================= FETCH RESULTS FOR DISPLAY ===================
$sql = "SELECT r.student_id, s.name, c.class_name, sub.subject_name, r.term, r.session,
               r.first_ca, r.second_ca, r.exam, r.total, r.grade
        FROM results r
        JOIN jss2_students_records s ON r.student_id = s.student_id
        JOIN classes c ON r.class = c.class_id
        JOIN jss2_subjects sub ON r.subject = sub.id
        $whereSQL
        ORDER BY c.class_id, sub.id, s.student_id";
$res = $conn->query($sql);

// Dropdown Data
$classes  = $conn->query("SELECT * FROM classes ORDER BY class_id");
$subjects = $conn->query("SELECT * FROM jss2_subjects ORDER BY id");
$sessions = $conn->query("SELECT DISTINCT session FROM results ORDER BY id DESC");
$terms    = $conn->query("SELECT DISTINCT term FROM school ORDER BY id ASC");
$students = $conn->query("SELECT student_id, name FROM jss2_students_records ORDER BY name");





// Detect if "All Terms" is selected
$allTermsSelected = empty($term); //  "" is for 'All Terms'
?>

<!-- =============== FILTER FORM ================= -->
<div class="card mt-4">
    <div class="card-header bg-info text-white">View Uploaded Results</div>
    <div class="card-body">
        <form method="get" class="row g-3 mb-4" id="filterForm">
            <!-- Class -->
            <div class="col-md-3">
                <label for="class_id" class="form-label">Class</label>
                <select name="class_id" id="class_id" class="form-select" required>
                    <option value="">-- Select Class --</option>
                    <?php while ($c = $classes->fetch_assoc()): ?>
                        <option value="<?= $c['class_id'] ?>" <?= ($c['class_id'] == $class_id ? 'selected' : '') ?>>
                            <?= htmlspecialchars($c['class_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Subject -->
            <div class="col-md-3 mt-4">
                <label for="subject_id" class="form-label">Subject</label>
                <select name="subject_id" id="subject_id" class="form-select">
                    <option value="">All Subjects</option>
                    <!-- <?php while ($s = $subjects->fetch_assoc()): ?>
                        <option value="<?= $s['id'] ?>" <?= ($s['id'] == $subject_id ? 'selected' : '') ?>>
                            <?= htmlspecialchars($s['subject_name']) ?>
                        </option>
                    <?php endwhile; ?> -->
                </select>
            </div>

            <!-- Session -->
            <div class="col-md-3 mt-4">
                <label for="session" class="form-label">Session</label>
                <select name="session" id="session" class="form-select" required>
                    <option value="">-- Select Session --</option>
                    <?php while ($ss = $sessions->fetch_assoc()): ?>
                        <option value="<?= $ss['session'] ?>" <?= ($ss['session'] == $session ? 'selected' : '') ?>>
                            <?= htmlspecialchars($ss['session']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Term -->
            <div class="col-md-3 mt-4">
                <label for="term" class="form-label">Term</label>
                <select name="term" id="term" class="form-select">
                    <option value="">All Terms</option>
                    <?php while ($t = $terms->fetch_assoc()): ?>
                        <option value="<?= $t['term'] ?>" <?= ($t['term'] == $term ? 'selected' : '') ?>>
                            <?= htmlspecialchars($t['term']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Student (depending on class) -->
            <div class="col-md-4 mt-4">
                <label for="student_id" class="form-label">Student(s)</label>
                <select name="student_id" id="student_id" class="form-select">
                    <option value="">All Students</option>
                    <!-- Populated with AJAX -->
                </select>
            </div>

            <!-- Submit -->
            <div class="col-md-2 d-flex align-items-end mt-4">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>


<!-- jQuery (for AJAX) -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#class_id').change(function() {
            var classId = $(this).val();
            $('#student_id').html('<option value="">Loading...</option>');

            if (classId !== "") {
                $.get('get_students.php', {
                    class_id: classId
                }, function(data) {
                    $('#student_id').html(data);
                });
            } else {
                $('#student_id').html('<option value="">All Students</option>');
            }
        });

        // Auto-load students if a class is already selected
        <?php if (!empty($class_id)): ?>
            $('#class_id').trigger('change');
        <?php endif; ?>
    });
</script> -->


<!-- =============== RESULTS TABLE ================= -->
<?php if ($class_id || $subject_id || $session || $term || $student_id): ?>
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">Results For <strong><?= htmlspecialchars($term) ?? 'All' ?> Term </strong></div>
        <div class="card-body">
            <?php if ($res->num_rows > 0): ?>

<div class="table-responsive">
    <table class="table table-bordered table-striped text-center" id="printTable">
        <thead>
            <tr>
                <th rowspan="2">Student ID</th>
                <th rowspan="2">Name</th>
                <th rowspan="2">Class</th>
                <th rowspan="2">Session</th>

                <?php
                // Get subjects for table structure
                $subjectList = $conn->query("SELECT * FROM jss2_subjects ORDER BY id");
                $subjectsArr = [];
                while ($sub = $subjectList->fetch_assoc()) {
                    $subjectsArr[] = $sub['subject_name'];
                    echo "<th colspan='5' style='text-align:center'>" . htmlspecialchars($sub['subject_name']) . "</th>";
                }
                ?>
                <th rowspan="2">Position</th> 
            </tr>
            <tr>
                <?php
                foreach ($subjectsArr as $subj) {
                    if ($allTermsSelected) {
                        echo "<th>1st Term</th><th>2nd Term</th><th>3rd Term</th><th>Grand Total</th><th>Average</th>";
                    } else {
                        echo "<th>1st CA</th><th>2nd CA</th><th>Exam</th><th>Total</th><th>Grade</th>";
                    }
                }
                ?>
            </tr>
        </thead>

        <tbody>
<?php
// Build results by student
$resultsByStudent = [];
while ($row = $res->fetch_assoc()) {
    $sid = $row['student_id'];
    $resultsByStudent[$sid]['info'] = [
        'student_id' => $row['student_id'],
        'name'       => $row['name'],
        'class_name' => $row['class_name'],
        'session'    => $row['session'],
    ];
    if ($allTermsSelected) {
        $termMap = [
            'First'  => '1st Term',
            'Second' => '2nd Term',
            'Third'  => '3rd Term',
        ];
        $termKey = $termMap[$row['term']] ?? $row['term'];
        $resultsByStudent[$sid]['subjects'][$row['subject_name']][$termKey] = $row['total'];
    } else {
        $resultsByStudent[$sid]['subjects'][$row['subject_name']] = [
            'first_ca'  => $row['first_ca'],
            'second_ca' => $row['second_ca'],
            'exam'      => $row['exam'],
            'total'     => $row['total'],
            'grade'     => $row['grade'],
        ];
    }
}

// ✅ Calculate overall total per student for ranking
$studentTotals = [];
foreach ($resultsByStudent as $sid => $student) {
    $overallTotal = 0;
    $subjectCount = 0;

    foreach ($student['subjects'] as $subj => $scores) {
        if ($allTermsSelected) {
            $grandTotal = ($scores['1st Term'] ?? 0) + ($scores['2nd Term'] ?? 0) + ($scores['3rd Term'] ?? 0);
            $overallTotal += $grandTotal;
            $subjectCount++;
        } else {
            $overallTotal += (int)($scores['total'] ?? 0);
            $subjectCount++;
        }
    }

    $studentTotals[$sid] = [
        'total' => $overallTotal,
        'avg'   => $subjectCount > 0 ? round($overallTotal / $subjectCount, 2) : 0
    ];
}

// ✅ Sort students by total DESC
uasort($studentTotals, function($a, $b) {
    return $b['total'] <=> $a['total'];
});

// ✅ Assign ranks
$rankings = [];
$position = 0;
$prevTotal = null;
foreach ($studentTotals as $sid => $data) {
    if ($prevTotal !== null && $data['total'] == $prevTotal) {
        // same score → same position
        $rankings[$sid] = $position;
    } else {
        // new score → update position
        $position++;
        $rankings[$sid] = $position;
    }
    $prevTotal = $data['total'];
}

// ✅ Now print the table rows
foreach ($resultsByStudent as $sid => $student) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($student['info']['student_id']) . "</td>";
    echo "<td>" . htmlspecialchars($student['info']['name']) . "</td>";
    echo "<td>" . htmlspecialchars($student['info']['class_name']) . "</td>";
    echo "<td>" . htmlspecialchars($student['info']['session']) . "</td>";

    foreach ($subjectsArr as $subj) {
        if ($allTermsSelected) {
            $t1 = $student['subjects'][$subj]['1st Term'] ?? 0;
            $t2 = $student['subjects'][$subj]['2nd Term'] ?? 0;
            $t3 = $student['subjects'][$subj]['3rd Term'] ?? 0;

            $displayT1 = $t1 ?: '-';
            $displayT2 = $t2 ?: '-';
            $displayT3 = $t3 ?: '-';

            $grandTotal = ($t1 ?: 0) + ($t2 ?: 0) + ($t3 ?: 0);
            $count = ($t1 ? 1 : 0) + ($t2 ? 1 : 0) + ($t3 ? 1 : 0);
            $average = $count > 0 ? round($grandTotal / $count, 2) : '-';

            echo "<td>$displayT1</td><td>$displayT2</td><td>$displayT3</td><td>$grandTotal</td><td>$average</td>";
        } else {
            $subjData = $student['subjects'][$subj] ?? null;

            $firstCA  = $subjData['first_ca']  ?? '-';
            $secondCA = $subjData['second_ca'] ?? '-';
            $exam     = $subjData['exam']      ?? '-';
            $total    = $subjData['total']     ?? '-';
            $grade    = $subjData['grade']     ?? '-';

            echo "<td>$firstCA</td><td>$secondCA</td><td>$exam</td><td>$total</td><td>$grade</td>";
        }
    }

    // ✅ Show calculated position
    $position = $rankings[$sid] ?? '-';
    echo "<td>{$position}</td>";

    echo "</tr>";
}
?>

        </tbody>
    </table>
    <form method="post" action="export_students_results.php">
    <input type="hidden" name="class_id" value="<?= $class_id ?>">
    <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
    <input type="hidden" name="session" value="<?= $session ?>">
    <input type="hidden" name="term" value="<?= $term ?>">
    <button type="submit" class="btn btn-success">Export Results to Excel</button>
    <span class="btn btn-danger" onclick="exportToPDF()">Export Results to PDF</span>

</form>

</div>
            <?php else: ?>
                <p class="text-muted">No results found for the selected filters.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include('assets/inc/footer.php'); ?>