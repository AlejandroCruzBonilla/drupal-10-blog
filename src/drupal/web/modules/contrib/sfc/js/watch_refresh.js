(function (drupalSettings) {
  var timestamp = Math.round(new Date().getTime() / 1000);
  setInterval(function () {
    fetch(drupalSettings.sfc_watch_file)
      .then(function (response) {
        return response.text();
      })
      .then(function (text) {
        if (timestamp < parseInt(text)) {
          location.reload();
        }
      });
  }, 1000);
})(drupalSettings);
