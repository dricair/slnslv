$( document ).ready(function() {

  var addLink = '<a href="#" class="add_row_link">Ajouter une ligne</a>';
  var delLink = '<a href="#" class="del_row_link">Supprimer</a>';
  var div = $('div.add-row');
  var link = $(addLink);
  div.append(link);

  var $tbody = div.find('table tbody');
  $tbody.data("index", $tbody.find('tr').length);

  $tbody.find('tr').each(function(index) {
    delRow($(this), delLink);
  });

  link.on('click', function(e) {
    // Prevent the link from creating a '#' in the URL
    e.preventDefault();

    // Add a new row to the table
    console.log("Click on addRow");
    newLine = addRow($tbody); 
  
    // Add a remove link to the line
    delRow(newLine, delLink);
  });
});               
   

// Function called when adding a row to the table
function addRow(tbody) {
  // Get the data prototype in the tbody
  var prototype = tbody.data('prototype');

  // Get the new index
  var index = tbody.data('index');

  // replace '__name__' in the prototype's HTML by the index
  var newLine = prototype.replace(/__name__/g, index);

  // Increase the index for the next usage
  tbody.data('index', index + 1);

  // Append the new line at the end
  tbody.append(newLine);
  newLine = tbody.find('tr').last();

  return newLine;
};


// function called to add a remove link to a row
function delRow(row, delLink) {
  console.log("delRow: " + row.find("td").last().text());
  link = $(delLink);
  row.find('td').last().append(link);

  link.on('click', function(e) {
    // Prevent the link from creating a '#' in the URL
    e.preventDefault();

    // Remove the row from the table
    console.log("Click on removeRow");
    $(this).closest('tr').remove();
  });

};


