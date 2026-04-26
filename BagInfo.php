<?php
include ("config.php");
require "recommend.php";
// Check if a specific parameter exists
if (isset($_GET['id'])) {
    // Retrieve the value of the parameter
    $id = $_GET['id'];
    $result = $conn->query("select *,pnp.id as pnp_id from products ps join product_name_price pnp on ps.pnp=pnp.id where ps.id=$id ");
    $row = mysqli_fetch_assoc($result);
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/bags_store/index.css" />
        <script src="https://fontawesome.com/" crossorigin="anonymous"></script>
        <link href="https://fontawesome.com/icons" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
        <script src="https://cdn.tailwindcss.com"></script>

        <title>Bag Detail</title>
    </head>

    <body>
        <div class="container">

            <?php
            include ("./components/navbar.php");
            ?>
            <h1 class="text-[#902c7e]  px-32  text-5xl font-bold mt-5">Bag Info</h1>
            <div class=" flex space-y-8 items-start mx-auto  px-20 py-10 space-x-10">
                <div>

                    <img class="object-cover  px-10 py-10 w-[500px]" src="<?php
                    if (strncmp($row["image"], "http", 4) === 0) {
                        echo $row["image"];
                    } else
                        echo "http://localhost:80/bags_store/" . $row["image"];
                    ?>" />
                </div>

                <div class="flex flex-col w-full space-y-5 border-2 px-10 py-5 rounded-lg">

                    <h1 class="text-3xl font-bold  capitalize text-[#902c7e]"> <?= $row["name"] ?></h1>
                    <h1 class="text-xl text-gray-500"> Brand :<span> <?= $row["brand"] ?> </span></h1>
                    <h1 class="text-xl text-gray-500"> Rs. <?= $row["price"] ?></h1>


                    <form action="/bags_store/addToCart.php" method="post">
                        <input type="" name="id" value="<?= $id ?>" hidden />
                        <div>
                            <span id="rangeValue"></span> <br />
                            <input type="range" id="myRange" min="1" value="1" max="<?= $row["quantity"] ?>"
                                name="quantity" />
                        </div>
                        <button name="submit"
                            class="mt-5 bg-[#902c7e] text-white px-5 py-2 border-2 border-[#902c7e] hover:bg-white hover:text-[#902c7e] duration-300 rounded-lg capitalize">
                            add to cart
                        </button>
                    </form>
                </div>
                <div>
                    <?php
recommend($_GET["id"]);
?>
                </div>

                <script>
                    const rangeSlider = document.getElementById("myRange");
                    const rangeValue = document.getElementById("rangeValue");

                    rangeValue.textContent = rangeSlider.value;

                    rangeSlider.addEventListener("input", function () {
                        rangeValue.textContent = this.value;
                    });
                </script>

            </div>
        </div>

    </body>

    </html>


    <?php
} else {
    echo "Parameter id not found in the URL.";
}
?>