<?php 
session_start();
include __DIR__ . "/includes/header.php";   
include __DIR__ . "/config/db.php";  

// Initialize variables with empty/default values.
$name = "";
$contact_no = "";
$email = "";
$profile = "";
$dob_raw = "";
$gender = "";
$marital = "";
$languages = [];
$state = "";
$district = "";
$city = "";

// Check if the page is in update mode (an id is provided in the URL).
if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
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
    
    // Store the id in session (or you can simply pass it along in a hidden field)
    $_SESSION['id'] = $row["id"];
    $name = $row["name"];
    $contact_no = $row["contact_no"];
    $email = $row["email"];
    $profile = $row["profile"];
    $dob_db = $row['dob'];
    
    // Convert the stored DOB to a format acceptable for the date input field
    $dt = DateTime::createFromFormat('d-m-Y', $dob_db);
    if (!$dt) {
        $dt = DateTime::createFromFormat('Y-m-d', $dob_db);
    }
    $dob_raw = $dt ? $dt->format('Y-m-d') : '';
    
    $gender = $row["gender"];
    $marital = $row["marital"];
    $languages = explode(",", $row["languages"]); // Convert comma-separated string to an array.
    $state = $row["state"];
    $district = $row["district"];
    $city = $row["city"];
}
?>

<h1>Register System</h1>
<div>
    <!-- The same form works for both registration and update -->
    <form id="<?= isset($id) ? 'update_form' : 'reg_form'; ?>" method="post" enctype="multipart/form-data">
        <?php 
        // If updating, include the record id as a hidden field.
        if (isset($id)) { 
        ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($id); ?>">
        <?php } ?>
        <div class="form_fields">
            <label class="label" for="name">Full Name: </label>
            <input type="text" name="name" id="name" placeholder="Enter Your Full Name Here..." value="<?= htmlspecialchars($name); ?>">
        </div>
        <div class="form_fields">
            <label class="label" for="contact_no">Mobile Number</label>
            <input type="tel" name="contact_no" id="contact_no" maxlength="10" inputmode="numeric" value="<?= htmlspecialchars($contact_no); ?>">
        </div>
        <div class="form_fields">
            <label class="label" for="email">Email: </label>
            <input type="email" name="email" id="email" placeholder="something@domain" value="<?= htmlspecialchars($email); ?>">
        </div>
        <div class="form_fields">
            <label for="profile" class="label">Upload Image:</label>
            <input type="file" name="profile" id="profile">
            <?php if (!empty($profile)) : ?>
                <p>Current Image: <img src="uploads/<?= htmlspecialchars($profile); ?>" alt="profile" width="50"></p>
            <?php endif; ?>
        </div>
        <div class="form_fields">
            <label class="label" for="dob">D.O.B: </label>
            <input type="date" name="dob" id="dob" value="<?= htmlspecialchars($dob_raw); ?>">
        </div>
        <div class="form_fields">
            <label class="label" for="gender">Gender: </label>
            <select name="gender" id="gender">
                <option value="" disabled <?= empty($gender) ? 'selected' : ''; ?>>Select your gender</option>
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
                    echo "<option value=\"" . htmlspecialchars($lang) . "\" $selected>" . htmlspecialchars($lang) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form_fields">
            <label class="label" for="state">State: </label>
            <select name="state" id="state" data-selected="<?= htmlspecialchars($state); ?>">
                <option value="" disabled <?= empty($state) ? 'selected' : ''; ?>>Select your State</option>
                <!-- Additional state options can be dynamically loaded -->
            </select>
        </div>
        <div class="form_fields">
            <label class="label" for="districts">District: </label>
            <select name="districts" id="districts" data-selected="<?= htmlspecialchars($district); ?>">
                <option value="" disabled <?= empty($district) ? 'selected' : ''; ?>>Select your district</option>
                <!-- Additional district options can be dynamically loaded -->
            </select>
        </div>
        <div class="form_fields">
            <label class="label" for="cities">City: </label>
            <select name="cities" id="cities" data-selected="<?= htmlspecialchars($city); ?>">
                <option value="" disabled <?= empty($city) ? 'selected' : ''; ?>>Select your city</option>
                <!-- Additional city options can be dynamically loaded -->
            </select>
        </div>
        <div id="form_submit">
            <!-- Button text changes based on update or new registration -->
            <input type="submit" name="submit" value="<?= isset($id) ? 'Update' : 'Submit'; ?>">
        </div>
    </form>
</div>

<hr>

<?php
// --------------------
// Listing Section with Pagination
// --------------------
$limit = 3;
$page = isset($_GET["page"]) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$totalQuery = $conn->query('SELECT COUNT(*) FROM reg_tab');
$totalRows = $totalQuery->fetchColumn();
$totalPage = ceil($totalRows / $limit);

$sql = "SELECT * FROM reg_tab ORDER BY id ASC LIMIT :limit OFFSET :offset;";
$stmt = $conn->prepare($sql);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);     
?>

<div id="list">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Profile</th>
                <th>D.O.B</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Status</th>
                <th>Languages</th>
                <th>State</th>
                <th>District</th>
                <th>City</th>
                <th colspan="2"></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data)): ?>
                <?php foreach($data as $i => $row): ?>
                    <tr>
                        <td><?= (($page - 1) * $limit) + $i + 1; ?></td>
                        <td><?= htmlspecialchars($row["name"]); ?></td>
                        <td><?= htmlspecialchars($row["contact_no"]); ?></td>
                        <td><?= htmlspecialchars($row["email"]); ?></td>
                        <?php if (!empty($row['profile'])): ?>
                            <td><img src="uploads/<?= htmlspecialchars($row['profile']); ?>" alt="profile" width="50"></td>
                        <?php else: ?>
                            <td>No Image</td>
                        <?php endif; ?>
                        <td><?= htmlspecialchars($row["dob"]); ?></td>
                        <td><?= htmlspecialchars($row["age"]); ?></td>
                        <td><?= htmlspecialchars($row["gender"]); ?></td>
                        <td><?= htmlspecialchars($row["marital"]); ?></td>
                        <td><?= htmlspecialchars($row["languages"]); ?></td>
                        <td><?= htmlspecialchars($row["state"]); ?></td>
                        <td><?= htmlspecialchars($row["district"]); ?></td>
                        <td><?= htmlspecialchars($row["city"]); ?></td>
                        <td><button class="update" data-id="<?= htmlspecialchars($row["id"]); ?>" type="button">Update</button></td>
                        <td><button class="delete" data-id="<?= htmlspecialchars($row["id"]); ?>" type="button">Delete</button></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1; ?>">« Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPage; $i++): ?>
            <a href="?page=<?= $i; ?>" <?= ($i == $page) ? 'class="active"' : ''; ?>>
                <?= $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPage): ?>
            <a href="?page=<?= $page + 1; ?>">Next »</a>
        <?php endif; ?>
    </div>
</div>

<!--
    Include JavaScript files as needed.
    Here, update_click.js handles click events on the update button,
    delete.js handles deletion, and validate.js may perform form validations.
-->


<script src="assets/js/validate.js"></script>

<script src="assets/js/delete.js" defer></script>
<script src="assets/js/update_click.js" defer></script>  

<?php include "includes/footer.php"; ?>
