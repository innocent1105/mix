<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'];
            $user_role = $user['user_role'] ?? 'user';
            $_SESSION['user_role'] = $user_role;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>GRZ - Immigration Dept</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- <script src="https://cdn.tailwindcss.com"></script> -->
  <link rel="stylesheet" href="./css/tailwind.css">
  <script>
    function toggleMenu() {
      const menu = document.getElementById("mobile-menu");
      menu.classList.toggle("hidden");
    }
  </script>
  <style>
    html {
      scroll-behavior: smooth;
    }
    .hr-txt{
      font-size: 90px;
    }

    @media (max-width: 750px) {
      .hr-txt{
        font-size: 24px;
      }
    }
    .w-value-section{
      background-image: url("./img/cloud.jpg");
      background-size: cover;
    }
  </style>

</head>
<body class="bg-gray-900 text-gray-100">

  <header class="bg-gray-800 shadow-md fixed top-0 left-0 right-0 w-full z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <div class="logo flex justify-between gap-4">
            <img src="./img/logo_prev_ui (1).png" class=" md:w-11 md:h-10 w-5 h-4" alt="">
            <h1 class="md:text-2xl font-bold text-sm text-yellow-400 cursor-default">The Department of Immigration</h1>
        </div>
      <div class="md:hidden">
        <button onclick="toggleMenu()" class="text-white focus:outline-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      </div>
      <nav class="hidden md:flex space-x-6">
        <a href="#services" class="text-sm py-2 text-white hover:text-yellow-400">Services</a>
        <a href="#contact" class="text-sm py-2 text-white hover:text-yellow-400">Contact</a>
        <a href="./register.php" class="text-sm py-2 text-yellow-300 hover:text-yellow-400">Register</a>

        </nav>
    </div>
    <div id="mobile-menu" class="md:hidden hidden px-6 pb-4">
      <nav class="flex flex-col space-y-3">
        <a href="#services" class="text-sm text-white hover:text-yellow-400">Services</a>
        <a href="#contact" class="text-sm text-white hover:text-yellow-400">Contact</a>
        <a href="./register.php" class="text-sm py-2 text-yellow-300 hover:text-yellow-400">Register</a>

        </nav>
    </div>
  </header>

    <section class="relative w-full bg-cover bg-center bg-no-repeat text-center py-48 pt-60 md:pt-48" style="background-image: url('./provinces/flag.jpg');">
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-yellow-400 mb-4 cursor-default">The Immigration Department</h2>
            <p class="text-gray-200 text-base sm:text-lg mb-6 cursor-default">Your trusted partner in international relocation and visa services.</p>
            

        <center>
            <form action="" method="POST" class="space-y-4 md:w-72 border p-4 bg-white rounded-md">
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                <h2 class="text-2xl text-gray-700 font-bold mb-4 cursor-default">Login</h2>
                <input type="email" name="email" placeholder="Email" required class="w-full text-gray-700  border p-2 rounded">
                <input type="password" name="password" placeholder="Password" required class="w-full border text-gray-700 p-2 rounded">
                <button type="submit" class="w-full bg-gray-900 text-white p-2 rounded hover:bg-gray-800 hover:shadow-md transition-all cursor-pointer">Login</button>
                <p class="text-sm text-gray-500 text-center">No account? <a href="register.php" class="text-yellow-700">Register</a></p>
            </form>
        </center>
        


        </div>
    </section> 


    
    <!-- New Section: Mission Statement -->
    <section id="mission" class="py-16 bg-gray-900 text-center">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <h3 class="text-3xl sm:text-4xl font-bold mb-10 text-yellow-300 cursor-default">Our Mission</h3>
      <p class="text-gray-300 text-lg sm:text-xl">
        We are committed to promoting a human rights-based approach to migration control, 
        simplifying access to permits, fostering interdepartmental cooperation, engaging civil society,
        and educating communities in collaboration with relevant bodies to ensure the effective 
        application of our mandate.
    </p>
    </div>
  </section>

 <section id="services" class="services py-16 bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <h3 class="text-3xl sm:text-4xl font-bold mb-10 text-yellow-300 cursor-default">Our Services</h3>
      <div class="flex flex-col md:flex-row justify-center gap-8">
        
        <div class="p-6 bg-gray-800 cursor-pointer hover:bg-slate-900 hover:border border border-gray-800  rounded-xl shadow hover:shadow-lg transition w-full md:w-1/2">
          <h4 class="text-xl font-semibold mb-2 text-white">International labour</h4>
          <p class="text-sm text-gray-400">
            Ensuring that businesses in Zambia may employ skilled foreigners who are needed, 
            especially in sectors that are reliant on international exchanges of people 
            and personnel, as provided under any other law. Facilitating the movement of students 
            and academic staff, within the Southern African Development Community and the 
            Common Market for East and Southern Africa, for study, teaching and research.
          </p>
        </div>
  
        <div class="p-6 bg-gray-800 cursor-pointer hover:bg-slate-900 hover:border border border-gray-800  rounded-xl shadow hover:shadow-lg transition w-full md:w-1/2">
          <h4 class="text-xl font-semibold mb-2 text-white">Maintain public records</h4>
          <p class="text-sm text-gray-400">
          Maintain public records showing funds received or collected from 
            <p class="text-sm text-gray-400">Foreign countries to defray the cost of 
              repatriating illegal foreigners originating 
              from their country, as determined through 
              international relations and agreements;
            </p>
            <p class="text-sm text-gray-400"> Donors or other sources; and</p>
            <p class="text-sm text-gray-400">Other fees and fines imposed or received by the Department under the Act.</p>

          </p>
        </div>

        <div class="p-6 bg-gray-800 cursor-pointer hover:bg-slate-900 hover:border border border-gray-800 rounded-xl shadow hover:shadow-lg transition w-full md:w-1/2">
          <h4 class="text-xl font-semibold mb-2 text-white">Maintain public records</h4>
          <p class="text-sm text-gray-400">
          Maintain public records showing funds received or collected from 
            <p class="text-sm text-gray-400">Foreign countries to defray the cost of 
              repatriating illegal foreigners originating 
              from their country, as determined through 
              international relations and agreements;
            </p>
            <p class="text-sm text-gray-400"> Donors or other sources; and</p>
            <p class="text-sm text-gray-400">Other fees and fines imposed or received by the Department under the Act.</p>

          </p>
        </div>
  
      </div>
    </div>
  </section>
  

 <section id="process" class=" w-value-section py-16 bg-gray-800">
    <div class="container mx-auto md:px-12 px-6 text-center">
      <h3 class="text-3xl font-bold mb-10 text-yellow-300">What We Value</h3>

      <div class="flex flex-col md:flex-row justify-center gap-12">
        
        <div class="flex-1 p-4 py-10 border border-gray-300 hover:scale-[1.1] transition-all rounded-md cursor-default hover:border-yellow-400 hover:shadow-md">
          <div class="text-yellow-400 text-4xl font-bold mb-2">1</div>
          <h4 class="font-semibold text-lg text-white">Loyalty</h4>
          <p class="text-sm text-gray-400 mt-2">
            We ensure that our conduct, whether in a personal or official capacity, does not bring
            the department or the nation into disrepute, or damage public confedence.
          </p>
        </div>
        
        <div class="flex-1 p-4 py-10 border border-gray-300 hover:scale-[1.1] transition-all rounded-md cursor-default hover:border-yellow-400 hover:shadow-md">
          <div class="text-yellow-400 text-4xl font-bold mb-2">2</div>
          <h4 class="font-semibold text-lg text-white">Service</h4>
          <p class="text-sm text-gray-400 mt-2">
            We shall be dedicated to deliver quality services and to maintain a deep 
            sense of responsibility with utmost courtesy.
          .</p>
        </div>
        <div class="flex-1 p-4 py-10 border border-gray-300  hover:scale-[1.1] transition-all rounded-md cursor-default hover:border-yellow-400 hover:shadow-md">
          <div class="text-yellow-400 text-4xl font-bold mb-2">3</div>
          <h4 class="font-semibold text-lg text-white">Integrity</h4>
          <p class="text-sm text-gray-400 mt-2">
            We shall, at all times conduct public and private affairs with the highest level of 
            integrity, protecting public interest over personal interest.
          </p>
        </div>
      </div>
    </div>
  </section> 




  




  
  <section id="process" class="md:py-16 py-8 md:px-12 md:my-8 my-4 bg-gray-900 md:flex md:justify-cennter md:gap-10">
      <!-- <div class=" hr-txt md:px-48 px-4 font-semibold text-slate-700">
        <span class=" text-yellow-500">Confidentiality</span>  <br class=" hidden md:block">Collaboration
      </div> -->
    
      <div class="long-txt text-gray-300 md:text-lg text-sm md:py-16 md:px-44 md:mx-36 py-4 px-4 md:text-center h-auto">
        Promote a Human Rights based approach and culture in 
        respect of the migration control. 
        Ensure the dignity, safety, and freedom of all migrants are upheld, 
        with policies rooted in compassion, equality, and justice. 
        Advocate for inclusive systems that protect vulnerable individuals 
        and prioritize humanitarian values over political interests.
    </div>

  </section>


  



  <section id="contact" class="contact-section md:py-16 py-4 bg-gray-900">
    <div class="container mx-auto px-6 text-center">
      <h3 class="text-3xl font-bold mb-4 text-yellow-300">Get in Touch</h3>
      <p class="text-gray-400 mb-8">Ready to move? Fill out the form and we'll reach out.</p>
      <form class="max-w-xl mx-auto space-y-4">
        <input type="text" placeholder="Full Name"
          class="w-full p-3 border border-gray-700 rounded-md bg-gray-800 text-white" required>
        <input type="email" placeholder="Email Address"
          class="w-full p-3 border border-gray-700 rounded-md bg-gray-800 text-white" required>
        <textarea placeholder="Your Message"
          class="w-full p-3 resize-none border border-gray-700 rounded-md bg-gray-800 text-white" rows="4" required></textarea>
        <button type="submit"
          class="bg-yellow-500 text-white px-6 py-3 rounded-full transition-all hover:shadow-md hover:bg-yellow-600">Send Message</button>
      </form>
    </div>
  </section> 


  <!-- New Section: Quick Links -->
  <section id="quick-links" class="py-16 bg-gray-800 text-center">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <h3 class="text-3xl sm:text-4xl font-bold mb-10 text-yellow-300 cursor-default">Quick Links</h3>
      <div class="flex flex-col md:flex-row justify-center gap-8">
        <a href="#visa" class="bg-gray-700 hover:bg-yellow-400 hover:text-black text-white py-4 px-6 rounded-md transition-all shadow-md w-full md:w-1/3">
          Visa Applications
        </a>
        <a href="#permits" class="bg-gray-700 hover:bg-yellow-400 hover:text-black text-white py-4 px-6 rounded-md transition-all shadow-md w-full md:w-1/3">
          Permit Renewals
        </a>
        <a href="#complaints" class="bg-gray-700 hover:bg-yellow-400 hover:text-black text-white py-4 px-6 rounded-md transition-all shadow-md w-full md:w-1/3">
          Submit Complaints
        </a>
      </div>
    </div>
  </section>

  <!-- New Section: Contact -->
  <section id="contact" class="py-16 bg-gray-900 text-center">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <h3 class="text-3xl sm:text-4xl font-bold mb-10 text-yellow-300 cursor-default">Contact Us</h3>
      <p class="text-gray-400 mb-6">Need assistance? Reach out via any of the following channels.</p>
      <div class="space-y-4 text-sm text-gray-300">
        <p><strong>Email:</strong> support@immigration.gov.zm</p>
        <p><strong>Phone:</strong> +260 211 123456</p>
        <p><strong>Location:</strong> Lusaka, Zambia - Main Immigration Office</p>
      </div>
    </div>
  </section>

  <!-- New Section: Footer -->
  <footer class="bg-gray-800 py-6 text-center text-gray-400">
    <p class="text-sm">&copy; <?= date("Y") ?> Department of Immigration - GRZ. All rights reserved.</p>
  </footer>


</body>
</html>


<?php
$content = ob_get_clean();
$title = "Login";
?>



