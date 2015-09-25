// Setup mail form for licensee selection
function setup_mail() {
  var input_licensee_list = $("#licensee_list");
  var output_licensee_list = $("#sln_registerbundle_licenseeselecttype_licensees");
  var group_selection = $("#sln_registerbundle_licenseeselecttype_groupe");

  var add_all_button = $("#add_all");
  var add_button = $("#add");
  var del_button = $("#del");
  var del_all_button = $("#del_all");

  // Enable/Disable the Add buttons depending on the number of items or the selection
  // of the input list
  input_licensee_list.on('change', function(e) {
    console.log("input_list.change, options=" + $(this).find("option").length + ", selected=" + $(this).find("option:selected").length);
    if ($(this).find("option").length == 0) add_all_button.attr("disabled", "disabled");
    else add_all_button.removeAttr("disabled");
    if ($(this).find("option:selected").length == 0) add_button.attr("disabled", "disabled");
    else add_button.removeAttr("disabled");
  })
  .change();

  // Enable/Disable the Del buttons depending on the number of items or the selection
  // of the output list
  output_licensee_list.on('change', function(e) {
    console.log("output_list.change, options=" + $(this).find("option").length + ", selected=" + $(this).find("option:selected").length);
    if ($(this).find("option").length == 0) del_all_button.attr("disabled", "disabled");
    else del_all_button.removeAttr("disabled");
    if ($(this).find("option:selected").length == 0) del_button.attr("disabled", "disabled");
    else del_button.removeAttr("disabled");
  })
  .change();

  // Update the list of licensees from a group when the group selection changes
  group_selection.on('change', function(e) {
    var licensee_group_api = $(this).parent().data('function');
    console.log("group change, value=" + $(this).val() + ", function=" + licensee_group_api.replace("__id__", $(this).val()));
    input_licensee_list.empty().append("<option>Chargement en cours...</option>");
    $.ajax({
      url: licensee_group_api.replace("__id__", $(this).val()),
      type: 'GET',
    })
    .done(function(data) {
      console.log('success, ' + data.length + ' licensees');
      input_licensee_list.empty();
      for (var i = 0; i < data.length; i++) {
        var licensee = data[i];
        if (output_licensee_list.find("option[value=" + licensee['id'] + ']').length == 0)
          input_licensee_list.append($("<option></option").attr('value', licensee['id']).text(licensee['name']));
      }
    })
    .fail(function(data) {
      console.log("Failure");
      input_licensee_list.empty().append("<option>Erreur de chargement</option>");
    });
    input_licensee_list.trigger('change');
  })
  .change();

  // Add all button: remove all entries from input list and add them to output list
  add_all_button.on('click', function(e) {
    add_to_output(input_licensee_list.find("option").remove(), group_selection, output_licensee_list);
    output_licensee_list.trigger('change');
    input_licensee_list.trigger('change');
  });

  // Add button: remove selected entries from input list and add them to output list
  add_button.on('click', function(e) {
    add_to_output(input_licensee_list.find("option:selected").remove(), group_selection, output_licensee_list);
    output_licensee_list.trigger('change');
    input_licensee_list.trigger('change');
  });

  // Delete all button: remove all entries from output list, reload input list from group
  del_all_button.on('click', function(e) {
    output_licensee_list.find("option").remove();
    output_licensee_list.trigger('change');
    group_selection.trigger('change'); // To reload from group
  });

  // Delete button: remove selected entries from output list, reload input list from group
  del_button.on('click', function(e) {
    output_licensee_list.find("option:selected").remove();
    output_licensee_list.trigger('change');
    group_selection.trigger('change'); // To reload from group
  });

  $("#my_form").on("submit", function() {
    output_licensee_list.find('option').prop("selected", true);
    $("#sln_registerbundle_licenseeselecttype_title").val(tinyMCE.get('title').getContent());
    $("#sln_registerbundle_licenseeselecttype_body").val(tinyMCE.get('body').getContent());
  });

}


// Add a list of options from input to output, add the name of the group, 
// sort the output list by name
function add_to_output(options, group_selection, output_licensee_list) {
  // Add the name of the group
  options.append(' (' + group_selection.find("option:selected").text() + ')');
  options.appendTo(output_licensee_list);
  options.attr("selected", 'selected');

  var opts_list = output_licensee_list.find('option');
  opts_list.sort(function(a, b) { return $(a).text() > $(b).text() ? 1 : -1; });
  output_licensee_list.empty().append(opts_list);
}


// Setup mail sending through AJAX call
function setup_mail_send() {
  var progress_label = $("#progresslabel");
  var progress_bar = $("#progressbar");
  var status_div = $("#mail_status");
  var done = false; // All mails sent
  var failed = false;
  var iid = 0;
  $("#send_mail").prop("disabled", false);

  $("#send_mail").on("click", function(e) {

    if ($(this).html() == "Annuler") {
      console.log("Cancel click");
      $(this).html("Continuer l'envoi...");
      progress_label.html("Cliquez sur le bouton pour continuer...");

      if (iid != 0) clearInterval(iid);
      done = true;
    } 

    else {
      var ajax_done = true; // Current ajax call completed
      var delay = 1000; // ms
      var timeout_start =  120 * 1000 / delay;
      var timeout;

      console.log("Send mail click");
      $(this).html("Annuler");
      progress_label.html("Envoi en cours...");

      iid = window.setInterval(function() {
        if (ajax_done && !done) {
          timeout = timeout_start;
          console.log("Calling fonction " + $("#send_mail").data('function') + ", timeout=" + timeout);
          ajax_done = false;

          $.ajax({
            url: $("#send_mail").data('function'),
            type: 'GET',
          })
          .done(function(data) {
            console.log('Done function, result=' + data.result);
            ajax_done = true;

            if (data.result == "fatal") {
              console.log("Erreur: " + data.error);
              status_div.append("<p>" + data.error + "</p>");
              done = true;
              failed = true;
            }

            else {
              if (data.result == "done") {
                console.log("All done.");
                done = true;
              }
              if ("failures" in data) {
                console.log("No error, failures=" + data.failures.length + ", sent=" + data.sent);

                for(var i = 0; i < data.failures.length; i++) {
                  status_div.append("<p>" + data.failures[i] + "</p>")
                }
              }

              var val = progress_bar.progressbar("value") || 0;
              progress_bar.progressbar( "value", val + data.sent );
            }
          })
          .fail(function() {
            console.error('failure');
            status_div.append("<p class='error'>Erreur de communication avec le serveur.</p>");
            done = true;
            failed = true;
          });

        }

        else {
          timeout--;
          console.log("Waiting: timeout = " + timeout);
        }

        if (done || timeout == 0) {
          if (failed) {
            progress_label.html("L'envoi des mails a échoué");
          } else if (timeout == 0) {
            console.log("End because of timeout.");
            progress_label.html("L'envoi n'a pas terminé. Cliquez sur le bouton pour continuer.");
            $("#send_mail").html("Continuer l'envoi...");
          }
          else {
            console.log("Completed.");
            progress_label.html("Tous les mails ont été envoyés.");
            $("#send_mail").html("Terminé.");
            $("#send_mail").prop("disabled", true);
          }
          window.clearInterval(iid);
          iid = 0;
        }
      }, delay);
    }
  });
}


