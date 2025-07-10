
/////////////////////////////DATA TABLE INITIALIZATION/////////////////////////////
$(document).ready(function() {
    //////////////////////////CHECKLIST TABLE/////////////////////
    $('#checklistTable').DataTable({
        dom: '<"dt-controls-wrapper"Blf>rtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
        ],
        order: [[0, 'asc'], [2, 'asc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100, 250, 500],
        responsive: true
    });

    ////////////////////////GUEST TABLE/////////////////////
    $('#guestTable').DataTable({
        dom: '<"dt-controls-wrapper"lf>rtip', // removed B
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        responsive: true
    });

    ///////////////////////MODAL CHECKLIST TABLE/////////////////////
    $('#guestTable tbody').on('click', 'tr.guest-row', function() {
        var location = $(this).data('location');
        if (!location) return;

        $('#modalLocationName').text(location);
        $('#modalChecklistBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>');
        var modal = new bootstrap.Modal(document.getElementById('locationChecklistModal'));
        modal.show();

        $.get('inv_checklist.php', { location: location }, function(data) {
            $('#modalChecklistBody').html(data);

            $('#modalChecklistBody table').DataTable({
                dom: '<"dt-controls-wrapper"lf>rtip', 
                order: [[0, 'asc']],
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                responsive: true
            });
        }).fail(function(xhr) {
            $('#modalChecklistBody').html('<div class="alert alert-danger">Failed to load checklist.<br>' + xhr.status + ' ' + xhr.statusText + '</div>');
        });
    });
});