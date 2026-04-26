<?php
session_start();

if (!isset($_SESSION["username"]) || !isset($_SESSION["uid"]) || !isset($_SESSION["role"]))
    header("location: index.php");

if ($_SESSION["role"] !== "admin")
    header("location: index.php");
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
    include("./components/adminNavbar.php");
    ?>
    <div class="px-32 py-10 space-y-5">

        <div class="flex ">
            <?php

            include("./components/adminSidebar.php");
            ?>

            <div class="overflow-x-auto border-2 w-full">
                <table class="table-auto w-full bg-white divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">price</th>
                            <th class="px-4 py-2 text-left">quantity</th>
                            <th class="px-4 py-2 text-left">brand</th>
                            <th class="px-4 py-2 text-left">image</th>
                            <th class="px-4 py-2 text-left">action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        include("config.php");
                        $result = $conn->query("SELECT * FROM products ps join product_name_price pnp on ps.pnp=pnp.id;");
                        while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr class="bg-gray-50">
                                <td class="border px-4 py-2"><?= $row["name"] ?></td>
                                <td class="border px-4 py-2"><?= $row["price"] ?></td>
                                <td class="border px-4 py-2"><?= $row["quantity"] ?></td>
                                <td class="border px-4 py-2"><?= $row["brand"] ?></td>
                                <td class="border px-4 py-2"><img src="<?= getImageUrl($row['image']) ?>" style="width:100px;height:100px;" /></td>
                                <td class="text-center ">
                                    <div class="flex items-center justify-center ">

                                        <div class="flex  space-x-5">
                                            <form action="editProduct.php" method="post">
                                                <input type="number" name="id" hidden value="<?= $row["id"] ?>" />
                                                <input type="text" name="name" hidden value="<?= $row["name"] ?>" />
                                                <input type="number" name="price" hidden value="<?= $row["price"] ?>" />
                                                <input type="text" name="brand" hidden value="<?= $row["brand"] ?>" />
                                                <input type="number" name="quantity" hidden
                                                    value="<?= $row["quantity"] ?>" />


                                                <button class=" px-2 py-1 bg-green-500 text-white">edit</button>
                                            </form>

                                            <form action="deleteProduct.php" method="post">
                                                <input type="number" name="id" hidden value="<?= $row["id"] ?>" />
                                                <button class="px-2 py-1 bg-red-500 text-white" type="submit" name="submit">
                                                    delete</button>
                                            </form>

                                            <form action="addToInventory.php" method="post">
                                                <input type="number" name="id" hidden value="<?= $row["id"] ?>" />
                                                <button class="px-2 py-1 bg-blue-500 text-white" type="submit"
                                                    name="submit">
                                                    add</button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>


                        <!-- Add more rows as needed -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>