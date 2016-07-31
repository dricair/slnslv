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
    groupe_description.find('#groupe_jours').hide();
  }
  else {
    var description_str = "<div class='panel panel-info'>" +
                          "  <div class='panel-heading'>__nom__ (__categorie__)</div>" +
                          "  <div class='panel-body'>__description__</div>" +
                          "</div>";
    description_str = description_str.replace("__nom__", data.nom);
    description_str = description_str.replace("__categorie__", data.categorie_name);
    description_str = description_str.replace("__description__", data.description.replace("\n", "<br/>"));
    groupe_description.find('.groupe-description').html(description_str);

    var horaires = "";
    if (data.horaire_list.length == 0) {
      url = "http://www.stadelaurentinnatation.fr/mapage3/index.html";
      horaires = "<p>Vous pouvez voir les horaires pour les groupes sur <a href='" + url + 
                 "' title='Page des horaires'>cette page</a></p>";
    }
    else {
      for (index = 0; index < data.horaire_list.length; index++) {
        var horaire = data.horaire_list[index];
        horaires += horaire.description + ", le " + horaire.jour + " de " + horaire.debut + " à " + horaire.fin;
        if (index < data.horaire_list.length - 1) horaires += "<br/>";
      }
    }
    
    var tarifs = "";
    for (index = 0; index < data.tarif_list.length; index++) {
      var tarif = data.tarif_list[index];
      tarifs += tarif.type;
      if (tarif.description) tarifs += "(" + tarif.description + ")";
      tarifs += " : " + tarif.value;
      if (index < data.tarif_list.length - 1) tarifs += "<br/>";
    }
    var tarifs_horaires_str = "<div class='panel panel-info'>" +
                              "  <div class='panel-heading'>Horaires et tarifs</div>" +
                              "  <div class='panel-body'>" + 
                              "    <p>" + horaires + "</p>" +
                              "    <p>" + tarifs + "</p>" +
                              "  </div>" +
                              "</div>"
    groupe_description.find('.groupe-horaires').html(tarifs_horaires_str);

    var multiple_list = data["multiple_list"];
    if (multiple_list) {
      console.log('Multiple_list = ' + multiple_list);
      groupe_description.find('div.checkbox').hide();
      for (index = 0; index < data.multiple_list.length; index++) {
        value = data.multiple_list[index];
        groupe_description.find(':checkbox[value=' + value + ']').parents('div.checkbox').show();
      }
      groupe_description.find('#groupe_jours').show();
    } else {
      groupe_description.find('#groupe_jours').hide();
    }
  }
}
