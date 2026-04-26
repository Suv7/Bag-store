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
                            <th class="px-4 py-2 text-left">first name</th>
                            <th class="px-4 py-2 text-left">last name</th>
                            <th class="px-4 py-2 text-left">username</th>
                            <th class="px-4 py-2 text-left">phone</th>
                            <th class="px-4 py-2 text-left">email</th>
                            <th class="px-4 py-2 text-left">address</th>
                            <th class="px-4 py-2 text-left">view orders</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        include ("config.php");
                        $result = $conn->query("SELECT * FROM users");
                        while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr class="bg-gray-50">
                                <td class="border px-4 py-2"><?= $row["firstName"] ?></td>
                                <td class="border px-4 py-2"><?= $row["lastName"] ?></td>
                                <td class="border px-4 py-2"><?= $row["username"] ?></td>
                                <td class="border px-4 py-2"><?= $row["phoneNumber"] ?></td>
                                <td class="border px-4 py-2"><?= $row["email"] ?></td>
                                <td class="border px-4 py-2"><?= $row["address"] ?></td>
                                <td class="border px-4 py-2"><a href="viewOrders.php?id=<?=$row["id"]?>"> view orders</a></td>

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