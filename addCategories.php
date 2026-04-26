<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];

    // Connect to DB
    $conn = new mysqli('localhost', 'root', '', 'crud');

    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);

    if ($stmt->execute()) {
        echo "Category added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<form method="POST" action="add_category.php">
  <input type="text" name="name" placeholder="Category Name" required>
  <textarea name="description" placeholder="Description"></textarea>
  <button type="submit">Add Category</button>
</form>
