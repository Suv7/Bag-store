<?php
include("config.php");
include("util.php");
if (isset($_POST["submit"])) {

    $target_file = upload_image($_FILES);

    $name = $_POST["name"];
    $price = $_POST["price"];
    $inventoryNumber = $_POST["inventoryNumber"];
    // $productTotalCount = $_POST["productTotalCount"];


    $conn->query("insert into product_name_price(name,price) values('$name',$price)");
    $id = mysqli_insert_id($conn);
    $stmt = $conn->prepare("insert into products(pnp,image,brand,quantity) values(?,?,?,?)");
    $stmt->bind_param("issi", $id, $target_file, $_POST["brand"], $_POST["quantity"]);

    $stmt->execute();
    $product_id = mysqli_insert_id($conn);


    echo $product_id;
    $istmt = $conn->prepare("INSERT INTO inventory_product_mapping (inventory_id,product_id, product_total_count)
VALUES (?,?,?);");
    $istmt->bind_param("iii", $_POST["inventoryNumber"], $product_id, $_POST["quantity"]);

    $istmt->execute();



    header("location: viewProducts.php");
} else {
    echo "something went wrong";
}
?>