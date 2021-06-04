$(function () {
  function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  function updateDailyChart(data) {
    // Daily chart update.
    let dates = [];
    let confirmed = [];
    let active = [];
    let deaths = [];
    let recovered = [];

    $.each(data, function (index, value) {
      dates.push(value["date"]);
      confirmed.push(value["confirmed"]);
      active.push(value["active"]);
      deaths.push(value["deaths"]);
      recovered.push(value["recovered"]);
    });

    dailyChart.updateOptions({
      xaxis: {
        categories: dates,
        labels: {
          show: false,
        },
      },
    });

    dailyChart.updateSeries([
      {
        name: "Confirmed",
        data: confirmed,
      },
      {
        name: "Active",
        data: active,
      },
      {
        name: "Deaths",
        data: deaths,
      },
      {
        name: "Recovered",
        data: recovered,
      },
    ]);
  }

  function updateGrid(array) {
    grid
      .updateConfig({
        data: () =>
          array.map((country) => [
            country.rank,
            country.country,
            numberWithCommas(country.total_confirmed),
            numberWithCommas(country.total_deaths),
            numberWithCommas(country.total_recovered),
            numberWithCommas(country.total_active),
            numberWithCommas(country.new_confirmed),
            numberWithCommas(country.new_deaths),
            numberWithCommas(country.new_recovered),
            numberWithCommas(country.new_active),
          ]),
      })
      .forceRender();
  }

  const dailyChartConfig = {
    chart: {
      id: "dailyChart",
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
    series: [],
    noData: {
      text: "This may take a while...",
    },
  };

  const dailyChart = new ApexCharts(
    document.querySelector("#daily-chart"),
    dailyChartConfig
  );
  dailyChart.render();

  const grid = new gridjs.Grid({
    columns: [
      { id: "rank", name: "Rank" },
      { id: "slug", name: "Country" },
      { id: "total_confirmed", name: "Total Confirmed" },
      { id: "total_deaths", name: "Total Deaths" },
      { id: "total_recovered", name: "Total Recovered" },
      { id: "total_active", name: "Total Active" },
      { id: "new_confirmed", name: "New Confirmed" },
      { id: "new_deaths", name: "New Deaths" },
      { id: "new_recovered", name: "New Recovered" },
      { id: "new_active", name: "New Active" },
    ],
    data: [],
    search: {
      enabled: true,
    },
    pagination: {
      enabled: true,
      limit: 10,
      summary: true,
    },
    style: {
      table: {
        "font-size": "14px",
      },
    },
  }).render(document.getElementById("countries-grid"));

  let gridData = [];

  // Get all countries.
  $.ajax({
    type: "GET",
    url: "../backend/api/v1.php?list-countries",
    dataType: "json",
    success: function (countries) {
      if (countries != 500 && countries != 400) {
        $.each(countries["countries"], function (index, value) {
          let node = `<option value="${value["slug"]}">${value["country"]}</option>`;
          $("#countries-option").append(node);
        });
      } else {
        alertify.error("Internal Server Error.");
        console.log(
          "Oops!\nThis is awkward.\nWe encountered a 500 Internal Server Error."
        );
      }
    },
  });

  // Get today statistics for global.
  $.ajax({
    type: "GET",
    url: "../backend/api/v1.php?list-statistics=global",
    dataType: "json",
    success: function (stats) {
      if (stats != 500 && stats != 400) {
        updateDailyChart(stats["daily-chart"]);
        $("#graph-country").text(
          "Worldwide's line chart showcased every 7th day."
        );

        $("#total-confirmed").text(
          numberWithCommas(stats["summary"]["total"]["total_confirmed"])
        );
        $("#total-active").text(
          numberWithCommas(stats["summary"]["total"]["total_active"])
        );
        $("#total-recovered").text(
          numberWithCommas(stats["summary"]["total"]["total_recovered"])
        );
        $("#total-deaths").text(
          numberWithCommas(stats["summary"]["total"]["total_deaths"])
        );

        $("#new-active").text(
          numberWithCommas(stats["summary"]["new"]["today"]["new_active"])
        );
        $("#new-deaths").text(
          numberWithCommas(stats["summary"]["new"]["today"]["new_deaths"])
        );
        $("#new-recovered").text(
          numberWithCommas(stats["summary"]["new"]["today"]["new_recovered"])
        );

        alertify.message("Last synced on " + stats["synced"], 5);
      }
    },
  });

  // Get grid data.
  $.ajax({
    type: "GET",
    url: "../backend/api/v1.php?list-grid-data",
    dataType: "json",
    success: function (statistics) {
      if (statistics != 500 && statistics != 400) {
        gridData = statistics;
        updateGrid(gridData["today"]);
      }
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

    $("#graph-country").text(
      countriesOptionSelectedText + "'s line chart showcased every 7th day."
    );

    let country = [];

    $.ajax({
      type: "GET",
      url:
        "../backend/api/v1.php?list-statistics=" + countriesOptionSelectedValue,
      dataType: "json",
      success: function (stats) {
        if (stats != 500 && stats != 400) {
          country = stats;
          updateDailyChart(stats["daily-chart"]);

          $("#total-confirmed").text(
            numberWithCommas(country["summary"]["total"]["total_confirmed"])
          );
          $("#total-active").text(
            numberWithCommas(country["summary"]["total"]["total_active"])
          );
          $("#total-recovered").text(
            numberWithCommas(country["summary"]["total"]["total_recovered"])
          );
          $("#total-deaths").text(
            numberWithCommas(country["summary"]["total"]["total_deaths"])
          );

          if (timeOptionSelected == "Today") {
            updateGrid(gridData["today"]);

            $("#new-active").text(
              numberWithCommas(country["summary"]["new"]["today"]["new_active"])
            );
            $("#new-deaths").text(
              numberWithCommas(country["summary"]["new"]["today"]["new_deaths"])
            );
            $("#new-recovered").text(
              numberWithCommas(
                country["summary"]["new"]["today"]["new_recovered"]
              )
            );
          } else if (timeOptionSelected == "Monthly") {
            updateGrid(gridData["monthly"]);

            $("#new-active").text(
              numberWithCommas(
                country["summary"]["new"]["monthly"]["new_active"]
              )
            );
            $("#new-deaths").text(
              numberWithCommas(
                country["summary"]["new"]["monthly"]["new_deaths"]
              )
            );
            $("#new-recovered").text(
              numberWithCommas(
                country["summary"]["new"]["monthly"]["new_recovered"]
              )
            );
          } else {
            updateGrid(gridData["three_months"]);

            $("#new-active").text(
              numberWithCommas(
                country["summary"]["new"]["three_months"]["new_active"]
              )
            );
            $("#new-deaths").text(
              numberWithCommas(
                country["summary"]["new"]["three_months"]["new_deaths"]
              )
            );
            $("#new-recovered").text(
              numberWithCommas(
                country["summary"]["new"]["three_months"]["new_recovered"]
              )
            );
          }

          alertify.message(
            "Filter applied for " + countriesOptionSelectedText + "!",
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

          alertify.error("Internal Server Error!");
        }
      },
    });
  });
});
