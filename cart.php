<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="/bags_store/index.css" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
   <script src="https://cdn.tailwindcss.com"></script>
   <title>Document</title>
</head>

<body>
   <div class="container">
      <?php
      include ("./components/navbar.php");
      ?>
      <div class=" space-y-5">
         <h1 class="text-center font-bold text-3xl mt-5 text-[#902c7e]"> Shopping Cart </h1>

         <div class="shadow-lg mx-auto max-w-[800px] px-5 py-10">
            <?php
            include ("config.php");
            $total = 0;
            if (!isset($_SESSION["uid"])) {
               header("location:login.php");
            } else {
               $uid = $_SESSION["uid"];
               // echo $uid;
               $result = $conn->query("SELECT *,c.quantity as cart_quantity from cart c INNER JOIN products p ON c.bid = p.id join product_name_price pnp on pnp.id=p.pnp where uid=$uid;");
               if ($result->num_rows == 0) {
                  ?>
                  <div class="text-center space-y-8">
                     <p class="text-3xl text-gray-500 font-bold">No Items in the cart</p>

                     <div><a href="./pages/shop.php"
                           class="px-8 py-2 capitalize border-2 rounded border-[#902c7e] text-[#902c7e] hover:bg-[#902c7e] hover:text-white duration-300 hover:shadow">
                           View Products</a></div>
                  </div>
                  <?php
               } else {

                  ?>
                  <table class="table-auto w-full">
                     <thead>
                        <tr>
                           <th class="px-4 py-2"> Images</th>
                           <th class="px-4 py-2">Description</th>
                           <th class="px-4 py-2">quantity</th>
                           <th class="px-4 py-2">price</th>
                        </tr>
                     </thead>
                     <tbody>




                        <?php

                        while ($row = mysqli_fetch_assoc($result)) {
                           $total += $row["cart_quantity"] * $row["price"];
                           ?>
                           <tr>
                              <td class="border px-4 py-2 text-center"><img src="<?= getImageUrl($row['image']) ?>"
                                    class="w-[150px] h-[150px]" /></td>
                              <td class="border px-4 py-2 text-center"><?= $row["name"] ?></td>
                              <td class="border px-4 py-2 text-center"><?= $row["cart_quantity"] ?></td>
                              <td class="border px-4 py-2 text-center"><?= $row["price"] ?></td>
                           </tr>
                           <?php

                        }



                        ?>

                     </tbody>
                  </table>
                  <div class="flex flex-col items-end mt-8 space-y-5">
                     <div class="w-[200px] border-2 flex justify-between px-5">
                        <div class="capitalize text-gray-500">
                           price
                        </div>
                        <div class="font-bold">
                           <?= $total ?>
                        </div>
                     </div>

                     <a class="w-[200px] capitalize border-2 rounded text-center border-[#902c7e] text-[#902c7e] hover:bg-[#902c7e] hover:text-white duration-300 hover:shadow"
                        href="index.php">
                        buy more bags
                     </a>


                     <form action="checkout.php" method="post">

                        <button
                           class="w-[200px] capitalize border-2 rounded border-[#902c7e] text-[#902c7e] hover:bg-[#902c7e] hover:text-white duration-300 hover:shadow ">
                           checkout
                        </button>
                     </form>
                  <?php }
            } ?>
            </div>
         </div>
      </div>
   </div>

</body>

</html>