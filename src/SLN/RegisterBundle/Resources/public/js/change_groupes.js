function setup_groupes() {
  $('form.ajax_form > select').on('change', function(e) {
    
    var form = $(this).parent();

    $.ajax({
      url: form.attr('action'),
      type: form.attr('method'),
      dataType: 'json',
      data: JSON.stringify({new_groupe: $(this).val()})
    })
    .done(function(data) {
      console.log("success, data=" + data);
    })
    .fail(function(data) {
      console.log("failure, data=" + data);
    });
  });
}
