<?php 

session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

include("../config/db.php");

function age_finder($dob_str) {
    $dob = DateTime::createFromFormat('d-m-Y', $dob_str);
    if (!$dob) {
        // Handle invalid date string
        return "Invalid date";
    }

    $today = new DateTime();
    $age = $today->diff($dob)->y;
    return $age;
}

$response = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    header("Content-Type: application/json");

    if (!isset($_POST["name"], $_POST["contact_no"], $_POST["email"], $_POST["dob"], $_POST["gender"], $_POST["marital"], $_POST["languages"], $_POST["state"], $_POST["districts"], $_POST["cities"])){
        $response['error'] = "ALL FIELDS REQUIRED!!";
        echo json_encode($response);
        exit;
    }

    $id = $_SESSION["id"]; 
    $name = htmlspecialchars($_POST["name"]);
    $contact_no = $_POST["contact_no"];
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $dob     = $_POST['dob'];
    $age     = age_finder($dob);    
    $gender = $_POST["gender"];
    $marital = $_POST["marital"];
    $languages = is_array($_POST['languages']) ? implode(',', $_POST['languages']) : $_POST['languages'];
    $state = $_POST["state"];
    $district = $_POST["districts"];
    $city = $_POST["cities"];

    if (empty($name) || empty($contact_no) || empty($email) || empty($dob) || empty($gender) || empty($marital) || empty($languages) || empty($state) || empty($district) || empty($city)){
        $response['error'] = "Fill in all the fields!!";
        echo json_encode($response);
        exit;
    }

    // Validate contact number
    $contact_regEx = "/^\d{10}$/";
    if (!preg_match($contact_regEx, $contact_no)){
        $response["error"] = "Enter valid contact number!";
        echo json_encode($response);
        exit;
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $response['error'] = "Enter valid email!!";
        echo json_encode($response);
        exit;
    }

    // Check for duplicate contact/email (excluding current ID)
    $stmt = $conn->prepare("SELECT id FROM reg_tab WHERE contact_no = :contact_no AND id != :id");
    $stmt->execute([':contact_no' => $contact_no, ':id' => $id]);
    if ($stmt->fetchColumn()) {
        $response['error'] = "Number already exists";
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("SELECT id FROM reg_tab WHERE email = :email AND id != :id");
    $stmt->execute([':email' => $email, ':id' => $id]);
    if ($stmt->fetchColumn()) {
        $response['error'] = "Email already exists";
        echo json_encode($response);
        exit;
    }

    if (!isset($_POST['languages']) || !is_array($_POST['languages'])) {
        $response['error'] = "ALL FIELDS REQUIRED!!";
        echo json_encode($response);
        exit;
    }
    $languages = implode(',', $_POST['languages']);    

    $profile = null;
    $profileUploaded = false;

    if (isset($_FILES["profile"]) && $_FILES["profile"]["error"] === 0) {
        $uploadDir = __DIR__ . "/../uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . "_" . basename($_FILES["profile"]["name"]);
        $target_file = $uploadDir . $filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $valid_types = ["jpg", "png", "jpeg"];

        $check = getimagesize($_FILES["profile"]["tmp_name"]);
        if ($check === false) {
            $response["error"] = "Not a valid image!!";
            echo json_encode($response);
            exit;
        }

        if ($_FILES["profile"]["size"] > 500000) {
            $response["error"] = "File size too big!!";
            echo json_encode($response);
            exit;
        }

        if (!in_array($imageFileType, $valid_types)) {
            $response["error"] = "Upload valid files!!";
            echo json_encode($response);
            exit;
        }

        if (!move_uploaded_file($_FILES["profile"]["tmp_name"], $target_file)) {
            $response["error"] = "Error uploading image";
            echo json_encode($response);
            exit;
        }

        $profile = $filename;
        $profileUploaded = true;
    }

    // Build query based on profile upload
    if ($profileUploaded) {
        $stmt = $conn->prepare("
            UPDATE reg_tab SET 
                name = :name,
                contact_no = :contact_no,
                email = :email,
                profile = :profile,
                dob = :dob,
                age = :age,
                gender = :gender,
                marital = :marital,
                languages = :languages,
                state = :state,
                district = :district,
                city = :city
            WHERE id = :id
        ");
        $stmt->bindParam(':profile', $profile);
    } else {
        $stmt = $conn->prepare("
            UPDATE reg_tab SET 
                name = :name,
                contact_no = :contact_no,
                email = :email,
                dob = :dob,
                age = :age,
                gender = :gender,
                marital = :marital,
                languages = :languages,
                state = :state,
                district = :district,
                city = :city
            WHERE id = :id
        ");
    }

    // Bind common params
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':contact_no', $contact_no);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':dob', $dob);
    $stmt->bindParam(':age', $age);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':marital', $marital);
    $stmt->bindParam(':languages', $languages);
    $stmt->bindParam(':state', $state);
    $stmt->bindParam(':district', $district);
    $stmt->bindParam(':city', $city);

    if ($stmt->execute()) {
        $response["success"] = "Updated Successfully!";
    } else {
        $response["error"] = "Failed to update data.";
    }

    echo json_encode($response);
}
?>
