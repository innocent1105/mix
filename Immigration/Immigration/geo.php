<?php
if (isset($_GET['proxy']) && $_GET['proxy'] === 'country') {
    // PHP proxy mode: fetch country info from amCharts
    header("Content-Type: application/json");
    echo file_get_contents("https://www.amcharts.com/tools/country/?v=xz6Z");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zambia Map</title>
  <style>
    #chartdiv {
      width: 100%;
      height: 500px;
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
<body>

  <div id="chartdiv" class=" "></div>


  <div id="province-modal" class=" w-full absolute p-4 flex justify-center">

    <div class=" province-container w-full md:px-72 bg-white rounded-md">
      <div class="province-img p-4 px-8 rounded-md rounded-b">
        <div class="flex justify-between">

          <div class="province-name">
            <div class="header-text text-2xl text-white font-medium"><span id="province-name-span1">Southern</span> Province</div>
          </div>
          
          <div id="close-province-modal-btn" class="close-btn text-white hover:text-gray-800 hover:bg-white cursor-pointer shadow-md p-1 px-2 border rounded-full">
            <i class="si-close"></i>
          </div>
        </div>

        <div class=" text-white text-md">
            The <span id="province-name-span2">Southern</span> province immigration offices.
        </div>
        
      </div>

    <div id="loader" class="flex justify-center  items-center py-10 hidden">
      <div class="w-10 h-10 border-4 border-green-500 border-dashed rounded-full animate-spin"></div>
    </div>


    
    <div id="offices-container" class="offices border p-4 flex flex-wrap gap-4 max-h-[32rem] overflow-y-auto bg-white shadow-inner justify-center sm:justify-start">
    
    </div>


  
      
    </div>
  </div>


  <script src="./js/jquery.js"></script>

<script>
  let provinceModal = document.getElementById("province-modal");
  let modalCloseBtn = document.getElementById("close-province-modal-btn");
  let zambianMap = document.getElementById("chartdiv");
  let provinceNameSpan1 = document.getElementById("province-name-span1");
  let provinceNameSpan2 = document.getElementById("province-name-span2");

  modalCloseBtn.addEventListener("click", ()=>{
    provinceModal.style.display = "none";
    zambianMap.style.display = "block";
  })








  am5.ready(function() {
    var root = am5.Root.new("chartdiv");

    root.setThemes([
      am5themes_Animated.new(root)
    ]);

    var chart = root.container.children.push(am5map.MapChart.new(root, {
      panX: "rotateX",
      projection: am5map.geoMercator(),
      layout: root.horizontalLayout
    }));

    // Fixed line using local PHP proxy
    am5.net.load("geo.php?proxy=country", chart).then(function (result) {
      var geo = am5.JSONParser.parse(result.response);
      loadGeodata(geo.country_code);
    });

    var polygonSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {
      calculateAggregates: true,
      valueField: "value"
    }));

    polygonSeries.mapPolygons.template.setAll({
      tooltipText: "{name}",
      interactive: true
    });

    polygonSeries.mapPolygons.template.states.create("hover", {
      fill: am5.color(0x677935)
    });

    polygonSeries.set("heatRules", [{
      target: polygonSeries.mapPolygons.template,
      dataField: "value",
      min: am5.color(0x8ab7ff),
      max: am5.color(0x25529a),
      key: "fill"
    }]);

    polygonSeries.mapPolygons.template.events.on("pointerover", function(ev) {
      heatLegend.showValue(ev.target.dataItem.get("value"));
    });

    function loadGeodata(country) {
      var defaultMap = "usaLow";
      if (country == "US") {
        chart.set("projection", am5map.geoAlbersUsa());
      } else {
        chart.set("projection", am5map.geoMercator());
      }

      var currentMap = defaultMap;
      var title = "";
      if (am5geodata_data_countries2[country] !== undefined) {
        currentMap = am5geodata_data_countries2[country]["maps"][0];
        if (am5geodata_data_countries2[country]["country"]) {
          title = am5geodata_data_countries2[country]["country"];
        }
      }

      am5.net.load("https://cdn.amcharts.com/lib/5/geodata/json/" + currentMap + ".json", chart).then(function (result) {
        var geodata = am5.JSONParser.parse(result.response);
        var data = [];
        for (var i = 0; i < geodata.features.length; i++) {
          data.push({
            id: geodata.features[i].id,
            value: Math.round(Math.random() * 10000)
          });
        }

        polygonSeries.set("geoJSON", geodata);
        polygonSeries.data.setAll(data);
      });

      chart.seriesContainer.children.push(am5.Label.new(root, {
        x: 5,
        y: 5,
        text: title,
        background: am5.RoundedRectangle.new(root, {
          fill: am5.color(0xffffff),
          fillOpacity: 0.2
        })
      }));
    }

    var heatLegend = chart.children.push(
      am5.HeatLegend.new(root, {
        orientation: "vertical",
        startColor: am5.color(0x8ab7ff),
        endColor: am5.color(0x25529a),
        startText: "Lowest",
        endText: "Highest",
        stepCount: 5
      })
    );

    heatLegend.startLabel.setAll({
      fontSize: 12,
      fill: heatLegend.get("startColor")
    });

    heatLegend.endLabel.setAll({
      fontSize: 12,
      fill: heatLegend.get("endColor")
    });

    polygonSeries.events.on("datavalidated", function () {
      heatLegend.set("startValue", polygonSeries.getPrivate("valueLow"));
      heatLegend.set("endValue", polygonSeries.getPrivate("valueHigh"));
    });

   
    // ajax
     function loadData(province) {
      $("#loader").removeClass("hidden"); // Show loader
      $("#offices-container").empty(); // Optional: clear container early

      $.ajax({
        type: "POST",
        url: "./includes/getData.php",
        data: { "province": province },
        success: (response) => {
          let offices = JSON.parse(response);
          let container = $("#offices-container");
          container.empty(); // Clear again just in case

          if (offices.length === 0) {
            document.getElementById("offices-container").innerHTML = `
              <div class="text-center text-gray-500 w-full py-10">
                <p class="text-lg">No offices found for this province.</p>
              </div>
            `;
          } else {
            offices.forEach(office => {
            let officeHTML = `
            <div class="office w-full sm:w-[48%] md:w-[30%] lg:w-[23%] p-4 bg-gray-50 rounded-xl border border-gray-200 hover:border-green-400 hover:shadow-md transition-all duration-200 cursor-pointer">
              <div class="office-name font-semibold text-lg text-gray-800">${office.name}</div>
              <div class="office-description text-gray-500 text-sm mt-1">${office.description}</div>

              <div class="flex items-center gap-3 mt-5">
                <div class="text-green-500 bg-green-100 p-2 rounded">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" 
                      viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 
                          00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </div>
                <div>
                  <div class="font-medium text-gray-800">Email</div>
                  <div class="text-sm text-gray-500">${office.email}</div>
                </div>
              </div>

              <div class="flex items-center gap-3 mt-2">
                <div class="text-blue-500 bg-blue-100 p-2 rounded">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 5h2l3.6 7.59-1.35 2.44A1 1 0 008 17h8a1 1 0 00.95-.68l3-8A1 1 0 
                          0019 7H7" />
                  </svg>
                </div>
                <div>
                  <div class="font-medium text-gray-800">Phone</div>
                  <div class="text-sm text-gray-500">${office.phone}</div>
                </div>
              </div>
            </div>
          `;

            container.append(officeHTML);
          });
          }


          

          $("#loader").addClass("hidden"); // Hide loader
        },
        error: (e) => {
          console.error("Error:", e);
          $("#loader").addClass("hidden"); // Hide loader even on error
        }
      });
    }

        

        polygonSeries.mapPolygons.template.setAll({
          tooltipText: "{name}",
          interactive: true
        });

        // 🔥 Add click event listener
        polygonSeries.mapPolygons.template.events.on("click", function(ev) {
          var data = ev.target.dataItem.dataContext;
          provinceModal.style.display = "block";
          zambianMap.style.display = "none";

          provinceNameSpan1.innerHTML = data.name;
          provinceNameSpan2.innerHTML = data.name;



          loadData(data.name);




        });






      }); // end am5.ready()
  </script>
</body>
</html>
