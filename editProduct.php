
<?php
session_start();

if (!isset($_SESSION["username"]) || !isset($_SESSION["uid"]) || !isset($_SESSION["role"]))
    header("location: index.php");

if ($_SESSION["role"] !== "admin")
    header("location: index.php");

    $id = $_POST["id"];
    $name = $_POST["name"];
    $price = $_POST["price"];
    $brand = $_POST["brand"];
    $quantity = $_POST["quantity"];
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
<?php
        include ("./components/adminNavbar.php");
        ?>
    <div class="px-32 py-10 space-y-5">
        
        <div class="flex w-full">
            <?php

            include ("./components/adminSidebar.php");
            ?>

            <div class="px-20">
                <h1 class="text-3xl font-bold text-[#902c7e]"> Update Product </h1>
                <form action="prodEdit.php" method="POST" enctype="multipart/form-data"
                    class="space-y-8 flex flex-col w-[500px]  py-10">
                    <input type="number" name="id" hidden value="<?= $id?>"/>
                    <div>
                        <label class=""> Name </label> <br />
                        <input type="text" name="name" class="w-full border-2 rounded-lg py-2 px-1 " value="<?= $name ?>"  />
                    </div>

                    <div>
                        <label> price </label>
                        <input type="number" name="price" class="w-full border-2 rounded-lg py-2 px-1 " value="<?= $price?>"  />

                    </div>

                    <div>
                        <label> brand </label>
                        <input type="text" name="brand" class="w-full border-2 rounded-lg py-2 px-1 " value="<?= $brand?>"  />

                    </div>

                    <div>
                        <label> quantity </label>
                        <input type="number" name="quantity" class="w-full border-2 rounded-lg py-2 px-1 " value="<?=$quantity  ?>"  />
                    </div>
                    <input type="file" name="image" class="text-white"  />
                    <button type="submit" name="submit"
                        class="w-full py-3 border-2 border-green-500 rounded-lg text-green-500 hover:bg-green-500 hover:text-white duration-300">
                        update </button>
                </form>

            </div>
        </div>
    </div>
</body>

</html>

