<?php
    require "./header.php";
    if($user_account == "citizen"){
        header("Location: ./user_request.php");
        die;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./css/tailwind.css">
    <link rel="stylesheet" href="./css/icons.css">
    <link rel="stylesheet" href="./css/main_style.css">
</head>
<body class="bg-gray-100">
    <div class="flex justify-end md:flex-row w-full gap-2">
        <?php include "./architecture/left-sidebar.php" ?>

        <div class="main-dashboard w-full md:w-4/5 bg-white p-4 md:pr-6 border pt-6 top-0">
            <div class="header text-md text-gray-500 font-medium">Certifications</div>

            <div class="citizens border rounded p-2 w-full mt-4 overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-gray-100 text-sm text-gray-700">
                        <tr>
                            <th class="p-2">Full Names</th>
                            <th class="p-2">Email</th>
                            <th class="p-2">Province</th>
                            <th class="p-2">Request</th>
                            <th class="p-2">Status</th>
                            <th class="p-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $query = "SELECT * FROM requests where request_type = 'certificate'";
                            $result = mysqli_query($con, $query);

                            if($result -> num_rows > 0){
                                while($row = $result->fetch_assoc()){
                                    $requesting_user_id = $row['user_id'];
                                    $request_id = $row['request_id'];
                                    $request_type = $row['request_type'];
                                    $request_status = $row['request_status'];

                                    // get user data
                                    $qry = "SELECT * FROM users WHERE user_id = '$requesting_user_id' LIMIT 1";
                                    $res = mysqli_query($con, $qry);
                                    if($res -> num_rows > 0){
                                        while($row = $res -> fetch_assoc()){
                                            $username = $row['user_name'];
                                            $email = $row['email'];
                                            $province = $row['province'];
                        ?>
                        <tr class="hover:bg-gray-100 transition-all">
                            <td class="p-2 flex items-center gap-2">
                                <img src="./Images/default-pp.png" class="w-8 h-8 object-cover rounded-full" alt="">
                                <div>
                                    <div class="text-sm text-gray-800"> <?php echo $username ?> </div>
                                </div>
                            </td>
                            <td class="text-sm text-gray-800 p-2"> <?php echo $email ?> </td>
                            <td class="text-sm text-gray-800 p-2"> <?php echo $province ?> </td>
                            <td class="text-sm text-gray-800 p-2"> <?php echo $request_type ?> </td>
                            <td class="text-sm text-gray-800 p-2"> <?php echo $request_status ?> </td>
                            <td class="text-sm text-gray-800 p-2">
                                <a href="./update_request.php?id=<?php echo $request_id ?>">
                                    <button class="p-2 px-4 rounded text-blue-700 focus:bg-gray-100 border-none font-medium text-sm">Update</button>
                                </a>
                            </td>
                        </tr>
                        <?php
                                        }
                                    }
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
