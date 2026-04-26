<?php
include("config.php");
include("util.php");
if(isset($_POST["submit"])) {
    $price = $_POST["price"];
    $id = $_POST["id"];
    $name = $_POST["name"];
    $quantity = $_POST["quantity"];
    $brand = $_POST["brand"];
    $query = "";
    foreach($_FILES as $x => $y) {
            echo "$x";
            foreach($y as $val) {
                echo $val;
            }
    }

    $result = $conn->query("SELECT pnp from products where id=$id") or die("Something went wrong");

    if($result->num_rows ==0) die("Something went wrong");

    $pnp = $result->fetch_assoc()["pnp"];
    if(isset($_FILES["image"]) && !empty($_FILES["image"]) && $_FILES["image"]["error"]!=4) {
        $target_file = upload_image($_FILES);
        $query = "update products set image='$target_file',brand='$brand',quantity='$quantity' where id=$id";
    }else {
        $query = "update products set brand='$brand',quantity='$quantity' where id=$id";

    }

    $conn->query("update product_name_price set  name='$name', price=$price where id=$pnp");

    $conn->query($query);

    header("location: viewProducts.php");
}else {
    echo "something went wrong";
}   