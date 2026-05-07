<?php 
	require("header.php");
	$user_data = check_login($con);
    $main_user = $user_data['user_id'];

    $response_msg = "";

    if(isset($_GET['id'])){
        $model_id = $_GET['id'];
        // echo $model_id;
        $qry = "select * from projects where model_name = '$model_id' limit 1";
        $result = mysqli_query($con, $qry);
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $id = $row['id'];
                $user_id = $row['user_id'];
                $project_name = $row['project_name'];
                $project_description = $row['project_description'];
                $model_images = $row['model_images'];
                $privacy = $row['privacy'];
                $views = $row['views'];
                $interested = $row['interested'];
                $date_created = $row['date_created'];
                $model_images = json_decode($model_images, true);
                $model_name = $model_id;
                $project_id = $row['project_id'];
                // var_dump($model_images);
                
                // user data 
                $query = "select * from users where user_id = '$user_id' limit 1";
                $result = mysqli_query($con, $query);
                if($result->num_rows > 0){
                    while($user = $result->fetch_assoc()){
                        $username = $user['username'];
                        $profile_picture = $user['pp'];
                        $account_type = $user['account_type'];
                    }
                }
            }
        }
    }else{
        $model_name = "";
        $qry = "select * from projects limit 1";
        $result = mysqli_query($con, $qry);
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $id = $row['id'];
                $user_id = $row['user_id'];
                $project_name = $row['project_name'];
                $project_description = $row['project_description'];
                $model_images = $row['model_images'];
                $privacy = $row['privacy'];
                $views = $row['views'];
                $interested = $row['interested'];
                $date_created = $row['date_created'];
                $project_id = $row['project_id'];
                $model_name = $row['model_name'];
                $model_images = json_decode($model_images, true);
                // var_dump($model_images);
                
                
                // user data 
                $query = "select * from users where user_id = '$user_id' limit 1";
                $result = mysqli_query($con, $query);
                if($result->num_rows > 0){
                    while($user = $result->fetch_assoc()){
                        $username = $user['username'];
                        $profile_picture = $user['pp'];
                        $account_type = $user['account_type'];
                    }
                }
            }
        }
    }    
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Three</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tailwind.css">
    <link rel="stylesheet" href="css/icons.css">
    <style>
       .lol{
            width: 70%;
       }
    </style>
</head>
<body><input type="text" id="model-name" class=" hidden" value="<?php echo $model_name ?>">
<input type="text" id="p-name" class=" hidden" value="<?php echo $project_name ?>">
<input type="text" id="p-des" class=" hidden" value="<?php echo $project_description ?>">
    <!-- <?php include "./loading.php" ?> -->
    <div class="fixed w-1/4 bg-white p-2 flex gap-2">
        <div class="square w-8 h-8 bg-black rounded-md mt-3 ml-1"></div>
        <a href="./landing.html">
            <div class="logo font-medium text-2xl mt-3">
                Realism Studio
            </div>
        </a>
    </div>
    <div class="flex">
        <div class=" p-2 w-1/4 mt-14">
            <div class="tab-bar border-b mb-2 flex justify-between gap-2 p-2">
                <div id="models-btn" class="tab text-blue-700 text-sm cursor-pointer hover:text-blue-500">Models</div>
                <div id="messages-btn" class="tab text-gray-400 text-sm cursor-pointer hover:text-blue-500">Messages</div>
                <div id="notifications-btn" class="tab text-gray-400 text-sm cursor-pointer hover:text-blue-500">Updates</div>
                <div class="tab text-gray-400 text-sm cursor-pointer hover:text-blue-500">
                    <a href="./profile.php">
                        Profile
                    </a>
                </div>
                <div class="tab text-gray-400 text-sm cursor-pointer hover:text-blue-500">
                    <a href="./upload.php">
                        Upload
                    </a>
                </div>
                <div class="tab text-gray-400 text-sm cursor-pointer hover:text-blue-500">Settings</div>
       
            </div>
            
        <div id="models-tab" class="models-tab">
            <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                <a href="./profile.php?id=<?php echo $user_id ?>">
                    <div class="user flex justify-between gap-2">
                        <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                        <div class="user-info">
                            <div class=" text-gray-700 font-medium text-sm"><?php echo $username; ?></div>
                            <div class=" text-gray-400 text-xs"><?php echo $account_type; ?></div>
                        </div>
                    </div>
                </a>
                <div class="action p-2"></div>
            </div>


            <div class="mobile-project-details cursor-default transition-all px-3 mt-4">
                <div class="mobile-project-name text-sm text-gray-700"><?php echo $project_name; ?></div>
                <div id="des-area" class="mobile-project-desc transition-all text-xs text-gray-500">
                    <?php echo $project_description; ?> 
                </div>
            </div>

            <div class="model-views w-full p-2">
                <?php
                    for ($i=0; $i < count($model_images); $i++) {
                        if($i == 0){
                ?>
                    <div class="main-model-view">
                        <img src="./models/images/<?php echo $model_images[$i]; ?>" alt="">
                    </div>
                    <div class="other-views">
                <?php
                        }else{
                ?>
                        
                            <img src="./models/images/<?php echo $model_images[$i]; ?>" alt="">
                        
                <?php 
                        } 
                        
                    }
                ?>
                </div>
                
            </div>


            <div class="stats flex justify-between">

                <div class=" bg-white text-xs hover:bg-gray-200 transition-all cursor-pointer rounded-md p-1 px-2">
                    Download model
                </div>

                <?php 
                    if($user_id == $main_user){
                ?>
                    <a href="./materials.php?id=<?php echo $model_name?>">
                        <div class=" bg-white text-xs hover:bg-gray-200 transition-all cursor-pointer rounded-md p-1 px-2">
                            Add materials
                        </div>
                    </a>
                <?php
                    }
                ?>
                

                <div class=" bg-white text-xs hover:bg-gray-200 transition-all cursor-pointer rounded-md p-1 px-2">
                   View in AR
                </div>

                <div class=" bg-white text-xs hover:bg-gray-200 transition-all cursor-pointer rounded-md p-1 px-2">
                    Share
                </div>
            </div>

            <div class="comments space-y-1 h-56 overflow-y-scroll">
                <!-- AI Summary -->
                <div class="ai-summary bg-gray-100 p-2 mt-2 rounded-md">
                <p class="text-sm font-semibold">AI Summary</p>
                <p id="ai-summary" class="text-xs text-gray-700 mt-1">Thinking...</p>
                </div>

                <!-- Filters -->
                <div class="flex justify-between border items-center p-2 text-xs text-gray-600">
                <div class="space-x-2">
                    <button class="hover:underline">Most Helpful</button>
                    <button class="hover:underline">Newest</button>
                    <button class="hover:underline">Critical</button>
                </div>
                <div>
                    <button class="hover:underline">Report a comment</button>
                </div>
                </div>

                <!-- Single Comment -->
                <div class="comment border flex gap-2 items-start p-1">
                <div class="user-img w-8 h-8 bg-gray-300 rounded-full"></div>
                <div class="flex-1">
                    <div class="flex justify-between items-center">
                    <span class="text-sm font-semibold">User 1</span>
                    <span class="text-xs text-gray-500">2h ago</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">The model is fast, but it sometimes struggles with red objects in shadows. Any tips?</p>
                    <div class="flex items-center space-x-3 text-xs mt-2 text-gray-500">
                    <button class="hover:text-blue-500">👍 4</button>
                    <button class="hover:text-blue-500">🔥 1</button>
                    <button class="hover:text-blue-500">Reply</button>
                    <button class="hover:text-blue-500">Helpful</button>
                    <button class="hover:text-red-500">Report</button>
                    </div>

                    <!-- Replies -->
                    <!-- <div class="mt-3 ml-4 border-l pl-4">
                    <div class="flex items-start space-x-2">
                        <div class="user-img w-6 h-6 bg-gray-300 rounded-full"></div>
                        <div>
                        <span class="text-xs font-semibold">DevBot</span>
                        <p class="text-xs mt-1">Try adjusting your threshold or using the shadow filter plugin from the docs.</p>
                        </div>
                    </div>
                    </div> -->
                </div>
                </div>

                </div>


       
            </div>
           
            
            <div id="messages-tab" class="messages-tab hidden">
                <div class="header text-xl font-medium cursor-default">Messages</div>
                <div class="search-tab my-2">
                    <input type="text" class=" w-full border border-2 border-gray-200 p-1 px-2 text-sm rounded-md" placeholder="search in chats">
                </div>

                <div class="chats-tab overflow-y-scroll">
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm">Mwewa</div>
                                <div class=" text-gray-400 text-xs"><?php echo $account_type; ?></div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm">Francis m</div>
                                <div class=" text-gray-400 text-xs">Engineer</div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm">Elvis</div>
                                <div class=" text-gray-400 text-xs"><?php echo $account_type; ?></div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm"><?php echo $username; ?></div>
                                <div class=" text-gray-400 text-xs">Engineer</div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm"><?php echo $username; ?></div>
                                <div class=" text-gray-400 text-xs"><?php echo $account_type; ?></div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm"><?php echo $username; ?></div>
                                <div class=" text-gray-400 text-xs"><?php echo $account_type; ?></div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm"><?php echo $username; ?></div>
                                <div class=" text-gray-400 text-xs"><?php echo $account_type; ?></div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm"><?php echo $username; ?></div>
                                <div class=" text-gray-400 text-xs"><?php echo $account_type; ?></div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm"><?php echo $username; ?></div>
                                <div class=" text-gray-400 text-xs"><?php echo $account_type; ?></div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm"><?php echo $username; ?></div>
                                <div class=" text-gray-400 text-xs"><?php echo $account_type; ?></div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm"><?php echo $username; ?></div>
                                <div class=" text-gray-400 text-xs"><?php echo $account_type; ?></div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm"><?php echo $username; ?></div>
                                <div class=" text-gray-400 text-xs"><?php echo $account_type; ?></div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm"><?php echo $username; ?></div>
                                <div class=" text-gray-400 text-xs"><?php echo $account_type; ?></div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                    <div class=" cursor-pointer hover:bg-gray-100 transition-all p-2 rounded-md flex justify-between gap-2">
                        <div class="user flex justify-between gap-2">
                            <img src="./profile_pictures/<?php echo $profile_picture; ?>" class=" w-10 h-10 rounded-full border border-gray-300" alt="">
                            <div class="user-info">
                                <div class=" text-gray-700 font-medium text-sm"><?php echo $username; ?></div>
                                <div class=" text-gray-400 text-xs"><?php echo $account_type; ?></div>
                            </div>
                        </div>
                        <div class="action bg-white p-2"></div>
                    </div>
                </div>
            
            </div>

            <div id="notifications-tab" class="notifications-tab hidden">
                <div class="header text-xl font-medium cursor-default">Notifications</div>
                <!-- <div class="search-tab my-2">
                    <input type="text" class=" w-full border border-2 border-gray-200 p-1 px-2 text-sm rounded-md" placeholder="search in chats">
                </div> -->

                <div class="chats-tab mt-2 overflow-y-scroll">
                    <div class="notification border rounded-md p-2 my-1">
                        <div class="note-name text-sm font-medium">New update</div>
                        <div class="text-gray-500 text-sm">There's a new update!!! Click here.</div>
                    </div>
                    <div class="notification border rounded-md p-2 my-1">
                        <div class="note-name text-sm font-medium">New update</div>
                        <div class="text-gray-500 text-sm">There's a new update!!! Click here.</div>
                    </div>
                    <div class="notification border rounded-md p-2 my-1">
                        <div class="note-name text-sm font-medium">New update</div>
                        <div class="text-gray-500 text-sm">There's a new update!!! Click here.</div>
                    </div>
                    <div class="notification border rounded-md p-2 my-1">
                        <div class="note-name text-sm font-medium">New update</div>
                        <div class="text-gray-500 text-sm">There's a new update!!! Click here.</div>
                    </div>
                </div>
            
            </div>

           </div>



        
        
        <div class="container w-3/4 shadow-md rounded-lg m-4 cursor-pointer" id="container">
            <div id="model-loading-effect" class="w-full p-2 text-center mt-56 text-gray-500 font-medium animate-pulse"> Loading...</div>       
    </div>
    </div>

    <!-- <div class="border p-2 ml-96">

    </div> -->













    <script>
        // window.onload = function(){
        //     stopLoading();
        // }
    </script>
    <script type="importmap">
        {
            "imports": {
                "three": "https://cdn.jsdelivr.net/npm/three@latest/build/three.module.js",
                "three/addons/": "https://cdn.jsdelivr.net/npm/three@latest/examples/jsm/"
            }
        }
    </script>
    <script type="module" src="main.js"></script>
    <script type="module" src="./js/jquery.js"></script>
    <script src="./js/dashboard.js"></script>
    <script>
 

    </script>
</body>
</html>