<?php
include("config.php");
if(isset($_POST["submit"])) {
    $conn->query("delete from products where id=". $_POST["id"]);
    header("location: viewProducts.php");
}else {
    echo "something went wrong";
}   