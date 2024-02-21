// Call the dataTables jQuery plugin
$(document).ready(function() {
  $('#dataTable').DataTable();
  $('#userTable').DataTable({
    "paging": false,
    "searching": false,
    "bInfo" : false
  });

  $('#companiesTable').DataTable({
    "paging": false,
    "searching": false,
    "bInfo" : false
  });

  $('#blockedTable').DataTable({
    "paging": false,
    "searching": false,
    "bInfo" : false
  });

  $('#clearBtnUser').on('click', function() {
    $('#email').val('');
    window.location.href = '/users';
  });

  $('#clearBtnBlocked').on('click', function() {
    $('#email').val('');
    window.location.href = '/users/blocked';
  });

  $('#clearBtnCompany').on('click', function() {
    $('#domain').val('');
    window.location.href = '/companies';
  });

  $('#entries').on('change', function() {
    $('#entriesform').submit();
  });
});
