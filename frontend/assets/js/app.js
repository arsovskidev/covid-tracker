$(function () {
  function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  // Monthly data chart.
  const monthly_chart_config = {
    chart: {
      id: "monthly_chart",
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
      text: "Loading...",
    },
  };

  const monthly_chart = new ApexCharts(
    document.querySelector("#monthly-chart"),
    monthly_chart_config
  );

  monthly_chart.render();

  function updateMontlyChart(data) {
    // Monthly chart update.
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

    monthly_chart.updateOptions({
      xaxis: {
        categories: dates,
        labels: {
          show: false,
        },
      },
    });

    monthly_chart.updateSeries([
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
    url: "../backend/endpoints/api.php?list-countries",
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
    url: "../backend/endpoints/api.php?list-statistics=global",
    dataType: "json",
    async: false,
    success: function (stats) {
      updateMontlyChart(stats["monthly-chart"]);

      $("#total-confirmed").text(
        numberWithCommas(stats["today"]["total"]["total_confirmed"])
      );
      $("#total-active").text(
        numberWithCommas(stats["today"]["total"]["total_active"])
      );
      $("#total-recovered").text(
        numberWithCommas(stats["today"]["total"]["total_recovered"])
      );
      $("#total-deaths").text(
        numberWithCommas(stats["today"]["total"]["total_deaths"])
      );

      $("#new-active").text(
        numberWithCommas(stats["today"]["new"]["new_active"])
      );
      $("#new-deaths").text(
        numberWithCommas(stats["today"]["new"]["new_deaths"])
      );
      $("#new-recovered").text(
        numberWithCommas(stats["today"]["new"]["new_recovered"])
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
        "../backend/endpoints/api.php?list-statistics=" +
        countriesOptionSelectedValue,
      dataType: "json",
      async: false,
      success: function (stats) {
        if (stats != 400) {
          country = stats;
          updateMontlyChart(stats["monthly-chart"]);
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
      $("#total-confirmed").text(
        numberWithCommas(country["today"]["total"]["total_confirmed"])
      );
      $("#total-active").text(
        numberWithCommas(country["today"]["total"]["total_active"])
      );
      $("#total-recovered").text(
        numberWithCommas(country["today"]["total"]["total_recovered"])
      );
      $("#total-deaths").text(
        numberWithCommas(country["today"]["total"]["total_deaths"])
      );

      $("#new-active").text(
        numberWithCommas(country["today"]["new"]["new_active"])
      );
      $("#new-deaths").text(
        numberWithCommas(country["today"]["new"]["new_deaths"])
      );
      $("#new-recovered").text(
        numberWithCommas(country["today"]["new"]["new_recovered"])
      );

      alertify.message(
        "Filtering data for " + countriesOptionSelectedText + "!",
        5
      );
    } else if (timeOptionSelected == "Monthly") {
      alertify.error("Under development for Monthly Data!");
    } else {
      alertify.error("Under development for 3 Months Data!");
    }
  });

  new gridjs.Grid({
    columns: [
      { id: "slug", name: "Country" },
      { id: "total_confirmed", name: "Total Confirmed" },
      { id: "total_deaths", name: "Total Deaths" },
      { id: "total_recovered", name: "Total Recovered" },
      { id: "total_active", name: "Total Active" },
    ],
    data: () =>
      allCountriesStatistics.map((country) => [
        country.country,
        country.total_confirmed,
        country.total_deaths,
        country.total_recovered,
        country.total_active,
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
