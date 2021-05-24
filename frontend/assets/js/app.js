$(function () {
  function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  // Daily data chart.
  const daily_chart_config = {
    chart: {
      id: "daily_chart",
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

  const daily_chart = new ApexCharts(
    document.querySelector("#daily-chart"),
    daily_chart_config
  );

  daily_chart.render();

  function updateMontlyChart(data) {
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

    daily_chart.updateOptions({
      xaxis: {
        categories: dates,
        labels: {
          show: false,
        },
      },
    });

    daily_chart.updateSeries([
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

  let allCountriesStatistics = [];

  $.ajax({
    type: "GET",
    url: "../backend/api/v1.php?list-countries",
    dataType: "json",
    async: false,
    success: function (countries) {
      allCountriesStatistics = countries["statistics"];
      console.log(allCountriesStatistics);
      $.each(countries["countries"], function (index, value) {
        let node = `<option value="${value["slug"]}">${value["country"]}</option>`;
        $("#countries-option").append(node);
      });
    },
  });

  $.ajax({
    type: "GET",
    url: "../backend/api/v1.php?list-statistics=global",
    dataType: "json",
    async: false,
    success: function (stats) {
      updateMontlyChart(stats["daily-chart"]);

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

  $("#apply-filter").on("click", function () {
    timeOptionSelected = $("#time-option option").filter(":selected").text();
    countriesOptionSelectedValue = $("#countries-option option")
      .filter(":selected")
      .val();
    countriesOptionSelectedText = $("#countries-option option")
      .filter(":selected")
      .text();

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
          updateMontlyChart(stats["daily-chart"]);

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

    if (timeOptionSelected == "Today") {
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
      $("#new-active").text(
        numberWithCommas(country["summary"]["new"]["monthly"]["new_active"])
      );
      $("#new-deaths").text(
        numberWithCommas(country["summary"]["new"]["monthly"]["new_deaths"])
      );
      $("#new-recovered").text(
        numberWithCommas(country["summary"]["new"]["monthly"]["new_recovered"])
      );
    } else {
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
  });

  new gridjs.Grid({
    columns: [
      { id: "rank", name: "Rank" },
      { id: "slug", name: "Country" },
      { id: "total_confirmed", name: "Total Confirmed" },
      { id: "total_deaths", name: "Total Deaths" },
      { id: "total_recovered", name: "Total Recovered" },
      { id: "total_active", name: "Total Active" },
    ],
    data: () =>
      allCountriesStatistics.map((country) => [
        country.rank,
        country.country,
        numberWithCommas(country.total_confirmed),
        numberWithCommas(country.total_deaths),
        numberWithCommas(country.total_recovered),
        numberWithCommas(country.total_active),
      ]),
    search: {
      enabled: true,
    },
    pagination: {
      enabled: true,
      limit: 10,
      summary: true,
    },
  }).render(document.getElementById("countries-grid"));
});
