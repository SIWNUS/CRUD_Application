<?php 
    session_start();

    include "includes/header.php"; 
    include "config/db.php";

    $limit = 3;
    $page = isset($_GET["page"]) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page -1) * $limit;

    $totalQuery = $conn->query('SELECT COUNT(*) FROM reg_tab');
    $totalRows = $totalQuery->fetchColumn();
    $totalPage = ceil($totalRows / $limit);

    $sql = "SELECT * FROM reg_tab ORDER BY id ASC limit :limit offset :offset;";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);     
?>
    <h1>Register System</h1>
    <div>
        <form id="reg_form" method="post" enctype="multipart/form-data">
            <div class="form_fields">
                <label class="label" for="name">Full Name: </label>
                <input type="text" name="name" id="name" placeholder="Enter Your Full Name Here...">
            </div>
            <div class="form_fields">
                <label class="label" for="contact_no">Mobile Number</label>
                <input type="tel" name="contact_no" id="contact_no" maxlength="10" inputmode="numeric">
            </div>
            <div class="form_fields">
                <label class="label" for="email">Email: </label>
                <input type="email" name="email" id="email" placeholder="something@domain">
            </div>
            <div class="form_fields">
                <label for="profile" class="label">Upload Image:</label>
                <input type="file" name="profile" id="profile">
            </div>
            <div class="form_fields">
                <label class="label" for="dob">D.O.B: </label>
                <input type="date" name="dob" id="dob">
            </div>
            <div class="form_fields">
                <label class="label" for="gender">Gender: </label>
                <select name="gender" id="gender">
                    <option value="" disabled selected>Select your gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="none">Prefer not to say</option>
                </select>
            </div>
            <div class="form_fields">
                <label class="label">Marital Status: </label>
                <div>
                    <input type="radio" name="status" id="single" value="single">
                    <label class="rlabel" for="single">Single</label>
                    <input type="radio" name="status" id="married" value="married">
                    <label class="rlabel" for="married">Married</label>
                </div>
            </div>
            <div class="form_fields">
                <label for="languages" class="label">Languages Known:</label>
                <select name="languages[]" id="languages" multiple>
                    <option value="Tamil">Tamil</option>
                    <option value="English">English</option>
                    <option value="Hindi">Hindi</option>
                    <option value="Telugu">Telugu</option>
                    <option value="Urdu">Urdu</option>
                </select>
            </div>
            <div class="form_fields">
                <label class="label" for="state">State: </label>
                <select name="state" id="state">
                    <option value="" disabled selected>Select your State</option>
                </select>
            </div>
            <div class="form_fields">
                <label class="label" for="districts">District: </label>
                <select name="districts" id="districts">
                    <option value="" disabled selected>Select your district</option>
                </select>
            </div>
            <div class="form_fields">
                <label class="label" for="cities">City: </label>
                <select name="cities" id="cities">
                    <option value="" disabled selected>Select your city</option>
                </select>
            </div>
            <div id="form_submit">
                <input type="submit" name="submit" value="submit">
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
                            <td><?php echo (($page - 1) * $limit) + $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($row["name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["contact_no"]); ?></td>
                            <td><?php echo htmlspecialchars($row["email"]); ?></td>
                            <?php if (!empty($row['profile'])): ?>
                                <td><img src="uploads/<?php echo htmlspecialchars($row['profile']); ?>" alt="profile" width="50"></td>
                            <?php else: ?>
                                <td>No Image</td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($row["dob"]); ?></td>
                            <td><?php echo htmlspecialchars($row["age"]); ?></td>
                            <td><?php echo htmlspecialchars($row["gender"]); ?></td>
                            <td><?php echo htmlspecialchars($row["marital"]); ?></td>
                            <td><?php echo htmlspecialchars($row["languages"]) ?></td>
                            <td><?php echo htmlspecialchars($row["state"]) ?></td>
                            <td><?php echo htmlspecialchars($row["district"]) ?></td>
                            <td><?php echo htmlspecialchars($row["city"]) ?></td>
                            <td><button class="update" data-id="<?php echo htmlspecialchars($row["id"]); ?>" type="button">Update</button></td>
                            <td><button class="delete" data-id="<?php echo htmlspecialchars($row["id"]); ?>" type="button">Delete</button></td>
                        </tr>
                    <?php endforeach ?>
                <?php endif ?>
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

    <script src="assets/js/validate.js"></script>
    <script src="assets/js/delete.js" defer></script>
    <script src="assets/js/update_click.js" defer></script>  
<?php include "includes/footer.php"; ?>