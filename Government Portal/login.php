<?php
 session_start();
    include("dbconnect.php");
    include("functions.php");

    $error = "";
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $email = $_POST['email'];
        $password = $_POST['password'];

        if(!empty($email) && !empty($password) && !is_numeric($email)){

            // read to db
            $query = "select * from users where email = '$email'";
        
            $result = mysqli_query($con,$query);

            if($result){
                if($result && mysqli_num_rows($result) > 0){
                    $user_data = mysqli_fetch_assoc($result);
                    if(password_verify($password,$user_data['password'])){
                        $_SESSION['user_id'] = $user_data['user_id'];
                        header("Location: index.php");
                        die;
                    }else{
                        $error = '<p class="error-bar">Wrong password, please try again</p>';
                    }
                 }else{
                    $error = '<p class="error-bar">Wrong Email, please try again.</p>';
                 }
               }
               
          
        }else{
            $error = '<p class="error-bar">Please enter valid information in all fields.</p>';
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="./css/tailwind.css">
</head>
<style>
    body{
        background-image: url("Images/people.jpg");
        background-size: cover;
    }
</style>
<body class=" flex items-center justify-center min-h-screen p-4">

    <?php include "./architecture/top_bar.php"?>

    <div id="box" class="form-box bg-white p-8 rounded-xl shadow-xl w-full max-w-md md:max-w-lg lg:max-w-xl">
        <div class="log-text text-3xl font-bold text-center mb-6 text-green-800">Login</div>
        <?php echo $error ?>
        <form action="" method="post" class="space-y-6">
            <div>
                <label for="email" class="block text-green-700 font-medium">Email</label>
                <input type="text" name="email" id="email" placeholder="Enter your email address" class="w-full px-4 py-3 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition">
            </div>
            <div>
                <label for="password" class="block text-green-700 font-medium">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" class="w-full px-4 py-3 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition">
            </div>
            <input type="submit" value="Login" 
                   class="w-full bg-green-600 text-white font-semibold py-3 rounded-lg hover:bg-green-700 transition cursor-pointer">
            <div class="text-center">
                <a href="reset_password.php" class="text-green-500 hover:underline">Forgot password? Reset now.</a>
            </div>
            <p class="text-center text-green-600">Don't have an account? 
                <a href="signup.php" class="text-green-500 hover:underline">Sign Up here.</a>
            </p>
        </form>
    </div>

</body>
</html>
