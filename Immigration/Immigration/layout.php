<!-- layout.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Immigration System' ?></title>
    <link rel="stylesheet" href="/css/tailwind.css">

    <style>
    #chartdiv {
      width: 100%;
      height: 430px;
    }
    /* .province-container{
      width: 55%;
    } */
    .province-img{
      background-image: url("./provinces/vic-falls-bridge.jpg");
      background-size: cover;
    }
    /* .office{
      width: 48%;
    } */
    /* .offices{
      height: 400px;
    } */
  </style>

  <!-- Resources -->
  <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
  <script src="https://cdn.amcharts.com/lib/5/map.js"></script>
  <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
  <script src="https://cdn.amcharts.com/lib/5/geodata/data/countries2.js"></script>
  <link rel="stylesheet" href="./css/tailwind.css">
  <link rel="stylesheet" href="./css/icons.css">
  
</head>
<body class="bg-gray-100 min-h-screen flex ">
    <div class="w-full bg-white shadow-lg rounded-xl p-6">
        <?= $content ?>
    </div>
</body>
</html>
