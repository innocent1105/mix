<?php
    require "./header.php";
    $user_id = $user_data['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/tailwind.css">
    <link rel="stylesheet" href="./css/icons.css">
    <link rel="stylesheet" href="./css/main_style.css">
</head>
<body class="bg-green-50 p-5">

    <?php include "./architecture/top_bar2.php" ?>


    <div class="">
        <div class="w-full w-full bg-white p-4 md:pr-6 mt-28 border pt-6 top-0">
            <div class="header text-md text-gray-500 font-medium flex justify-between">
                <div class="text">Citizen Requests</div>
                <a href="ratings.php">Leave a feedback</a>
            </div>
            
            <div class="citizens border rounded p-2 w-full mt-4 overflow-x-auto">
                <table class="w-full min-w-max">
                    <thead class="bg-green-700 text-sm text-white">
                        <tr>
                            <th class="p-2">Full Names</th>
                            <th class="p-2">Email</th>
                            <th class="p-2">Category</th>
                            <th class="p-2">Request</th>
                            <th class="p-2">Status</th>
                            <th class="p-2">Action</th>
                        </tr>
                    </thead>
                    
                    <tbody class="border mt-2">
                        <?php 
                            $query = "SELECT * FROM requests WHERE user_id='$user_id'";
                            $result = mysqli_query($con, $query);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $requesting_user_id = $row['user_id'];
                                    $request_id = $row['request_id'];
                                    $request_type = $row['request_type'];
                                    $request_category = $row['request_category'];
                                    $request_status = $row['request_status'];

                                    // Get user data
                                    $qry = "SELECT * FROM users WHERE user_id = '$requesting_user_id' LIMIT 1";
                                    $res = mysqli_query($con, $qry);
                                    if ($res->num_rows > 0) {
                                        while ($row = $res->fetch_assoc()) {
                                            $username = $row['user_name'];
                                            $email = $row['email'];
                                            $province = $row['province'];
                        ?>
                        <tr class="hover:bg-gray-100 hover:shadow-md transition-all cursor-default text-sm text-gray-800">
                            <td class="flex p-2 mt-2 gap-2 items-center">
                                <img src="./Images/default-pp.png" class="w-8 h-8 object-cover rounded-full" alt="">
                                <span><?php echo $username ?></span>
                            </td>
                            <td class="p-2 mt-2"> <?php echo $email ?> </td>
                            <td class="text-sm text-gray-800 p-2"> 
                                <?php 
                                    if($request_category == "business"){
                                        echo "Business Permit";
                                    }else if($request_category == "construction"){
                                        echo "Construction Permit";
                                    }else if($request_category == "event"){
                                        echo "Event Permit";
                                    }else if($request_category == "birth"){
                                        echo "Birth certificate";
                                    }else if($request_category == "marriage"){
                                        echo "Marriage certificate";
                                    }else if($request_category == "police clearance"){
                                        echo "Police clearance certificate";
                                    }else{
                                        echo "";
                                    }
                                ?>
                            </td>
                            <td class="p-2 mt-2"> <?php echo $request_type ?> </td>
                            <td class="p-2 mt-2"> <?php echo $request_status ?> </td>
                            <td class="p-2 mt-2">
                                <button class="p-2 px-4 rounded text-red-500 hover:bg-gray-100 font-medium text-sm">Delete</button>
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

