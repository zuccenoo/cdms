//////////////////////////DATATABLES INITIALIZATION//////////////////////////
$(document).ready(function() {
    $('#archivedTable').DataTable();

    /////////////////////////////////////////////TAB FUNCTION////////////////////////////////////////////////
    // Only filter when a non-dropdown-toggle tab is clicked
    $(document).ready(function() {
        var table = $('#archivedTable').DataTable();

        // Category tab click (non-dropdown)
        $('#categoryTabs .nav-link').on('click', function() {
            if ($(this).hasClass('dropdown-toggle')) return;
            $('#categoryTabs .nav-link').removeClass('active');
            $(this).addClass('active');
            var category = $(this).data('category');
            if (category === 'allInventory') {
                table.column(2).search('').draw();
            } else {
                table.column(2).search('^' + category + '$', true, false).draw();
            }
        });

        // Dropdown item click (for staff items)
        $('#categoryTabs .dropdown-item').on('click', function(e) {
            e.preventDefault();
            $('#categoryTabs .nav-link').removeClass('active');
            $(this).closest('.dropdown').find('.nav-link').addClass('active');
            var category = $(this).data('category');
            table.column(2).search('^' + category + '$', true, false).draw();
        });
    });
});


//////////////////////////RESTORE FUNCTION//////////////////////////
function openRestoreModal(itemId) {
    document.getElementById('itemIdToRestore').value = itemId;

    // Find the row in the table with the matching itemId
    const table = document.getElementById("archivedTable");
    const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
    let itemName = '';
    for (let row of rows) {
        if (row.cells[0] && row.cells[0].innerText === itemId) {
            itemName = row.cells[1].innerText;
            break;
        }
    }
    document.getElementById('restoreConfirmText').textContent =
        `Are you sure you want to restore "${itemId}", "${itemName}"?`;

    const modal = new bootstrap.Modal(document.getElementById('restoreItemModal'));
    modal.show();
}

function confirmRestoreItem() {
    const itemId = document.getElementById('itemIdToRestore').value;
    $.post('', {restore_item: 1, item_id: itemId}, function(data) {
        if (data.trim() === "success") {
            showSuccessModal('Item restored successfully!', function() {
                location.reload();
            });
        } else {
            alert('Failed to restore item: ' + data);
        }
    });
}

//////////SUCCESS MODAL FUNCTION//////////////////////////
function showSuccessModal(message = "The item has been added successfully.", onClose) {
    document.querySelector("#successModal .modal-body p").textContent = message;
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();

    // Remove any previous event listeners
    $('#successModal').off('hidden.bs.modal');

    $('#successModal').on('hidden.bs.modal', function() {
        if (typeof onClose === 'function') {
            onClose();
        }
    });
}

function closeSuccessModal() {
    const successModal = bootstrap.Modal.getInstance(document.getElementById('successModal'));
    successModal.hide();
}

////////////////////////////DELETE ITEM FUNCTION///////////////////////////
function openDeleteModal(itemId) {
    document.getElementById('itemIdToDelete').value = itemId;

    // Find the row in the table with the matching itemId
    const table = document.getElementById("archivedTable");
    const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
    let itemName = '';
    for (let row of rows) {
        if (row.cells[0] && row.cells[0].innerText === itemId) {
            itemName = row.cells[1].innerText;
            break;
        }
    }
    document.getElementById('deleteConfirmText').textContent =
        `Are you sure you want to delete "${itemId}", "${itemName}"?`;

    const modal = new bootstrap.Modal(document.getElementById('deleteItemModal'));
    modal.show();
}
function deleteItemModal() {
    const itemId = document.getElementById('itemIdToDelete').value;
    $.post('', {delete_item: 1, item_id: itemId}, function(data) {
        if (data.trim() === "success") {
            showSuccessModal('Item deleted successfully!', function() {
                location.reload();
            });
        } else {
            alert('Failed to delete item: ' + data);
        }
    });
}
closeDeleteModal = function() {
    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteItemModal'));
    deleteModal.hide();
}
