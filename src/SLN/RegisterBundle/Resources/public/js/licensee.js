// Find group row if available. Description will be updated based on it.
function setup_group_description() {
  var groupe_choice = $('#sln_registerbundle_licenseetype_groupe');

  if (groupe_choice.length > 0) {
    console.log("Found groupe choice");
    var groupe_description = $('#groupe-description');
    var groupe_api = groupe_description.data('function');

    groupe_choice.on('change', function(e) {
      console.log('group change, value = ' + groupe_choice.val() + ', function = ' + groupe_api.replace('__id__', groupe_choice.val()));
      if (groupe_choice.val() == "") {
        update_groupe_description(groupe_description, "");
      } else {
        update_groupe_description(groupe_description, "Chargement...");

        $.ajax({
          url: groupe_api.replace('__id__', groupe_choice.val()),
          type: 'GET',
        })
        .done(function(data) {
          console.log('success, nom=' + data.nom);
          update_groupe_description(groupe_description, data);
        })
        .fail(function() {
          console.error('failure');
          update_groupe_description(groupe_description, "Erreur de réception");
        });
      }
    })
    .change();
  }
}

function update_groupe_description(groupe_description, data) {
  if ($.type(data) == "string") {
    groupe_description.find('.groupe-description').html(data);
    groupe_description.find('.groupe-horaires').html("");
  }
  else {
    var description_str = "<p class='title'>__nom__ (__categorie__)</p><p>__description__</p>";
    description_str = description_str.replace("__nom__", data.nom);
    description_str = description_str.replace("__categorie__", data.categorie_name);
    description_str = description_str.replace("__description__", data.description.replace("\n", "<br/>"));
    groupe_description.find('.groupe-description').html(description_str);

    var horaires = "";
    for (index = 0; index < data.horaire_list.length; index++) {
      var horaire = data.horaire_list[index];
      horaires += horaire.description + ", le " + horaire.jour + " de " + horaire.debut + " à " + horaire.fin;
      if (index < data.horaire_list.length - 1) horaires += "<br/>";
    }
    var horaires_str = "<p class='title'>Horaires</p><p>" + horaires + "</p>";
    groupe_description.find('.groupe-horaires').html(horaires_str);
  }
}
