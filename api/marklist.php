<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include("../config/db.php");

$response = [];

// Only run if the request method is POST.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");

    // Check that all required fields are provided.
    if (
        !isset(
            $_POST["pname"],
            $_POST["eng_mark"],
            $_POST["tam_mark"],
            $_POST["math_mark"],
            $_POST["sci_mark"],
            $_POST["soc_mark"],
            $_POST["tot_mark"]
        )
    ) {
        $response["error"] = "Fill all fields!!";
        echo json_encode($response);
        exit;
    }

    // Get the form values.
    $name       = trim($_POST["pname"]);
    $eng        = $_POST["eng_mark"];
    $tam        = $_POST["tam_mark"];
    $math       = $_POST["math_mark"];
    $sci        = $_POST["sci_mark"];
    $soc        = $_POST["soc_mark"];
    $tot        = $_POST["tot_mark"];
    $reg_id     = '';

    // Lookup the user id from reg_tab using the provided name.
    $sql = "SELECT id FROM reg_tab WHERE name = :name";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        $reg_id = $data["id"];
    } else {
        $response["error"] = "Cannot find user id";
        echo json_encode($response);
        exit;
    }

    // Determine whether we are doing an update or an insert.
    $isUpdate = false;
    if (isset($_SESSION["marks_id"]) && is_numeric($_SESSION["marks_id"])) {
        $isUpdate = true;
        $id = $_SESSION["marks_id"];
    }

    if (!$isUpdate) {
        // For new records, check if there's already a marks record with this reg_id.
        $stmt2 = $conn->prepare("SELECT id FROM marks_tab WHERE reg_id = :reg_id");
        $stmt2->execute([":reg_id" => $reg_id]);
        if ($stmt2->fetchColumn()) {
            $response['error'] = "This user is already graded in another record!";
            echo json_encode($response);
            exit;
        }
    }

    // Decide which SQL to run based on whether the user is updating or inserting.
    if ($isUpdate) {
        $sql1 = "UPDATE marks_tab SET
                    reg_id = :reg_id,
                    eng = :eng,
                    tam = :tam,
                    math = :math,
                    sci = :sci,
                    soc = :soc,
                    total = :tot
                WHERE id = :id";
    } else {
        $sql1 = "INSERT INTO marks_tab 
                    (reg_id, eng, tam, math, sci, soc, total) VALUES 
                    (:reg_id, :eng, :tam, :math, :sci, :soc, :tot)";
    }
    
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bindParam(":reg_id", $reg_id, PDO::PARAM_INT);
    $stmt1->bindParam(":eng", $eng, PDO::PARAM_INT);
    $stmt1->bindParam(":tam", $tam, PDO::PARAM_INT);
    $stmt1->bindParam(":math", $math, PDO::PARAM_INT);
    $stmt1->bindParam(":sci", $sci, PDO::PARAM_INT);
    $stmt1->bindParam(":soc", $soc, PDO::PARAM_INT);
    $stmt1->bindParam(":tot", $tot, PDO::PARAM_INT);
    if ($isUpdate) {
        $stmt1->bindParam(":id", $id, PDO::PARAM_INT);
    }

    if ($stmt1->execute()) {
        if ($isUpdate) {
            $response["success"] = "Marklist Successfully Updated!";
        } else {
            $response["success"] = "Marklist Successfully Updated!";
        }
        echo json_encode($response);
        exit;
    } else {
        $response["error"] = "Error inserting/updating data to db";
        echo json_encode($response);
        exit;
    }
}
?>
