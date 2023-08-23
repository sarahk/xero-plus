$(document).ready(function () {

  $(document).ajaxStart(function () {
    $("#modalSpinner").show();
  });
  $(document).ajaxStop(function () {
    $("#modalSpinner").hide();
  });

  $('#cabinSingle').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget) // Button that triggered the modal
    var key = button.data('key') // Extract info from data-* attributes
    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.

    $.getJSON("/json.php?endpoint=Cabins&action=Single&key=" + key, function (data) {

      $('#cabinnumber').val(data.cabinnumber);
      $('#cabinstyle').val(data.style);
      $('#cabinstatus').html(data.status);
      $('#lastupdated').html(data.lastupdated);


      $('#cabinSingleLabel').text('Contact: ' + data.cabinnumber);
    });

    //var modal = $(this);
    //modal.find('.modal-title').text('Contact: ' + $('#modalContactName').text());

  });
});