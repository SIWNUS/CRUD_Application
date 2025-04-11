<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

include("../config/db.php");

$mail = new PHPMailer(true);

function age_finder($date) {
    $date_given = DateTime::createFromFormat("Y-m-d", $date);
    $today = new DateTime();
    $age = $today->diff($date_given)->y;
    return $age;
}

$response = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");

    // Check required fields (note: profile upload is optional on update)
    if (!isset($_POST["name"], $_POST["contact_no"], $_POST["email"], $_POST["dob"], 
               $_POST["gender"], $_POST["marital"], $_POST["languages"], 
               $_POST["state"], $_POST["districts"], $_POST["cities"])) {
        $response['error'] = "ALL FIELDS REQUIRED!!";
        echo json_encode($response);
        exit;
    }

    $name       = htmlspecialchars($_POST["name"]);
    $contact_no = $_POST["contact_no"];
    $email      = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $dob_raw    = $_POST['dob'];
    // Format DOB for storage (using d-m-Y format as in your database)
    $dob        = DateTime::createFromFormat("Y-m-d", $dob_raw)->format('d-m-Y');
    $age        = age_finder($dob_raw);
    $gender     = $_POST["gender"];
    $marital    = $_POST["marital"];
    $languages  = is_array($_POST['languages']) ? implode(',', $_POST['languages']) : $_POST['languages'];
    $state      = $_POST["state"];
    $district   = $_POST["districts"];
    $city       = $_POST["cities"];

    // Basic empty check
    if (empty($name) || empty($contact_no) || empty($email) || empty($dob) ||
        empty($gender) || empty($marital) || empty($languages) || empty($state) ||
        empty($district) || empty($city)) {
        $response['error'] = "Fill in all the fields!!";
        echo json_encode($response);
        exit;
    }

    // Validate contact number and email formats.
    $contact_regEx = "/^\d{10}$/";
    if (!preg_match($contact_regEx, $contact_no)) {
        $response["error"] = "Enter valid contact number!";
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = "Enter valid email!!";
        echo json_encode($response);
        exit;
    }

    // Determine whether this is an update or a new record.
    $isUpdate = isset($_POST["id"]) && is_numeric($_POST["id"]);

    if ($isUpdate) {
        $id = $_POST["id"];
        // For updates, check duplicates excluding the current record.
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
    } else {
        // For insertion, check if the number or email already exists
        $stmt = $conn->prepare("SELECT contact_no FROM reg_tab WHERE contact_no = :contact_no");
        $stmt->execute([":contact_no" => $contact_no]);
        if ($stmt->fetchColumn()) {
            $response['error'] = "Number already exists";
            echo json_encode($response);
            exit;
        }

        $stmt = $conn->prepare("SELECT email FROM reg_tab WHERE email = :email");
        $stmt->execute([":email" => $email]);
        if ($stmt->fetchColumn()) {
            $response['error'] = "Email already exists";
            echo json_encode($response);
            exit;
        }
    }
    
    // Handle file upload if provided.
    $profile       = null;
    $profileUploaded = false;
    if (isset($_FILES["profile"]) && $_FILES["profile"]["error"] === 0) {
        $uploadDir = __DIR__ . "/../uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filename    = uniqid() . "_" . basename($_FILES["profile"]["name"]);
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

    // Now, build the query based on whether we are updating or inserting.
    if ($isUpdate) {
        // For updates, get the current profile from the database if no new file is uploaded.
        if (!$profileUploaded) {
            // No new profile file uploaded, so keep the existing profile.
            $stmt = $conn->prepare("SELECT profile FROM reg_tab WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $existingProfile = $stmt->fetchColumn();
            $profile = $existingProfile;
        }
        
        if ($profileUploaded) {
            $sqlUpdate = "UPDATE reg_tab SET 
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
                WHERE id = :id";
            $stmt = $conn->prepare($sqlUpdate);
            $stmt->bindParam(':profile', $profile);
        } else {
            $sqlUpdate = "UPDATE reg_tab SET 
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
                WHERE id = :id";
            $stmt = $conn->prepare($sqlUpdate);
        }
        
        // Bind common parameters
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
        exit;
    } else {
        // Insert new record
        $sqlInsert = "INSERT INTO reg_tab 
            (name, contact_no, email, profile, dob, age, gender, marital, languages, state, district, city)
            VALUES
            (:name, :contact_no, :email, :profile, :dob, :age, :gender, :marital, :languages, :state, :district, :city)";
        $stmt = $conn->prepare($sqlInsert);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':contact_no', $contact_no);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':profile', $profile);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':marital', $marital);
        $stmt->bindParam(':languages', $languages);
        $stmt->bindParam(':state', $state);
        $stmt->bindParam(':district', $district);
        $stmt->bindParam(':city', $city);

        if ($stmt->execute()) {

            try {
                // Setup and send a confirmation email
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'psuswin00@gmail.com';
                $mail->Password   = 'fbod egnn clxd naib'; // Use your App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('psuswin00@gmail.com', 'Your Name');
                $mail->addAddress($email);

                if ($profile) {
                    $mail->addEmbeddedImage(__DIR__ . '/../uploads/' . $profile, 'profile_pic');
                }

                $mail->isHTML(true);
                $mail->Subject = 'Confirmation Mail';
                $mail->Body    = '<strong>Name: </strong>' . $name . '<br>' .
                                 '<strong>Contact Number: </strong>' . $contact_no . '<br>' .
                                 '<strong>Email: </strong>' . $email . '<br>' .
                                 '<strong>D.O.B: </strong>' . $dob . '<br>' .
                                 '<strong>Age: </strong>' . $age . '<br>' .
                                 '<strong>Gender: </strong>' . $gender . '<br>' .
                                 '<strong>Marital Status: </strong>' . $marital . '<br>' .
                                 '<strong>Languages Known: </strong>' . $languages . '<br>' .
                                 '<strong>State: </strong>' . $state . '<br>' .
                                 '<strong>District: </strong>' . $district . '<br>' .
                                 '<strong>City: </strong>' . $city . '<br>';
                if ($profile) {
                    $mail->Body   .= '<strong>Profile: </strong> <br> <img src="cid:profile_pic" alt="Profile pic" width="200" />';
                }
                $mail->AltBody = "Name: $name\n" .
                                 "Contact Number: $contact_no\n" .
                                 "Email: $email\n" .
                                 "D.O.B: $dob\n" .
                                 "Age: $age\n" .
                                 "Gender: $gender\n" .
                                 "Marital Status: $marital\n" .
                                 "Languages Known: $languages\n" .
                                 "State: $state\n" .
                                 "District: $district\n" .
                                 "City: $city\n" .
                                 "Profile: [Image attached or viewable in HTML version]";
                $mail->send();

                $response['success'] = "Data registered successfully! Check your email!";
                echo json_encode($response);                         
            } catch (Exception $e) {
                $response['error'] = "Email could not be sent. Error: {$mail->ErrorInfo}";
                echo json_encode($response);
            }
            exit;
        } else {
            $error = $stmt->errorInfo();
            $response['error'] = "Data cannot be registered: " . $error[2];
            echo json_encode($response);
            exit;
        }
    }
}
?>
