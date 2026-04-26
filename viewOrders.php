<?php
session_start();

if (!isset($_SESSION["username"]) || !isset($_SESSION["uid"]) || !isset($_SESSION["role"]))
    header("location: index.php");

if ($_SESSION["role"] !== "admin")
    header("location: index.php");

if(!isset($_GET["id"]))  return;
$id = $_GET["id"];



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
        
        <div class="flex ">
            <?php

            include ("./components/adminSidebar.php");
            ?>

            <div class="overflow-x-auto border-2 w-full">
                <table class="table-auto w-full bg-white divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">order id</th>
                            <th class="px-4 py-2 text-left">total price</th>
                            <th class="px-4 py-2 text-left">createdDate</th>
                            <th class="px-4 py-2 text-left"> view details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        include ("config.php");
                        $result = $conn->query("SELECT *  FROM orders where uid=$id ");
                        while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr class="bg-gray-50">
                                <td class="border px-4 py-2"><?= $row["id"] ?></td>
                                <td class="border px-4 py-2"><?= $row["total_price"] ?></td>
                                <td class="border px-4 py-2"><?= $row["createdDate"] ?></td>
                                <td class="border px-4 py-2"><a href="adminInvoice.php?id=<?=$row["id"]?>">view details</a> </td>

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