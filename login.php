<?php
session_start();

include ("config.php");
if (isset($_SESSION["uid"]))
    header("location:index.php");
if (isset($_POST["submit"])) {
    $username = $_POST["username"];
    $password = md5($_POST["password"]);
    $sql = "SELECT * FROM users where username='$username' and password='$password'";

    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $_SESSION["uid"] = $row["id"];
        $_SESSION["username"] = $row["username"];
        $_SESSION["role"] = $row["role"];

        if($row["role"]=="admin") {
        header("Location: admin.php");
            
        } else {

            header("Location: index.php");
        }
    } else {
        $error = "Invalid Username/Password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <div class="container login">
        <div class="box form-box w-[500px] shadow-xl mx-auto px-10 py-5 mt-10 ">
            <h1 class="head text-3xl font-bold text-center mb-5 text-[#902c7e]">Log in</h1>
            <div class="wrapper">
            <p class="text-red-900 text-center"><?php if(isset($error)) echo $error;?></p>
                <form action="" method="post" class=" space-y-5">
                    <div class="field  input space-y-3">
                        <label class="text-[#902c7e]"  for="uname">Username</label> <br/>
                        <input type="text" name="username" id="uname" placeholder="Username" required class="w-full px-3 py-2 border-2 rounded-lg ">
                    </div>

                    <div class="field  input">
                        <label class="text-[#902c7e]"  for="password">Password</label>
                        <input type="password" name="password" id="password" placeholder="Password" required class="w-full px-3 py-2 border-2 rounded-lg ">
                    </div>

                    <div class="field w-full flex  justify-end">
                        <input class="border-2 px-5 py-1 border-blue-500 text-blue-500 rounded-lg hover:text-white hover:bg-blue-500 duration-300 hover:shadow-xl cursor-pointer" type="submit" class="btn" name="submit" value="Login" required >
                    </div>

                    <div class="links">
                        Don't have account? <a href="register.php" class="text-purple-900 hover:text-purple-700 hover:underline">Register</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>