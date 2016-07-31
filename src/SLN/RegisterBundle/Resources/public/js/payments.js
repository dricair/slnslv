function setup_payments() {
  // Rest functions
  inscription = $("#inscription_table");

  // For each inscription, add the possibility to toggle the value (Missing or not)
  $("td.inscription_toggle").on('click', function(e) {
    value = 0;
    payment = false;
    id = $(this).parent().data('licensee');
    if (typeof id !== "undefined") {
      restFunction = inscription.data("licensee-function");
    } else {
      id = $(this).parent().data('user');
      value = $(this).parent().data('value');
      payment = true;
      restFunction = inscription.data("payment-function");
    }

    console.log('toggle, index=' + $(this).data('index') + ', missing=' + $(this).data('missing') + ', id=' + id);

    icon = $(this).find('span.glyphicon');

    old_missing = $(this).data('missing');
    missing = old_missing ? 0 : 1;
    if (payment) {
      if (!missing && value != 0 && !confirm('Etes-vous sûr de vouloir indiquer que le paiement est complet ?') ||
           missing && value == 0 && !confirm('Etes-vous sûr de vouloir indiquer que le paiement est incomplet ?'))
        return false;
    }

    icon.removeClass("glyphicon-remove glyphicon-ok text-danger text-success");
    $(this).data('missing', missing);
    url = restFunction.replace('__id__', id)
                      .replace('__inscr__', $(this).data('index'))
                      .replace('__missing__', missing);
    console.log('REST function: ' + url);
    icon.addClass('glyphicon-refresh glyphicon-refresh-animate');

    $.ajax({
      url: url,
      type: 'GET'
    })
    .done(function(data) {
      console.log('success, data=' + data);
      missing = data;
    })
    .fail(function(data) {
      console.log('failure');
      missing = old_missing;
    });

    icon.removeClass('glyphicon-refresh glyphicon-refresh-animate');
    icon.addClass(missing ? "text-danger glyphicon-remove" : "text-success glyphicon-ok");
  });

}
