$(function () {
  let activeCasesChartID = document
    .getElementById("activeCasesChart")
    .getContext("2d");

  let deathsChartID = document.getElementById("deathsChart").getContext("2d");

  let activeCasesChart = new Chart(activeCasesChartID, {
    type: "line",
    data: {
      labels: [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
      ],
      datasets: [
        {
          label: "Active Cases",
          backgroundColor: "#8375ff",
          borderColor: "#8375ff",
          fill: false,
          data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        },
      ],
    },
    options: {
      legend: {
        display: false,
      },
      tooltips: {
        callbacks: {
          label: function (tooltipItem) {
            return tooltipItem.yLabel;
          },
        },
      },
      responsive: true,
    },
  });

  let deathsChart = new Chart(deathsChartID, {
    type: "line",
    data: {
      labels: [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
      ],
      datasets: [
        {
          label: "Total Deaths",
          backgroundColor: "#000000",
          borderColor: "#000000",
          fill: false,
          data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        },
      ],
    },
    options: {
      legend: {
        display: false,
      },
      tooltips: {
        callbacks: {
          label: function (tooltipItem) {
            return tooltipItem.yLabel;
          },
        },
      },
      responsive: true,
    },
  });

  setTimeout(function (e) {
    alertify.message("Synced on May 6, 2021 at 16:30", 0);
  }, 1000);

  let countriesOption = $("#countries-option");

  $.ajax({
    type: "GET",
    url: "../backend/requests/getCountires.php",
    dataType: "json",
    cache: false,
    success: function (countries) {
      $.each(countries, function (index, value) {
        console.log(value);
        let node = `<option value="${value["slug"]}">${value["country"]}</option>`;
        countriesOption.append(node);
      });
    },
  });
});
