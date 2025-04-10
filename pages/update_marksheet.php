<?php
session_start();
include __DIR__ . "/../includes/header.php";   
include __DIR__ . "/../config/db.php";  

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    echo "Invalid ID.";
    exit;
}

$id = (int) $_GET["id"];

$sql = "
    SELECT m.*, r.name 
    FROM marks_tab m 
    JOIN reg_tab r ON m.reg_id = r.id 
    WHERE m.id = :id
";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":id", $id, PDO::PARAM_INT);
$stmt->execute();
$mark_row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mark_row) {
    echo "User not found.";
    exit;
}

$_SESSION['marks_id'] = $mark_row["id"];
$reg_name = $mark_row["name"];
$eng = $mark_row["eng"];
$tam = $mark_row["tam"];
$math = $mark_row["math"];
$sci = $mark_row["sci"];
$soc = $mark_row["soc"];
$tot = $mark_row["total"];

// Fetch all names for the dropdown
$nameStmt = $conn->query("SELECT id, name FROM reg_tab");
$nameList = $nameStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Marksheet</h1>
<div>
    <form id="markUpdateForm" method="post" action="submit_marks.php">
        <input type="hidden" name="mark_id" value="<?php echo htmlspecialchars($mark_row['id']); ?>">

        <div class="form_fields">
            <label class="label" for="pname">Name</label>
            <select name="pname" id="pname">
                <option value="" disabled>Select your name</option>
                <?php foreach ($nameList as $data): ?>
                    <option value="<?php echo htmlspecialchars($data['name']); ?>" 
                        <?php echo ($data['name'] === $reg_name) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($data['name']); ?>
                    </option>
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
            $value = $$key;
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
            <input type="number" name="tot_mark" id="tot_mark" class="total" value="<?php echo htmlspecialchars($tot); ?>" readonly>
        </div>

        <div>
            <input type="submit" value="Update" class="submit">
        </div>
    </form>
</div>

<script src="../assets/js/marksUpdate.js"></script>
<script src="../assets/js/mark_update.js"></script>

<?php include('../includes/footer.php') ?>