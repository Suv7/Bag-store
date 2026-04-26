<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "crud"; // change this

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// When form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_id = $_POST['product_id'];

    $inventoryId = $_POST['inventory_id'];
    $quantity    = $_POST['quantity'];

    // Check if mapping already exists for this inventory
    $check = $conn->prepare("SELECT id FROM inventory_product_mapping WHERE inventory_id = ?");
    $check->bind_param("i", $inventoryId);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE inventory_product_mapping SET product_total_count = ? WHERE inventory_id = ? and product_id=?");
        $stmt->bind_param("iii", $quantity, $inventoryId,$product_id);
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO inventory_product_mapping (inventory_id, product_total_count,product_id) VALUES (?, ?,?)");
        $stmt->bind_param("iii", $inventoryId, $quantity,$product_id);
    }

    if ($stmt->execute()) {
        echo "Inventory quantity saved successfully!";

        header("Location: admin.php?message=Saved+Successfully");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $check->close();
}

// Fetch all inventories for dropdown
?>