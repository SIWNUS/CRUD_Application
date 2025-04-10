<?php 

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    include ("../config/db.php");

    header("Content-Type: application/json");

    $response = [];

    $delete_id = $_GET['id'];

    if ($delete_id){
        $sql = "DELETE FROM reg_tab Where id = :delete_id";
        $stmt = $conn -> prepare($sql);
        $stmt -> bindParam(":delete_id", $delete_id, PDO::PARAM_INT);
        if ($stmt->execute()){
            if ($stmt->rowCount() > 0){
                $response['success'] = "Deleted Successfully";
                echo json_encode($response);
                exit();
            } else {
                $response['error'] = "Record not found!!";
                echo json_encode($response);
                exit();
            }
        } else {
            $response['error'] = "Error executing deletion!";
            echo json_encode($response);
            exit();
        }

    } else {
        $response['error'] = "No recored found!!";
        echo json_encode($response);
        exit();
    }
    
?>