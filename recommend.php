<?php
function recommend($id) {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "crud";
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $current_product_id = $id;

    // Step 1: Get tags of current product
    $sql = "
        SELECT t.id, t.name
        FROM tags t
        INNER JOIN product_tags pt ON t.id = pt.tag_id
        WHERE pt.product_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $current_product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $tag_ids = [];
    while ($row = $result->fetch_assoc()) {
        $tag_ids[] = $row['id'];
    }

    if (empty($tag_ids)) {
        echo "No tags found for this product.";
        exit;
    }

    // Step 2: Get similar products
    $tag_ids_str = implode(",", $tag_ids);
    $sql_similar = "
        SELECT p.id, product_name_price.name, p.image, COUNT(*) AS match_count
        FROM products p
        INNER JOIN product_name_price ON p.pnp = product_name_price.id
        INNER JOIN product_tags pt ON p.id = pt.product_id
        WHERE pt.tag_id IN ($tag_ids_str)
        AND p.id != ?
        GROUP BY p.id
        ORDER BY match_count DESC
    ";
    $stmt = $conn->prepare($sql_similar);
    $stmt->bind_param("i", $current_product_id);
    $stmt->execute();
    $result_similar = $stmt->get_result();

    // Step 3: Display recommendations with images and links
    echo "<h3>Recommended Products:</h3><ul style='list-style:none;padding:0;'>";
    while ($row = $result_similar->fetch_assoc()) {
        $product_id = $row['id'];
        $product_name = htmlspecialchars($row['name']);
        $image_path = htmlspecialchars($row['image']); // ensure stored path is safe
        $match_count = $row['match_count'];

        echo "<li style='margin-bottom:15px;'>
                <a href='BagInfo.php?id={$product_id}' style='text-decoration:none;color:black;'>
                    <img src='" . getImageUrl($image_path) . "' alt='" . $product_name . "' style='width:100px;height:auto;display:block;margin-bottom:5px;'>
                    <strong>{$product_name}</strong>
                </a>
                <div style='font-size:12px;color:gray;'>Matching tags: {$match_count}</div>
              </li>";
    }
    echo "</ul>";

    $conn->close();
}
?>
