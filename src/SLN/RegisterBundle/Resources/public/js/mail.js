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
    console.log("input_list.change, options=" + $(this).find("option").length + ", selected=" + $(this).find("option:selected").length);
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
    add_to_output(input_licensee_list.find("option").remove(), group_selection, output_licensee_list)
    output_licensee_list.trigger('change');
    input_licensee_list.trigger('change');
  });

  // Add button: remove selected entries from input list and add them to output list
  add_button.on('click', function(e) {
    var options = input_licensee_list.find("option:selected").remove()
    options.append(' (' + group_selection.find("option:selected").text() + ')');
    options.appendTo(output_licensee_list);
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
}


// Add a list of options from input to output, add the name of the group, 
// sort the output list by name
function add_to_output(options, group_selection, output_licensee_list) {
  // Add the name of the group
  options.append(' (' + group_selection.find("option:selected").text() + ')');
  options.appendTo(output_licensee_list);

  var opts_list = output_licensee_list.find('option');
  opts_list.sort(function(a, b) { return $(a).text() > $(b).text() ? 1 : -1; });
  output_licensee_list.empty().append(opts_list);
}


