<?php 

ini_set('display_errors', 1);
error_reporting(E_ALL);

include("../config/db.php");

$response = [];

if ($_SERVER["REQUEST_METHOD"] === "POST"){

    if (!isset($_POST["pname"], $_POST["eng_mark"], $_POST["tam_mark"], $_POST["math_mark"], $_POST["sci_mark"], $_POST["soc_mark"], $_POST["tot_mark"])){
        $response["error"] = "Fill all fields!!";
        echo json_encode($response);
        exit;
    }

    $name = trim($_POST["pname"]);
    $reg_id = '';

    $sql = "SELECT id FROM reg_tab WHERE name = :name";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->execute();

    $data = $stmt -> fetch(PDO::FETCH_ASSOC);
    if ($data){
        $reg_id = $data["id"];
    } else {
        $response["error"] = "Cannot find user id";
        echo json_encode($response);
        exit;
    }

    $stmt2 = $conn->prepare("SELECT id FROM marks_tab WHERE reg_id = :reg_id");
    $stmt2->execute([":reg_id" => $reg_id]);

    if ($stmt2->fetchColumn()) {
        $response['error'] = "This user is already graded in another record!";
        echo json_encode($response);
        exit;
    }

    $eng = $_POST["eng_mark"];
    $tam = $_POST["tam_mark"];
    $math = $_POST["math_mark"];
    $sci = $_POST["sci_mark"];
    $soc = $_POST["soc_mark"];
    $tot = $_POST["tot_mark"];

    $sql1 = "INSERT INTO marks_tab 
            (reg_id, eng, tam, math, sci, soc, total) VALUES 
            (:reg_id, :eng, :tam, :math, :sci, :soc, :tot);";
    
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bindParam(":reg_id", $reg_id, PDO::PARAM_INT);
    $stmt1->bindParam(":eng", $eng, PDO::PARAM_INT);
    $stmt1->bindParam(":tam", $tam, PDO::PARAM_INT);
    $stmt1->bindParam(":math", $math, PDO::PARAM_INT);
    $stmt1->bindParam(":sci", $sci, PDO::PARAM_INT);
    $stmt1->bindParam(":soc", $soc, PDO::PARAM_INT);
    $stmt1->bindParam(":tot", $tot, PDO::PARAM_INT);

    if ($stmt1->execute()) {
        $response["success"] = "Marklist Successfully Updated!";
        echo json_encode($response);
        exit;
    } else {
        $response["error"] = "Error inserting data to db";
        echo json_encode($response);
        exit;
    }

}

?>