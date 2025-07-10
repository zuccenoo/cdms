$(document).ready(function() {
    // Consumption Log Table
    var consumptionTable = $('#consumptionTable').DataTable({
        dom: '<"dt-controls-wrapper"Blf>rtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100, 250, 500],
        responsive: true
    });

    // Stock Level Table
    var stockTable = $('#stockLevelTable').DataTable({
        dom: '<"dt-controls-wrapper"Blf>rtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100, 250, 500],
        responsive: true
    });

    // Consumption Log Tabs
    $('#categoryTabs-consumption .nav-link').on('click', function() {
        if ($(this).hasClass('dropdown-toggle')) return;
        $('#categoryTabs-consumption .nav-link').removeClass('active');
        $(this).addClass('active');
        var category = $(this).data('category');
        if (category === 'allInventory') {
            consumptionTable.column(3).search('').draw();
        } else {
            consumptionTable.column(3).search('^' + category + '$', true, false).draw();
        }
    });
    $('#categoryTabs-consumption .dropdown-item').on('click', function(e) {
        e.preventDefault();
        $('#categoryTabs-consumption .nav-link').removeClass('active');
        $(this).closest('.dropdown').find('.nav-link').addClass('active');
        var category = $(this).data('category');
        consumptionTable.column(3).search('^' + category + '$', true, false).draw();
    });

    // Stock Level Tabs
    $('#categoryTabs-stock .nav-link').on('click', function() {
        if ($(this).hasClass('dropdown-toggle')) return;
        $('#categoryTabs-stock .nav-link').removeClass('active');
        $(this).addClass('active');
        var category = $(this).data('category');
        if (category === 'allInventory') {
            stockTable.column(2).search('').draw();
        } else {
            stockTable.column(2).search('^' + category + '$', true, false).draw();
        }
    });
    $('#categoryTabs-stock .dropdown-item').on('click', function(e) {
        e.preventDefault();
        $('#categoryTabs-stock .nav-link').removeClass('active');
        $(this).closest('.dropdown').find('.nav-link').addClass('active');
        var category = $(this).data('category');
        stockTable.column(2).search('^' + category + '$', true, false).draw();
    });
});
