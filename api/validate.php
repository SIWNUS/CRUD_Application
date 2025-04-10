<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

include("../config/db.php");

$mail = new PHPMailer(true);

function age_finder($date){
    $date_given = DateTime::createFromFormat("Y-m-d", $date);
    $year = $date_given->format("Y");
    $today = new DateTime();
    $age = $today->format("Y") - $year;

    if ($today->format("m-d")<$date_given->format("m-d")){
        $age--;
    }
    return $age;
}

$response = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    header("Content-Type: application/json");

        if (!isset($_POST["name"], $_POST["contact_no"], $_POST["email"], $_POST["dob"], $_POST["gender"], $_POST["status"], $_POST["languages"], $_POST["state"], $_POST["districts"], $_POST["cities"])){
            $response['error'] = "ALL FIELDS REQUIRED!!";
        echo json_encode($response);
        exit;
    }

    $name = htmlspecialchars($_POST["name"]);
    $contact_no = $_POST["contact_no"];
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $dob_raw = $_POST['dob'];
    $dob =  DateTime::createFromFormat("Y-m-d", $dob_raw)->format('d-m-Y');
    $age = age_finder($dob_raw);
    $gender = $_POST["gender"];
    $marital = $_POST["status"];
    $languages = is_array($_POST['languages']) ? implode(',', $_POST['languages']) : $_POST['languages'];
    $state = $_POST["state"];
    $district = $_POST["districts"];
    $city = $_POST["cities"];

    if (empty($name) || empty($contact_no) || empty($email) || empty($dob) || empty($gender) || empty($marital) || empty($languages) || empty($state) || empty($district) || empty($city)){
        $response['error'] = "Fill in all the fields!!";
        echo json_encode($response);
        exit;
    };

    $stmt = $conn -> prepare("SELECT contact_no FROM reg_tab WHERE contact_no = :contact_no");
    $stmt->execute([":contact_no" => $contact_no]);

    if ($stmt -> fetchColumn()) {
        $response['error'] = "Number already exists";
        echo json_encode($response);
        exit;
    }

    $stmt = $conn -> prepare("SELECT email FROM reg_tab WHERE email = :email");
    $stmt->execute([":email" => $email]);

    if ($stmt->fetchColumn()) {
        $response['error'] = "Email already exists";
        echo json_encode($response);
        exit;
    }
    

    $contact_regEx = "/^\d{10}$/";
    if (!preg_match($contact_regEx, $contact_no)){
        $response["error"] = "Enter valid contact numer!";
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $response['error'] = "Enter valid email!!";
        echo json_encode($response);
        exit;
    }

    if (isset($_FILES["profile"])){
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

        if (file_exists($target_file)){
            $response['error'] = "File already exists!!";
            echo json_encode($response);
            exit;
        }


        if ($_FILES["profile"]["size"] > 500000){
            $response["error"] = "File size too big!!";
            echo json_encode($response);
            exit;
        }

        if (!in_array($imageFileType, $valid_types)){
            $response["error"] = "Upload valid files!!";
            echo json_encode($response);
            exit;
        }

        if (!move_uploaded_file($_FILES["profile"]["tmp_name"], $target_file)){
            $response["error"] = "Error uploadng image";
            echo json_encode($response);
            exit;
        }

        $profile = $filename;

        $stmt = $conn->prepare("
        INSERT INTO reg_tab 
          (name, contact_no, email, profile, dob, age, gender, marital, languages, state, district, city)
        VALUES
          (:name, :contact_no, :email, :profile, :dob, :age, :gender, :marital, :languages, :state, :district, :city)
      ");
      
      // bind all 12 parameters:
        $stmt->bindParam(':name',       $name);
        $stmt->bindParam(':contact_no',$contact_no);
        $stmt->bindParam(':email',      $email);
        $stmt->bindParam(':profile',    $profile);
        $stmt->bindParam(':dob',        $dob);
        $stmt->bindParam(':age',        $age);
        $stmt->bindParam(':gender',     $gender);
        $stmt->bindParam(':marital',    $marital);
        $stmt->bindParam(':languages',  $languages);
        $stmt->bindParam(':state',      $state);
        $stmt->bindParam(':district',   $district);
        $stmt->bindParam(':city',       $city);      

        if ($stmt -> execute()){
   
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';             // Set the SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'psuswin00@gmail.com';   // Your Gmail address
                $mail->Password = 'fbod egnn clxd naib';      // Gmail App Password
                $mail->SMTPSecure = 'tls';                  // Encryption
                $mail->Port = 587;

                $mail->setFrom('psuswin00@gmail.com', 'Your Name');
                $mail->addAddress($email);

                $mail->addEmbeddedImage(__DIR__ . '/../uploads/' . $profile, 'profile_pic');

                $mail->isHTML(true);
                $mail->Subject = 'Confirmation Mail';
                $mail->Body = '<strong>Name: </strong>' . $name . '<br>' .
                                '<strong>Contact Number: </strong>' . $contact_no . '<br>' . 
                                '<strong>Email: </strong>' . $email . '<br>' . 
                                '<strong>D.O.B: </strong>' . $dob . '<br>' . 
                                '<strong>Age: </strong>' . $age . '<br>' . 
                                '<strong>Gender: </strong>' . $gender . '<br>' . 
                                '<strong>Marital Status: </strong>' . $marital . '<br>' . 
                                '<strong>Languages Known : </strong>' . $languages . '<br>' . 
                                '<strong>State: </strong>' . $state . '<br>' . 
                                '<strong>District: </strong>' . $district . '<br>' . 
                                '<strong>City: </strong>' . $city . '<br>' . 
                                '<strong>Profile: </strong> <br> <img src="cid:profile_pic" alt= "Profile pic" width=200 />';
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

                $response['success'] = "Data registered succesfully! Check your email!";
                echo json_encode($response);                         
            } catch (Exception $e) {
                echo "Email could not be sent. Error: {$mail->ErrorInfo}";
            }
            exit;
        } else {
            $error = $stmt->errorInfo();
            $response['error'] = "Data cannot be registered: ". $error[2];
            echo json_encode($response);
            exit;
        }
        
    } else {
        $response["error"] = "No file uploaded!!";
        echo json_encode($response);
        exit;
    }
}

?>