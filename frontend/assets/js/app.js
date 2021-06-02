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
    if (array != undefined) {
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
    } else {
      console.log("No connection.");
    }
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

  let gridData = [];

  // Get all countries.
  $.ajax({
    type: "GET",
    url: "../backend/api/v1.php?list-countries",
    dataType: "json",
    async: false,
    success: function (countries) {
      $.each(countries["countries"], function (index, value) {
        let node = `<option value="${value["slug"]}">${value["country"]}</option>`;
        $("#countries-option").append(node);
      });
    },
  });

  // Get today statistics for global.
  $.ajax({
    type: "GET",
    url: "../backend/api/v1.php?list-statistics=global",
    dataType: "json",
    async: false,
    success: function (stats) {
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
    },
  });

  // Get grid data.
  $.ajax({
    type: "GET",
    url: "../backend/api/v1.php?list-grid-data",
    dataType: "json",
    async: false,
    success: function (statistics) {
      gridData = statistics;
    },
  });

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

  updateGrid(gridData["today"]);

  $("#apply-filter").on("click", function () {
    timeOptionSelected = $("#time-option option").filter(":selected").text();
    countriesOptionSelectedValue = $("#countries-option option")
      .filter(":selected")
      .val();
    countriesOptionSelectedText = $("#countries-option option")
      .filter(":selected")
      .text();

    $("#graph-country").text(
      countriesOptionSelectedText + "'s line chart showcased every 10th day."
    );

    let country = [];

    $.ajax({
      type: "GET",
      url:
        "../backend/api/v1.php?list-statistics=" + countriesOptionSelectedValue,
      dataType: "json",
      async: false,
      success: function (stats) {
        if (stats != 400) {
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

          alertify.error("No data for this country!");
        }
      },
    });

    if (country["summary"] != undefined) {
      if (timeOptionSelected == "Today") {
        updateGrid(gridData["today"]);

        $("#new-active").text(
          numberWithCommas(country["summary"]["new"]["today"]["new_active"])
        );
        $("#new-deaths").text(
          numberWithCommas(country["summary"]["new"]["today"]["new_deaths"])
        );
        $("#new-recovered").text(
          numberWithCommas(country["summary"]["new"]["today"]["new_recovered"])
        );
      } else if (timeOptionSelected == "Monthly") {
        updateGrid(gridData["monthly"]);

        $("#new-active").text(
          numberWithCommas(country["summary"]["new"]["monthly"]["new_active"])
        );
        $("#new-deaths").text(
          numberWithCommas(country["summary"]["new"]["monthly"]["new_deaths"])
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
    } else {
      console.log("There is something wrong with the response.");
    }
  });
});
