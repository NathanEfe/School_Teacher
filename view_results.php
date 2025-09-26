<?php
session_start();
if (!isset($_SESSION["staff_id"])) {
    header("Location: login/login.php");
    exit;
}
include('assets/inc/header.php'); ?>
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
        <div class="alert alert-warning">Select the <a class="alert-link">Class, Subject, Session and Term to View Results</a></div>
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
                    <?php while ($s = $subjects->fetch_assoc()): ?>
                        <option value="<?= $s['id'] ?>" <?= ($s['id'] == $subject_id ? 'selected' : '') ?>>
                            <?= htmlspecialchars($s['subject_name']) ?>
                        </option>
                    <?php endwhile; ?>
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
                    <option value="">All Terms</option> <!-- Allow empty for All Terms -->
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
</script>


<!-- =============== RESULTS TABLE ================= -->
<?php if ($class_id || $subject_id || $session || $term || $student_id): ?>
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">Results For <strong><?= htmlspecialchars($term) ?? 'All' ?> Term</strong></div>
        <div class="card-body">
            <?php if ($res->num_rows > 0): ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center" id="reports">
                        <thead>
                            <tr>
                                <th rowspan="2">Student ID</th>
                                <th rowspan="2">Name</th>
                                <th rowspan="2">Class</th>
                                <th rowspan="2">Session</th>

                                <?php
                                // Get subjects for table structure
                                if ($subject_id != '') {
                                    $subjectList = $conn->query("SELECT * FROM jss2_subjects WHERE id = '$subject_id' ORDER BY id");
                                } else {
                                    $subjectList = $conn->query("SELECT * FROM jss2_subjects ORDER BY id");
                                }
                                $subjectsArr = [];
                                while ($sub = $subjectList->fetch_assoc()) {
                                    $subjectsArr[] = $sub['subject_name'];
                                    echo "<th colspan='5' style='text-align:center'>" . htmlspecialchars($sub['subject_name']) . "</th>";
                                }
                                ?>
                                <!-- Always show these -->
                                <th rowspan="2">Grand Total</th>
                                <th rowspan="2">Average</th>
                                <th rowspan="2">Position</th>
                            </tr>
                            <tr>
                                <?php
                                foreach ($subjectsArr as $subj) {
                                    if ($allTermsSelected) {
                                        echo "<th>1st Term</th><th>2nd Term</th><th>3rd Term</th><th>Total</th><th>Average</th>";
                                    } else {
                                        echo "<th>1st CA</th><th>2nd CA</th><th>Exam</th><th>Total Mark</th><th>Grade</th>";
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

                            // Calculate overall total per student for ranking
                            $studentStats = [];

                            foreach ($resultsByStudent as $sid => $student) {
                                $grandTotal = 0;
                                $subjectCount = 0;

                                foreach ($student['subjects'] as $subj => $scores) {
                                    if ($allTermsSelected) {
                                        $t1 = $scores['1st Term'] ?? 0;
                                        $t2 = $scores['2nd Term'] ?? 0;
                                        $t3 = $scores['3rd Term'] ?? 0;
                                        $grandTotal += ($t1 + $t2 + $t3);
                                    } else {
                                        $grandTotal += (int)($scores['total'] ?? 0);
                                    }
                                    $subjectCount++;
                                }

                                $average = $subjectCount > 0 ? round($grandTotal / $subjectCount, 2) : 0;

                                $studentStats[$sid] = [
                                    'grand_total' => $grandTotal,
                                    'average'     => $average,
                                ];
                            }
                            
                            // Sort students by average (descending)
                            uasort($studentStats, function($a, $b) {
                                return $b['average'] <=> $a['average'];
                            });

                            $rankings = [];
                            $position = 0;
                            $prevAvg  = null;

                            foreach ($studentStats as $sid => $stats) {
                                if ($prevAvg !== null && $stats['average'] == $prevAvg) {
                                    // same average → same position
                                    $rankings[$sid] = $position;
                                } else {
                                    $position++;
                                    $rankings[$sid] = $position;
                                }
                                $prevAvg = $stats['average'];
                            }
                                                        

                            // Sort resultsByStudent by ranking
                            uksort($resultsByStudent, function($a, $b) use ($rankings) {
                                return $rankings[$a] <=> $rankings[$b];
                            });

                            //Add position suffixes (e.g., 1st, 2nd, 3rd)
                            function ordinal_suffix($num) {
                                if (!in_array(($num % 100), [11, 12, 13])) {
                                    switch ($num % 10) {
                                        case 1:  return $num . 'st';
                                        case 2:  return $num . 'nd';
                                        case 3:  return $num . 'rd';
                                    }
                                }
                                return $num . 'th';
                            }


                            // print the table rows
                            foreach ($resultsByStudent as $sid => $student) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($student['info']['student_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($student['info']['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($student['info']['class_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($student['info']['session']) . "</td>";
                                $grandTotal = $studentStats[$sid]['grand_total'] ?? 0;


                                foreach ($subjectsArr as $subj) {
                                    if ($allTermsSelected) {
                                        $t1 = $student['subjects'][$subj]['1st Term'] ?? 0;
                                        $t2 = $student['subjects'][$subj]['2nd Term'] ?? 0;
                                        $t3 = $student['subjects'][$subj]['3rd Term'] ?? 0;

                                        $displayT1 = $t1 ?: '-';
                                        $displayT2 = $t2 ?: '-';
                                        $displayT3 = $t3 ?: '-';

                                        $grandTotal = ($t1 + $t2 + $t3);
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

                                // ===== Grand Total & Average for this student =====
                                $grandTotalPerStudent = 0;
                                $subjectCount = 0;

                                foreach ($student['subjects'] as $subj => $scores) {
                                    if ($allTermsSelected) {
                                        $t1 = $scores['1st Term'] ?? 0;
                                        $t2 = $scores['2nd Term'] ?? 0;
                                        $t3 = $scores['3rd Term'] ?? 0;
                                        $grandTotalPerStudent += ($t1 + $t2 + $t3);
                                    } else {
                                        $grandTotalPerStudent += (int)($scores['total'] ?? 0);
                                    }
                                    $subjectCount++;
                                }
                                $average    = $studentStats[$sid]['average'] ?? 0;
                                $position   = $rankings[$sid] ?? '-';
                                $posDisplay = is_numeric($position) ? ordinal_suffix($position) : '-';

                                echo "<td>{$grandTotalPerStudent}</td>";
                                echo "<td>{$average}</td>";
                                echo "<td>{$posDisplay}</td>";
                                echo "</tr>";
                            }
                            ?>

                        </tbody>
                    </table>
                    <form method="post" action="export_students_results.php" style="display:inline-block;">
                        <input type="hidden" name="class_id" value="<?= $class_id ?>">
                        <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
                        <input type="hidden" name="session" value="<?= $session ?>">
                        <input type="hidden" name="term" value="<?= $term ?>">
                        <button type="submit" class="btn btn-success">Export Results to Excel</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-warning alert-muted">No results found for the selected filters.</div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadPDF() {
        const element = document.getElementById('reports');

        const opt = {
            margin: 0.5,
            filename: 'reports.pdf',
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 3,
                scrollY: 0
            },
            jsPDF: {
                unit: 'in',
                format: [50, 12.5],
                orientation: 'landscape'
            },
            pagebreak: {
                mode: ['css', 'legacy']
            }
        };

        html2pdf().set(opt).from(element).save();
    }
</script>
<?php include('assets/inc/footer.php'); ?>