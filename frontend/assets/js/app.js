$(function () {
  setTimeout(function (e) {
    // alertify.message("Synced on May 6, 2021 at 16:30", 0);
  }, 1000);

  let countriesOption = $("#countries-option");

  $.ajax({
    type: "GET",
    url: "../backend/requests/getCountires.php",
    dataType: "json",
    success: function (countries) {
      $.each(countries, function (index, value) {
        let node = `<option value="${value["slug"]}">${value["country"]}</option>`;
        countriesOption.append(node);
      });
    },
  });

  var options = {
    chart: {
      height: 350,
      type: "line",
    },
    stroke: {
      curve: "smooth",
    },
    dataLabels: {
      enabled: false,
    },
    colors: ["#8375ff", "#fff67a", "#000000", "#a0ff7a"],

    series: [
      {
        name: "Total",
        data: [10, 20, 30, 0, 50, 60, 70, 80, 90, 100, 110, 120],
      },
      {
        name: "Active",
        data: [20, 30, 40, 50, 0, 70, 80, 90, 100, 110, 120, 130],
      },
      {
        name: "Deaths",
        data: [30, 40, 50, 60, 70, 0, 90, 100, 110, 120, 130, 140],
      },
      {
        name: "Recovered",
        data: [40, 50, 60, 70, 80, 90, 0, 110, 120, 130, 140, 150],
      },
    ],
    xaxis: {
      categories: [
        "1 May",
        "2 May",
        "3 May",
        "4 May",
        "5 May",
        "6 May",
        "7 May",
        "8 May",
        "9 May",
        "10 May",
        "11 May",
        "12 May",
      ],
    },
  };

  var chart = new ApexCharts(document.querySelector("#chart"), options);

  chart.render();
});
