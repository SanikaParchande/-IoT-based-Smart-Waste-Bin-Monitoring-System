<?php
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bin_id = $_POST['bin_id'];
    $fill = $_POST['fill_level'];
    $gas = $_POST['gas_status'];

    $sql = "INSERT INTO bin_data (bin_id, fill_level, gas_status) 
            VALUES ('$bin_id', '$fill', '$gas')";

    if ($conn->query($sql) === TRUE) {
        echo "Data stored successfully";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
