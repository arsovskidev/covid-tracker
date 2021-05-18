$(function () {
  function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  $.ajax({
    type: "GET",
    url: "../backend/endpoints/list-countries.php",
    dataType: "json",
    success: function (countries) {
      $.each(countries, function (index, value) {
        let node = `<option value="${value["slug"]}">${value["country"]}</option>`;
        $("#countries-option").append(node);
      });
    },
  });

  $.ajax({
    type: "GET",
    url: "../backend/endpoints/list-summary.php?country=global",
    dataType: "json",
    success: function (summary) {
      $("#total-confirmed").text(numberWithCommas(summary["total_confirmed"]));

      $("#total-active").text(
        numberWithCommas(
          summary["total_confirmed"] -
            summary["total_deaths"] -
            summary["total_recovered"]
        )
      );
      $("#new-active").text(numberWithCommas(summary["new_confirmed"]));

      $("#total-deaths").text(numberWithCommas(summary["total_deaths"]));
      $("#new-deaths").text(numberWithCommas(summary["new_deaths"]));

      $("#total-recovered").text(numberWithCommas(summary["total_recovered"]));
      $("#new-recovered").text(numberWithCommas(summary["new_recovered"]));

      alertify.message("Last synced on " + summary["date"], 5);
    },
  });

  $("#apply-filter").on("click", function () {
    timeOptionSelected = $("#time-option option").filter(":selected").text();
    countriesOptionSelectedValue = $("#countries-option option")
      .filter(":selected")
      .val();
    countriesOptionSelectedText = $("#countries-option option")
      .filter(":selected")
      .text();

    if (timeOptionSelected == "Today") {
      $.ajax({
        type: "GET",
        url:
          "../backend/endpoints/list-summary.php?country=" +
          countriesOptionSelectedValue,
        dataType: "json",
        success: function (summary) {
          if (summary["total_confirmed"]) {
            $("#total-confirmed").text(
              numberWithCommas(summary["total_confirmed"])
            );

            $("#total-active").text(
              numberWithCommas(
                summary["total_confirmed"] -
                  summary["total_deaths"] -
                  summary["total_recovered"]
              )
            );
            $("#new-active").text(numberWithCommas(summary["new_confirmed"]));

            $("#total-deaths").text(numberWithCommas(summary["total_deaths"]));
            $("#new-deaths").text(numberWithCommas(summary["new_deaths"]));

            $("#total-recovered").text(
              numberWithCommas(summary["total_recovered"])
            );
            $("#new-recovered").text(
              numberWithCommas(summary["new_recovered"])
            );

            alertify.message(
              "Filtering data for " + countriesOptionSelectedText + "!",
              5
            );
          } else {
            $("#total-confirmed").text("-");
            $("#total-active").text("-");
            $("#new-active").text("-");
            $("#total-deaths").text("-");
            $("#new-deaths").text("-");
            $("#total-recovered").text("-");
            $("#new-recovered").text("-");

            alertify.error("No data for this country!");
          }
        },
      });
    } else if (timeOptionSelected == "Monthly") {
      alertify.error("Under development for Monthly Data!");
    } else {
      alertify.error("Under development for 3 Months Data!");
    }
  });

  // Chart.
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
