<?php
if (isset($_POST["submit"])) {
    session_start();
    include ("config.php");

    if (!isset($_SESSION["uid"])) {
        header("location: /bags_store/login.php");
    } else {
        $bid = $_POST["id"];
        $uid = $_SESSION["uid"];
    
        $result = $conn->query("select * from cart where bid=$bid and uid=$uid");

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            $quantity = $row["quantity"]+$_POST["quantity"];
            $conn->query("update cart set quantity=$quantity where bid=$bid and uid=$uid;");
        } else {
            $sql = "INSERT INTO cart (bid,uid,quantity) values(?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $bid, $uid,$_POST["quantity"]);


            // echo $bid. " " . $uid. " <br>";
            $stmt->execute();
        }
        header("location: cart.php");
    }
}
?>