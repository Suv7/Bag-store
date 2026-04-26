<?php
// db connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "crud";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Add new tag if submitted
if (isset($_POST['new_tag']) && $_POST['new_tag'] != "") {
    $tag_name = trim($_POST['new_tag']);
    $stmt = $conn->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
    $stmt->bind_param("s", $tag_name);
    $stmt->execute();
}

// Add product-tag relationship
if (isset($_POST['product_id']) && isset($_POST['tag_ids'])) {
    $product_id = (int) $_POST['product_id'];
    foreach ($_POST['tag_ids'] as $tag_id) {
        $stmt = $conn->prepare("INSERT IGNORE INTO product_tags (product_id, tag_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $product_id, $tag_id);
        $stmt->execute();
    }
    echo "<p style='color:green;'>Tags added successfully!</p>";
}

// Fetch products
$products = $conn->query("SELECT products.id, name FROM products INNER JOIN product_name_price on products.pnp = product_name_price.id");

// Fetch tags
$tags = $conn->query("SELECT id, name FROM tags");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Tags to Product</title>
</head>
<body>
    <h2>Add Tags to Product</h2>
    <form method="POST">
        <label>Select Product:</label><br>
        <select name="product_id" required>
            <option value="">--Select Product--</option>
            <?php while ($p = $products->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Select Tags:</label><br>
        <?php while ($t = $tags->fetch_assoc()): ?>
            <input type="checkbox" name="tag_ids[]" value="<?= $t['id'] ?>"> <?= htmlspecialchars($t['name']) ?><br>
        <?php endwhile; ?>
        <br>

        <label>Add New Tag (optional):</label><br>
        <input type="text" name="new_tag" placeholder="Enter new tag"><br><br>

        <button type="submit">Save Tags</button>
    </form>
</body>
</html>