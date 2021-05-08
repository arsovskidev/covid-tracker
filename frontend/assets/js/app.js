$(function () {
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
        let node = `<option value="${value["slug"]}">${value["country"]}</option>`;
        countriesOption.append(node);
      });
    },
  });
});
