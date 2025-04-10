<?php 
include("../includes/header.php");
include("../config/db.php");

$sql = "SELECT id, name FROM reg_tab;";
$stmt = $conn->query($sql);
$row = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 3;
$offset = ($page - 1) * $limit;

$totalRows = $conn->query("SELECT COUNT(*) FROM marks_tab")->fetchColumn();
$totalPage = ceil($totalRows / $limit);

$sql1 = "SELECT * FROM marks LIMIT $limit OFFSET $offset;";
$stmt1 = $conn->query($sql1); 
$row1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

?>

<?php 

$rankData = $conn->query("SELECT reg_tab.id AS student_id, total, eng, tam, math, sci, soc 
                          FROM marks_tab 
                          INNER JOIN reg_tab ON reg_tab.id = marks_tab.reg_id")->fetchAll(PDO::FETCH_ASSOC);

// Filter students with all subjects >= 40
$rankEligible = array_filter($rankData, function ($entry) {
    return $entry['eng'] >= 40 && $entry['tam'] >= 40 &&
           $entry['math'] >= 40 && $entry['sci'] >= 40 &&
           $entry['soc'] >= 40;
});

usort($rankEligible, function($a, $b) {
    return $b['total'] - $a['total'];
});

$ranks = [];
$rank = 1;
foreach ($rankEligible as $entry) {
    $ranks[$entry['student_id']] = $rank++;
}

?>

<h1>Marksheet</h1>
<div>
    <form id="markForm" method="post">
        <div class="form_fields">
            <label class="label" for="pname">Name</label>
            <select name="pname" id="pname">
                <option value="" disabled selected>Select your name</option>
                <?php foreach($row as $data): ?>
                <option value="<?php echo htmlspecialchars($data['name']); ?>"><?php echo htmlspecialchars($data['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php
        $subjects = [
            "eng" => "English",
            "tam" => "Tamil",
            "math" => "Maths",
            "sci" => "Science",
            "soc" => "Social Studies"
        ];

        foreach ($subjects as $key => $label): 
            $value = $key;
        ?>
            <div class="form_fields">
                <label class="label" for="<?php echo $key; ?>"><?php echo $label; ?></label>
                <input type="number" name="<?php echo $key . '_mark'; ?>" id="<?php echo $key . '_mark'; ?>"
                    class="marks" min="0" max="100"
                    oninput="if(this.value.length > 3) this.value = this.value.slice(0, 3);"
                    value="<?php echo htmlspecialchars($value); ?>">
            </div>
        <?php endforeach; ?>

        <div class="form_fields">
            <label class="label" for="total">Total Marks</label>
            <input type="number" name="tot_mark" id="tot_mark" class="total" readonly>
        </div>
        <div>
            <input type="submit" value="submit" class="submit">
        </div>
    </form>
</div>

<hr>

<div id="list">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>English</th>
                <th>Tamil</th>
                <th>Maths</th>
                <th>Science</th>
                <th>Social</th>
                <th>Total</th>
                <th colspan="2"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($row1 as $i => $mark): ?>
            <tr>
                <td><?php echo (($page - 1) * $limit) + $i + 1; ?></td>
                <td><?php echo htmlspecialchars($mark['name']); ?></td>
                <td><?php echo htmlspecialchars($mark['eng']); ?> <span class="grade-subject"></span></td>
                <td><?php echo htmlspecialchars($mark['tam']); ?> <span class="grade-subject"></span></td>
                <td><?php echo htmlspecialchars($mark['math']); ?> <span class="grade-subject"></span></td>
                <td><?php echo htmlspecialchars($mark['sci']); ?> <span class="grade-subject"></span></td>
                <td><?php echo htmlspecialchars($mark['soc']); ?> <span class="grade-subject"></span></td>
                <td><?php echo htmlspecialchars($mark['total']); ?><span> (<?php echo $mark['grade']; ?>)</span></td>
                <td class="grade-rank">Rank 
                    <?php 
                        echo isset($ranks[$mark['student_id']])
                            ? htmlspecialchars($ranks[$mark['student_id']]) 
                            : '-'; 
                    ?>
                </td>
                <td><button type="button" class="update" data-id="<?php echo htmlspecialchars($mark["mark_id"]); ?>">Edit</button></td>
                <td><button type="button" class="delete" data-id="<?php echo htmlspecialchars($mark["mark_id"]); ?>">Delete</button></td>
            </tr>
            <?php endforeach; ?>
        </tbody>

    </table>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">« Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPage; $i++): ?>
            <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'class="active"'; ?>>
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPage): ?>
            <a href="?page=<?php echo $page + 1; ?>">Next »</a>
        <?php endif; ?>
    </div>
</div>


<script src="../assets/js/mark_update.js"></script>
<script src="../assets/js/delete_marks.js"></script>
<script src="../assets/js/marksUpdateClick.js"></script>

<?php 
include("../includes/footer.php");
?>