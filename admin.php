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

        <div class="flex w-full">
            <?php

            include("./components/adminSidebar.php");
            ?>

            <div>
                <?php
                if (isset($_GET["message"]))
                    echo $_GET["message"];
                ?>

            </div>
        </div>
    </div>
</body>

</html>