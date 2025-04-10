<?php 
session_start();
include __DIR__ . "/../includes/header.php";   
include __DIR__ . "/../config/db.php";  

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    echo "Invalid ID.";
    exit;
}

$id = $_GET["id"];
$sql = "SELECT * FROM reg_tab WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":id", $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "User not found.";
    exit;
}

$_SESSION['id'] = $row["id"];
$name = $row["name"];
$contact_no = $row["contact_no"];
$email = $row["email"];
$profile = $row["profile"];
$dob_db = $row['dob'];

$dt = DateTime::createFromFormat('d-m-Y', $dob_db);
if (!$dt) {
    $dt = DateTime::createFromFormat('Y-m-d', $dob_db);
}
$dob_raw = $dt ? $dt->format('Y-m-d') : '';

$gender = $row["gender"];
$marital = $row["marital"];
$languages = explode(",", $row["languages"]); // convert comma-separated to array
$state = $row["state"];
$district = $row["district"];
$city = $row["city"];
?>

<h1>Register System</h1>
<br>
<div>
    <form id="update_form" method="post" enctype="multipart/form-data">
        <div class="form_fields">
            <label class="label" for="name">Full Name: </label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($name); ?>">
        </div>
        <div class="form_fields">
            <label class="label" for="contact_no">Mobile Number</label>
            <input type="number" name="contact_no" id="contact_no" min="1000000000" max="9999999999" value="<?= htmlspecialchars($contact_no); ?>">
        </div>
        <div class="form_fields">
            <label class="label" for="email">Email: </label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email); ?>">
        </div>
        <div class="form_fields">
            <label for="profile" class="label">Upload Image:</label>
            <input type="file" name="profile" id="profile">
        </div>
        <div class="form_fields">
            <label class="label" for="dob">D.O.B: </label>
            <input type="date" name="dob" id="dob" value="<?= htmlspecialchars($dob_raw); ?>">
        </div>
        <div class="form_fields">
            <label class="label" for="gender">Gender: </label>
            <select name="gender" id="gender">
                <option value="male" <?= $gender === 'male' ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?= $gender === 'female' ? 'selected' : ''; ?>>Female</option>
                <option value="none" <?= $gender === 'none' ? 'selected' : ''; ?>>Prefer not to say</option>
            </select>
        </div>
        <div class="form_fields">
            <label class="label">Marital Status: </label>
            <div>
                <input type="radio" name="marital" id="single" value="single" <?= $marital === 'single' ? 'checked' : ''; ?>>
                <label class="rlabel" for="single">Single</label>
                <input type="radio" name="marital" id="married" value="married" <?= $marital === 'married' ? 'checked' : ''; ?>>
                <label class="rlabel" for="married">Married</label>
            </div>
        </div>
        <div class="form_fields">
            <label for="languages" class="label">Languages Known:</label>
            <select name="languages[]" id="languages" multiple>
                <?php
                $lang_options = ["Tamil", "English", "Hindi", "Telugu", "Urdu"];
                foreach ($lang_options as $lang) {
                    $selected = in_array($lang, $languages) ? 'selected' : '';
                    echo "<option value=\"$lang\" $selected>$lang</option>";
                }
                ?>
            </select>
        </div>
        <div class="form_fields">
            <label class="label" for="state">State: </label>
            <select name="state" id="state" data-selected="<?= htmlspecialchars($state); ?>">
                <option value="" disabled>Select your State</option>
            </select>
        </div>
        <div class="form_fields">
            <label class="label" for="districts">District: </label>
            <select name="districts" id="districts" data-selected="<?= htmlspecialchars($district); ?>">
                <option value="" disabled>Select your district</option>
            </select>
        </div>
        <div class="form_fields">
            <label class="label" for="cities">City: </label>
            <select name="cities" id="cities" data-selected="<?= htmlspecialchars($city); ?>">
                <option value="" disabled>Select your city</option>
            </select>
        </div>
        <div id="form_submit">
            <input type="submit" name="submit" value="Update">
        </div>
    </form>
</div>

<script src="../assets/js/update_data.js"></script>
<?php include __DIR__ . "/../includes/footer.php"; ?>