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
    <script src="./js/jquery.js"></script>
    <style>
        .star:active{
            transform: scale(0.92);
        }   
    </style>
</head>
<body class="bg-gray-100 p-6 px-48"> <input type="text" id="user_id" value="<?php echo $user_id ?>" class=" hidden">

    <?php include "./architecture/top_bar2.php" ?>

    <div class=" text-2xl font-medium mt-16 mt-4">Leave a feedback</div>
    <div id="feedback-box" class="feedback-box w-full bg-white shadow rounded-md p-8 mt-4">
        <div class="header text-lg "> Rate your experience</div>
        <div class=" text-sm text-gray-500 mt-8">How would you rate the overall experience whilst using the system?</div>
        <div id="stars-tab" class="rating-stars text-md flex gap-4 mt-2">
            <!-- <div class="star p-2 border text-lg rounded-md transition-all active:scale-95 hover:text-white hover:bg-blue-500 hover:shadow cursor-pointer focus">
                <i class="si-star"></i>
            </div> -->
        </div>

        <div class=" text-sm text-gray-500 mt-8">Why have you rated your experience this way?</div>
        <textarea name="" id="comment" placeholder="Enter any suggestions, complaints or comments..." class=" resize-none rounded-md border p-4 w-1/2 mt-1 text-sm text-gray-700"></textarea>
        <br> <br>
        <button id="submit-btn" class="p-2 px-4 rounded text-white bg-green-700 focus:bg-green-700 hover:bg-green-900 font-medium text-sm">Done</button>

    </div>



    <script>
        let user_id = document.getElementById("user_id").value;
        let submit = document.getElementById("submit-btn");
        let ratingTab = document.getElementById("stars-tab");
        let feedbackBox = document.getElementById("feedback-box");
        let rating = 0;

        function rate(rating){
            let elements = document.getElementsByName("star");
            for (let x = 0; x < elements.length; x++) {
                const element = elements[x];
                if(x < rating){
                   element.style.backgroundColor = "green"; 
                   element.style.color = "white";
                }else{
                    element.style.backgroundColor = "white";
                   element.style.color = "gray";
                }
            }
        }



        function createElement(div, i){
            let element = document.createElement(div);
                element.setAttribute("class", "star p-2 border text-lg rounded-md transition-all active:scale-95 hover:text-white hover:bg-green-500 hover:shadow cursor-pointer")
                element.setAttribute("id", "element-" + i);
                element.setAttribute("name", "star");
                element.innerHTML = `<i class="si-star"></i>`;
                element.addEventListener("click", ()=>{
                    rating = i + 1;
                    rate(rating);
                });

                ratingTab.appendChild(element);
        }

        for (let i = 0; i < 5; i++) {
            createElement("div", i);
        }


        submit.addEventListener("click", ()=>{
            let comment = document.getElementById("comment").value;
            let ratingData = {
                "rating" : rating,
                "comment" : comment,
                "user_id" : user_id
            };

            ratingData = JSON.stringify(ratingData);
            // console.log(ratingData);
            $.ajax({
                type: "POST",
                url: "./process_ratings.php",
                data: {
                    "data" : ratingData
                },
                beforeSend: ()=>{
                    submit.style.backgroundColor = "gray";
                    submit.innerHTML = "submitting...";
                },
                success: (response)=>{
                    if(response == "success"){
                        feedbackBox.innerHTML = `
                            <div class="">Feedback submitted successfully.</div> <br>
                            <div class=" text-green-500"><a href="requests.php">Go to home.</a></div>
                        `;
                    }
                },
                error: (error)=>{
                    console.log(error);
                }
            })
        })





    </script>
    
</body>
</html>












