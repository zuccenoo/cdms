////////////////////////////////////////////////SIDEBAR NA BAGO////////////////////////////////////////////////
/////////ADJUST COLUMN ARRAY KAPAG MAGADJUST NG COLUMNS/////////
/*
current column array:
0: 'select-checkbox', // Checkbox for selection
1: 'inv_id', // Inventory ID
2: 'item_name', // Item Name
3: 'item_category', // Item Category
4: 'item_quantity', // Item Quantity
5: 'item_location', // Item Location
6: 'item_supplier', // Item Supplier
7: 'date_added', // Date Added
8: 'last_updated', // Last Updated
9: 'actions' // Actions (Edit/Archive/Delete/Consume/Expand)
*/ 

/////////////////////////////////////////////////DATATABLES INITIALIZATIONSSS////////////////////////////////////////////////
$(document).ready(function() {
    var table = $('#inventoryTable').DataTable({
        dom: '<"dt-controls-wrapper"Blf>rtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
        ],
        order: [[8, 'desc']], // Adjust index if needed
        columnDefs: [
            { orderable: false, className: 'select-checkbox', targets: 0 }, // Disable sorting for selection
            { orderable: false, targets: 1 }, // Disable sorting for expandable
            { orderable: false, targets: -1 },
            { orderSequence: ["asc", "desc"], targets: "_all" }
        ],
        select: {
            style: 'multi',
            selector: 'td:first-child'
        }
    });

    //////////////////////////////////////////////EXPANDABLE FUNCTION////////////////////////////////////////////////
    $(document).ready(function() {
        var table = $('#inventoryTable').DataTable();

        $('#inventoryTable tbody').on('click', 'button.details-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                var raw = tr.find('td').eq(2).clone();
                raw.find('span, br').remove();
                var itemName = raw.text().replace(/\(\d+\)/, '').trim();
                $.get('inv_index.php', { item_details_by_name: itemName }, function(detailsHtml) {
                    row.child(detailsHtml).show();
                    tr.addClass('shown');
                });
            }
        });        
    });

    applyRowHighlight();

    ///////////////////////////////////////////SELECT FUNCTION////////////////////////////////////////////////
    // Select-all checkbox
    $('#select-all-dt').on('click', function() {
        if (this.checked) {
            table.rows({ search: 'applied' }).select();
        } else {
            table.rows().deselect();
        }
        // Sync all visible child checkboxes
        $('.child-row-checkbox').prop('checked', this.checked);
        var selectedRows = table.rows({ selected: true }).count();
        $('#bulkArchiveBtn').toggle(selectedRows > 0);
        $('#bulkDeleteBtn').toggle(selectedRows > 0);
        $('#deselectAllBtn').toggle(selectedRows > 0);
    });

    // Keep select-all checkbox in sync
    table.on('select deselect', function() {
        var allRows = table.rows({ search: 'applied' }).count();
        var selectedRows = table.rows({ selected: true, search: 'applied' }).count();
        $('#select-all-dt').prop('checked', allRows > 0 && allRows === selectedRows);
        $('#bulkArchiveBtn').toggle(selectedRows > 0);
        $('#bulkDeleteBtn').toggle(selectedRows > 0);
        $('#deselectAllBtn').toggle(selectedRows > 0);
    });

    // Parent select/deselect: select/deselect all child checkboxes (if expanded)
    table.on('select', function(e, dt, type, indexes) {
        if (type === 'row') {
            var row = table.row(indexes[0]);
            var tr = $(row.node());
            if (tr.hasClass('shown')) {
                $('.child-row-checkbox', row.child()).prop('checked', true);
            }
        }
    });
    table.on('deselect', function(e, dt, type, indexes) {
        if (type === 'row') {
            var row = table.row(indexes[0]);
            var tr = $(row.node());
            if (tr.hasClass('shown')) {
                $('.child-row-checkbox', row.child()).prop('checked', false);
            }
        }
    });
    ///if a parent row is selected, check all child checkboxes
    $(document).on('change', '.child-row-checkbox', function() {
        var parentTr = $(this).closest('tr').parents('tr.shown').first();
        if (parentTr.length) {
            var parentRow = table.row(parentTr);
            var allChecked = $('.child-row-checkbox', parentRow.child()).length === $('.child-row-checkbox:checked', parentRow.child()).length;
            if (allChecked) {
                parentRow.select();
            }
        }
    });

    ///////////////////////////////////////////BULK ARCHIVE///////////////////////////////////////////////
    $('#bulkArchiveBtn').on('click', function() {
        var selectedData = table.rows({ selected: true }).data();
        var ids = [];
        for (var i = 0; i < selectedData.length; i++) {
            ids.push(selectedData[i][1]); // [1] is the ID column
        }
        $('.child-row-checkbox:checked').each(function() {
            ids.push($(this).data('inv-id'));
        });
        ids = [...new Set(ids)];
        if (ids.length === 0) return;

        // Use the single archive modal for confirmation
        $('#archiveConfirmText').text(`Are you sure you want to archive ${ids.length} selected item(s)?`);
        $('#itemIdToArchive').val(ids.join(',')); // Store all IDs as comma-separated
        $('#archiveItemModal').data('bulk', true);

        const archiveItemModal = new bootstrap.Modal(document.getElementById('archiveItemModal'));
        archiveItemModal.show();
    });
    
    ///////////////////////////////////////////BULK DELETE///////////////////////////////////////////////
    $('#bulkDeleteBtn').on('click', function() {
        var selectedData = table.rows({ selected: true }).data();
        var ids = [];
        for (var i = 0; i < selectedData.length; i++) {
            ids.push(selectedData[i][1]); // [2] is the ID column
        }
        $('.child-row-checkbox:checked').each(function() {
            ids.push($(this).data('inv-id'));
        });
        ids = [...new Set(ids)]; // Remove duplicates
        if (ids.length === 0) return;

        $('#deleteConfirmText').text(`Are you sure you want to delete ${ids.length} selected item(s)?`);
        $('#itemIdToDelete').val(ids.join(','));
        $('#itemNameToDelete').val('');
        $('#deleteItemModal').data('bulk', true);

        const deleteItemModal = new bootstrap.Modal(document.getElementById('deleteItemModal'));
        deleteItemModal.show();
    });

    ///////////////////////////////////////////DESELECT////////////////////////////////////////////////
    $('#deselectAllBtn').on('click', function() {
        table.rows().deselect();
        $('.child-row-checkbox').prop('checked', false);
    });

    /////////////////////////////////////////////TAB FUNCTION////////////////////////////////////////////////
    // Only filter when a non-dropdown-toggle tab is clicked
    $('#categoryTabs .nav-link').on('click', function() {
        // Ignore dropdown toggles
        if ($(this).hasClass('dropdown-toggle')) return;
        $('#categoryTabs .nav-link').removeClass('active');
        $(this).addClass('active');
        var category = $(this).data('category');
        if (category === 'allInventory') {
            table.column(3).search('').draw();
        } else {
            table.column(3).search('^' + category + '$', true, false).draw();
        }
    });

    // Only filter when a specific staff item is clicked
    $('#categoryTabs .dropdown-item').on('click', function(e) {
        e.preventDefault();
        $('#categoryTabs .nav-link').removeClass('active');
        $(this).closest('.dropdown').find('.nav-link').addClass('active');
        var category = $(this).data('category');
        table.column(3).search('^' + category + '$', true, false).draw();
    });

});

////////////////////////////////////////////////ITEMCODE FUNCTION////////////////////////////////////////////////
document.getElementById('itemCategory').addEventListener('change', function() {
    generateItemCode();
});

function generateItemCode() {
    const category = document.getElementById("itemCategory").value; // Get the selected category

    if (!category) return; // Exit if no category is selected

    // Send AJAX request to fetch the next available item code
    fetch(`inv_index.php?get_item_code=true&category=${encodeURIComponent(category)}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById("itemCode").value = data.trim(); // Set the item code in the input field
        })
        .catch(error => console.error("Error fetching item code:", error));
}

/*////////////////////////////////////////////////SEARCH FUNCTION////////////////////////////////////////////////
const searchBar = document.getElementById('searchBar');
const clearSearchBtn = document.getElementById('clearSearchBtn');

function toggleClearBtn() {
    clearSearchBtn.style.display = searchBar.value ? '' : 'none';
}
searchBar.addEventListener('input', toggleClearBtn);
searchBar.addEventListener('focus', toggleClearBtn);
window.addEventListener('DOMContentLoaded', toggleClearBtn);

searchBar.addEventListener('blur', function() {
    setTimeout(() => {
        if (!this.value) clearSearchBtn.style.display = 'none';
    }, 100);
});

clearSearchBtn.addEventListener('click', function() {
    searchBar.value = '';
    clearSearchBtn.style.display = 'none';
    searchBar.dispatchEvent(new Event('keyup'));
});

searchBar.addEventListener('keyup', function () {
    const query = this.value;
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'inv_index.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (this.status === 200) {
            document.getElementById('inventoryBody').innerHTML = this.responseText;
        }
    };
    xhr.send('query=' + encodeURIComponent(query));
});
*////////////////////////////////////////////////

/*///////////////////////////////////////////////FILTER 2 FUNCTION////////////////////////////////////////////////
function setCategoryFilter(category) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'inv_index.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById('inventoryBody').innerHTML = xhr.responseText;
        }
    };
    xhr.send('categoryFilter=' + encodeURIComponent(category));
}
*////////////////////////////////////////////////

/*////////////////////////////////////////////////////FIELD SORT////////////////////////////////////////////////
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.sort-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const field = this.getAttribute('data-field');
            // Toggle sort order
            let currentOrder = this.classList.contains('asc') ? 'asc' : 'desc';
            let newOrder = currentOrder === 'asc' ? 'desc' : 'asc';

            // AJAX request
            fetch('inv_index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `ajax_sort=1&field=${encodeURIComponent(field)}&sort=${newOrder}`
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('inventoryBody').innerHTML = html;
                // Optionally update icons/classes here
            });
        });
    });
});
*/////////////////////////////////////////////////

/*////////////////////////////////////////////////////SHOW ENTRY////////////////////////////////////////////////
document.getElementById('entriesSelect').addEventListener('change', function() {
    const entries = this.value;
    const url = new URL(window.location.href);
    url.searchParams.set('entries', entries);
    url.searchParams.set('page', 1); // Reset to first page

    // Get current category from active tab or URL
    const activeTab = document.querySelector('.tab.active');
    let category = activeTab ? activeTab.getAttribute('data-category') || activeTab.textContent.trim() : '';
    if (category === 'Show all') category = 'allInventory';
    url.searchParams.set('category', category);

    window.location.href = url.toString();
});
*/////////////////////////////////////////////

////////////////////////////////////////////////ADD MODAL////////////////////////////////////////////////
function openAddModal() {
    const now = new Date();
    const formattedDateTime = now.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: '2-digit',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    document.getElementById("datePreview").innerText = formattedDateTime;
    document.getElementById("dateAdded").value = now.toISOString();

    const addItemModal = new bootstrap.Modal(document.getElementById('addItemModal'));
    addItemModal.show();
}

function addItem() {
    const locationSelect = document.getElementById('itemLocation');
    const customLocationInput = document.getElementById('customLocation');
    // If "Other" is selected, prepend "Other - " to the custom location input
    if (locationSelect.value === 'Other' && customLocationInput.value.trim()) {
        customLocationInput.value = 'Other - ' + customLocationInput.value.trim();
    }

    const form = document.querySelector('#addItemModal form');
    const formData = new FormData(form);

    // Validate required fields
    const requiredFields = ['itemCategory', 'itemName', 'itemQuantity', 'itemLocation'];
    for (const field of requiredFields) {
        const input = document.getElementById(field);
        if (!input || !input.value.trim()) {
            const fieldName = field.replace('item', 'Item ');
            alert(`${fieldName} is empty. Please fill out the necessary field(s).`);
            return;
        }
    }

    // Check for duplicate item name before submitting
    const itemName = document.getElementById('itemName').value.trim();
    fetch('inv_index.php?check_item_exists=1&item_name=' + encodeURIComponent(itemName))
        .then(res => res.json())
        .then(data => {
            if (data.exists) {
                document.getElementById('existsItemName').textContent = itemName;
                const modal = new bootstrap.Modal(document.getElementById('itemExistsModal'));
                modal.show();

                document.getElementById('continueAddBtn').onclick = function() {
                    modal.hide();
                    actuallyAddItem(formData);
                };
            } else {
                actuallyAddItem(formData);
            }
        });
}
function actuallyAddItem(formData) {
    fetch('inv_index.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.text())
    .then(data => {
        if (data.startsWith("success:")) {
            const newId = data.split(":")[1].trim();
            showSuccessModal("The item has been added successfully.", function() {
                window.location.href = window.location.pathname + '?highlight=' + encodeURIComponent(newId);
            });
        } else if (data.trim() === "success") {
            showSuccessModal();
        } else {
            alert('Error adding item. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding item. Please try again.');
    });
}

////////////////////////////////////////////////RESET FIELDS KAPAG CLOSE ADD MODAL////////////////////////////////////////////////
function resetFormFields() {
    const fields = ['itemCategory', 'itemName', 'itemCode', 'itemQuantity', 'itemLocation', 'itemSupplier', 'dateAdded', 'customLocation'];
    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            element.value = ''; // Reset the value of the field
        }
    });

    // Reset the "Select Location" dropdown
    const locationSelect = document.getElementById('itemLocation');
    if (locationSelect) {
        locationSelect.selectedIndex = 0; // Reset to the first option
    }

    // Hide and reset the "Custom Location" input
    const customLocationInput = document.getElementById('customLocation');
    if (customLocationInput) {
        customLocationInput.style.display = 'none'; // Hide the input
        customLocationInput.required = false; // Remove the required attribute
    }
}

/////////////////////////////////////////////////////////////MULTI ADD////////////////////////////////////////////////
function openMultiAddModal() {
    const now = new Date();
    const formattedDateTime = now.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: '2-digit',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });

    document.getElementById("multiDatePreview").innerText = formattedDateTime;
    document.getElementById("multiDateAdded").value = now.toISOString();

    const addItemModalEl = document.getElementById('addItemModal');
    const addItemModal = bootstrap.Modal.getInstance(addItemModalEl) || new bootstrap.Modal(addItemModalEl);
    addItemModal.hide();

    const multiAddModalEl = document.getElementById('multiAddModal');
    const multiAddModal = bootstrap.Modal.getInstance(multiAddModalEl) || new bootstrap.Modal(multiAddModalEl);
    multiAddModal.show();
}
//add row
function addRow() {
    const table = document.getElementById('multiAddTable').getElementsByTagName('tbody')[0];
    const newRow = table.rows[0].cloneNode(true);

    newRow.querySelectorAll('input, select').forEach(el => el.value = '');
    table.appendChild(newRow);
}
//remove row
function removeRow(btn) {
    const row = btn.closest('tr');
    const table = row.parentNode;
    if (table.rows.length > 1) row.remove();
}
//copy row
function copyRow(btn) {
    const row = btn.closest('tr');
    const newRow = row.cloneNode(true);

    newRow.querySelectorAll('input, select').forEach((el, idx) => {
        if (el.type === 'checkbox') {
            el.checked = row.querySelectorAll('input, select')[idx].checked;
        } else {
            el.value = row.querySelectorAll('input, select')[idx].value;
        }
    });
    row.parentNode.insertBefore(newRow, row.nextSibling);
}
window.copyRow = copyRow; // Make it available globally

function submitMultiAdd() {
    const form = document.getElementById('multiAddForm');
    const formData = new FormData(form);

    const rows = document.querySelectorAll('#multiAddTable tbody tr');
    let isValid = true;

    rows.forEach(row => {
        const itemName = row.querySelector('input[name^="item_name"]').value.trim();
        const itemQuantity = row.querySelector('input[name^="quantity"]').value.trim();

        if (!itemName || !itemQuantity) {
            isValid = false;
            row.classList.add('table-danger');
        } else {
            row.classList.remove('table-danger');
        }
    });

    if (!isValid) {
        alert('Please fill in all required fields.');
        return;
    }

    fetch('inv_index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.startsWith("success:")) {
            // Get all new IDs
            const ids = data.split(":")[1].trim();
            showSuccessModal("Items added successfully.", function() {
                window.location.href = window.location.pathname + '?highlight=' + encodeURIComponent(ids);
            });
        } else if (data.trim() === "success") {
            showSuccessModal("Items added successfully.");
        } else {
            alert('Error adding items.');
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}

////////////////////////////////////////////////CLOSE MODAL////////////////////////////////////////////////
function closeModal() {
    document.getElementById('addItemModal').style.display = 'none';
    document.getElementById('editItemModal').style.display = 'none';

    resetFormFields();
}

////////////////////////////////////////////////CUSTOM LOCATION////////////////////////////////////////////////
//custom location add
function toggleCustomLocationInput() {
    const locationSelect = document.getElementById('itemLocation');
    const customLocationWrapper = document.getElementById('customLocationWrapper');
    const customLocationInput = document.getElementById('customLocation');

    if (locationSelect.value === 'Other') {
        customLocationWrapper.style.display = 'block';
        customLocationInput.required = true;
        locationSelect.style.display = 'none'; // Hide the dropdown
    } else {
        customLocationWrapper.style.display = 'none';
        customLocationInput.required = false;
        locationSelect.style.display = ''; // Show the dropdown
    }
}

document.getElementById('itemLocation').addEventListener('change', toggleCustomLocationInput);

// Handle the "X" button to return to main location dropdown
document.getElementById('customLocationCancel').addEventListener('click', function() {
    const locationSelect = document.getElementById('itemLocation');
    const customLocationWrapper = document.getElementById('customLocationWrapper');
    const customLocationInput = document.getElementById('customLocation');
    // Reset custom input and show dropdown
    customLocationInput.value = '';
    customLocationInput.required = false;
    customLocationWrapper.style.display = 'none';
    locationSelect.style.display = '';
    locationSelect.value = ''; // Reset selection
});

//custom location edit
function toggleEditCustomLocationInput() {
    const locationSelect = document.getElementById('editItemLocation');
    const customLocationWrapper = document.getElementById('editCustomLocationWrapper');
    const customLocationInput = document.getElementById('editCustomLocation');

    if (locationSelect.value === 'Other') {
        customLocationWrapper.style.display = 'block';
        customLocationInput.required = true;
        locationSelect.style.display = 'none';
        // Do NOT clear customLocationInput.value here!
    } else {
        customLocationWrapper.style.display = 'none';
        customLocationInput.required = false;
        locationSelect.style.display = '';
        // Only clear the input when leaving "Other"
        customLocationInput.value = '';
    }
}

document.getElementById('editItemLocation').addEventListener('change', toggleEditCustomLocationInput);

////////////////////////////////////////////////FORMAT DATE////////////////////////////////////////////////
function formatDateForInput(dateString) {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return ''; // prevent invalid date
    return date.toISOString().slice(0, 16); // "yyyy-MM-ddTHH:mm"
}

////////////////////////////////////////////////EDIT FUNCTION////////////////////////////////////////////////
function openEditModal(invId) {
    const table = document.getElementById("inventoryTable");
    const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

    function formatDatePreview(dateString) {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';
        return date.toLocaleString('en-US', {
            month: 'long',
            day: '2-digit',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    // Always fetch real item details via AJAX for edit
    fetch('inv_index.php?get_item_details=1&inv_id=' + encodeURIComponent(invId))
        .then(res => res.json())
        .then(data => {
            if (!data || !data.inv_id) {
                alert("Item not found!");
                return;
            }
            document.getElementById("editItemId").value = data.inv_id;
            document.getElementById("editItemName").value = data.item_name;
            document.getElementById("editItemCategory").value = data.category;
            document.getElementById("editItemQuantity").value = data.quantity;
            document.getElementById("editItemUnit").value = data.unit;
            document.getElementById("editItemLocation").value = data.location_name;

            // Handle custom location
            const editLocationSelect = document.getElementById('editItemLocation');
            const editCustomLocationWrapper = document.getElementById('editCustomLocationWrapper');
            const editCustomLocationInput = document.getElementById('editCustomLocation');
            const locationValue = data.location_name;
            if (locationValue.startsWith('Other - ')) {
                editLocationSelect.value = 'Other';
                editCustomLocationWrapper.style.display = 'block';
                editCustomLocationInput.required = true;
                editLocationSelect.style.display = 'none';
                editCustomLocationInput.value = locationValue.replace('Other - ', '');
            } else {
                editLocationSelect.value = locationValue;
                editCustomLocationWrapper.style.display = 'none';
                editCustomLocationInput.required = false;
                editLocationSelect.style.display = '';
                editCustomLocationInput.value = '';
            }

            document.getElementById("editItemSupplier").value = data.supplier;
            document.getElementById("editItemConsumable").checked = !!parseInt(data.is_consumable);

            document.getElementById("editDateAdded").value = data.date_added;
            document.getElementById("editDatePreview").innerText = formatDatePreview(data.date_added);

            const now = new Date();
            document.getElementById("editLastUpdated").value = now.toISOString();
            document.getElementById("editUpdatedPreview").innerText = formatDatePreview(now);

            const editItemModal = new bootstrap.Modal(document.getElementById('editItemModal'));
            editItemModal.show();
        });

}

function editItem() {
    const locationSelect = document.getElementById('editItemLocation');
    const customLocationInput = document.getElementById('editCustomLocation');

    // If "Other" is selected, require a value and prepend "Other - "
    if (locationSelect.value === 'Other') {
        if (!customLocationInput.value.trim()) {
            alert("Please specify a location.");
            customLocationInput.focus();
            return;
        }
        customLocationInput.value = 'Other - ' + customLocationInput.value.replace(/^Other - /, '').trim();
    } else {
        customLocationInput.value = '';
    }

    const form = document.querySelector("#editItemModal form");
    const formData = new FormData(form);
    formData.append("edit_item", "true");

    fetch("inv_index.php", {
        method: "POST",
        body: formData,
    })
    .then(response => response.text())
    .then(data => {
        if (data.startsWith("success:")) {
            const editedId = data.split(":")[1].trim();
            showSuccessModal("The item has been updated successfully.", function() {
                window.location.href = window.location.pathname + '?highlight=' + encodeURIComponent(editedId) + '&type=edit';
            });
            closeEditModal();
        } else if (data.trim() === "success") {
            showSuccessModal("The item has been updated successfully.");
            closeEditModal();
        } else {
            showSuccessModal("Failed to update item. " + data); // Show error in modal
        }
    })
    .catch(error => {
        showSuccessModal("Failed to update item. " + error); // Show error in modal
    });
}

function closeEditModal() {
    // Hide the edit modal
    document.getElementById('editItemModal').style.display = 'none';
}

//////////////////////////////////////////////////////////////ARCHIVE FUNCTION////////////////////////////////////////////////
function openArchiveModal(itemId) {
    document.getElementById('itemIdToArchive').value = itemId;
    const archiveItemModal = new bootstrap.Modal(document.getElementById('archiveItemModal'));
    archiveItemModal.show();
}

function archiveItem() {
    const isBulk = $('#archiveItemModal').data('bulk');
    if (isBulk) {
        // Bulk archive
        const ids = $('#itemIdToArchive').val().split(',');
        const formData = new URLSearchParams();
        formData.append('bulk_archive', '1');
        ids.forEach(id => formData.append('ids[]', id.trim()));

        fetch('inv_index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(response => response.text())
        .then(data => {
            $('#archiveItemModal').data('bulk', false); // Reset for next time
            if (data.trim() === "success") {
                showSuccessModal("Selected items have been archived successfully.");
            } else {
                alert('Failed to archive items: ' + data);
            }
        })
        .catch(error => {
            alert('An error occurred while archiving items.');
        });
    } else {
        // Single archive (existing logic)
        const itemId = document.getElementById('itemIdToArchive').value;
        fetch('inv_index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `archive_item=1&item_id=${encodeURIComponent(itemId)}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "success") {
                showSuccessModal("The item has been archived successfully.", function() {
                    location.reload();
                });
            } else {
                alert('Failed to archive item: ' + data);
            }
        })
        .catch(error => {
            alert('An error occurred while archiving the item.');
        });
    }
}

function closeArchiveModal() {
    const archiveModal = bootstrap.Modal.getInstance(document.getElementById('archiveItemModal'));
    if (archiveModal) archiveModal.hide();
}

////////////////////////////////////////////////DELETE FUNCTION////////////////////////////////////////////////
function openDeleteModal(itemId) {
    // Set the item ID to the input field
    document.getElementById('itemIdToDelete').value = itemId;

    // Look for the row in the table with the matching itemId
    const table = document.getElementById("inventoryTable");
    const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

    // Always fetch real item details via AJAX for delete
    fetch('inv_index.php?get_item_details=1&inv_id=' + encodeURIComponent(itemId))
        .then(res => res.json())
        .then(data => {
            if (data && data.inv_id) {
                document.getElementById('itemNameToDelete').value = data.item_name;
                document.getElementById('deleteConfirmText').textContent =
                    `Are you sure you want to delete "${itemId}", "${data.item_name}"?`;
            } else {
                document.getElementById('deleteConfirmText').textContent =
                    'Are you sure you want to delete the selected item(s)?';
            }
            // Show the modal using Bootstrap's modal API
            const deleteItemModal = new bootstrap.Modal(document.getElementById('deleteItemModal'));
            deleteItemModal.show();
        });
    return;
}

function deleteItem() {
    const isBulk = $('#deleteItemModal').data('bulk');
    if (isBulk) {
        // Bulk delete
        const ids = $('#itemIdToDelete').val().split(',');
        const formData = new URLSearchParams();
        formData.append('bulk_delete', '1');
        ids.forEach(id => formData.append('ids[]', id.trim()));

        fetch('inv_index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "success") {
                showSuccessModal("Selected items have been deleted successfully.");
                closeDeleteModal();
            } else {
                alert('Failed to delete items: ' + data);
            }
        })
        .catch(error => {
            alert('An error occurred while deleting items.');
        });
    } else {
        // Single delete
        const itemId = document.getElementById('itemIdToDelete').value;
        fetch('inv_index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `item_id=${encodeURIComponent(itemId)}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "success") {
                showSuccessModal("The item has been deleted successfully.");
                closeDeleteModal();
            } else {
                alert('Failed to delete item: ' + data);
            }
        })
        .catch(error => {
            alert('An error occurred while deleting the item.');
        });
    }
}

function closeDeleteModal() {
    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteItemModal'));
    if (deleteModal) deleteModal.hide();
}

////////////////////////////////////////////////CONSUME FUNCTION////////////////////////////////////////////////
let currentConsumeQuantity = 0;

// Open the consume modal and set up values
function openConsumeModal(invId) {
    let found = false;
    // 1. Try to find in main table
    const table = document.getElementById("inventoryTable");
    const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
    for (let row of rows) {
        if (row.cells[1] && row.cells[1].innerText === invId) {
            document.getElementById("consumeItemId").value = invId;
            // Extract only the quantity number
            const quantityText = row.cells[4].innerText.trim(); // [4] for Quantity in child rows
            currentConsumeQuantity = parseInt(quantityText.split(' ')[0], 10);
            found = true;
            break;
        }
    }
    if (!found) {
        const childCheckbox = document.querySelector('.child-row-checkbox[data-inv-id="' + invId + '"]');
        if (childCheckbox) {
            const childRow = childCheckbox.closest('tr');
            if (childRow) {
                document.getElementById("consumeItemId").value = invId;
                const quantityText = childRow.cells[4].innerText.trim();
                currentConsumeQuantity = parseInt(quantityText.split(' ')[0], 10);
                found = true;
            }
        }
    }
    if (!found) {
        alert("Could not find item row for consumption.");
        return;
    }
    document.getElementById("consumeQuantity").value = "";
    document.getElementById("consumeErrorMsg").style.display = "none";
    document.getElementById("consumeSubmitBtn").disabled = false;

    // Show the modal (Bootstrap 5)
    var modal = new bootstrap.Modal(document.getElementById('consumeModal'));
    modal.show();
}

// Validate input and disable button if invalid
document.getElementById("consumeQuantity").addEventListener("input", function() {
    const val = parseInt(this.value, 10);
    const errorMsg = document.getElementById("consumeErrorMsg");
    const submitBtn = document.getElementById("consumeSubmitBtn");

    if (!this.value || isNaN(val) || val <= 0) {
        errorMsg.style.display = "none";
        submitBtn.disabled = true;
    } else if (val > currentConsumeQuantity) {
        errorMsg.textContent = "You cannot consume more than the current quantity!";
        errorMsg.style.display = "block";
        submitBtn.disabled = true;
    } else {
        errorMsg.style.display = "none";
        submitBtn.disabled = false;
    }
});

// Handle form submission via AJAX
document.getElementById('consumeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('inv_index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === "success") {
            showSuccessModal("Quantity consumed successfully.")
            closeConsumeModal();
        } else {
            document.getElementById("consumeErrorMsg").textContent = "Error: " + data;
            document.getElementById("consumeErrorMsg").style.display = "block";
        }
    })
    .catch(error => {
        document.getElementById("consumeErrorMsg").textContent = "Error: " + error;
        document.getElementById("consumeErrorMsg").style.display = "block";
    });
});

function closeConsumeModal() {
    const consumeModal = bootstrap.Modal.getInstance(document.getElementById('consumeModal'));
    if (consumeModal) consumeModal.hide();
}

//////////////////////////////////////////////////SUCCESS MODAL////////////////////////////////////////////////
function showSuccessModal(message = "The item has been added successfully.", onClose) {
    document.querySelector("#successModal .modal-body p").textContent = message;
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();

    // Remove any previous event listeners
    $('#successModal').off('hidden.bs.modal');

    $('#successModal').on('hidden.bs.modal', function() {
        if (typeof onClose === 'function') {
            onClose();
        } else {
            location.reload();
        }
    });
}

function closeSuccessModal() {
    const successModal = bootstrap.Modal.getInstance(document.getElementById('successModal'));
    successModal.hide();
}

////////////////////////////////////////////////////////////HIGHLIGHT ROWS////////////////////////////////////////////////
function applyRowHighlight() {
    const params = new URLSearchParams(window.location.search);
    const highlightIds = params.get('highlight');
    const highlightType = params.get('type');
    if (highlightIds) {
        // Support multiple IDs separated by comma
        const idArr = highlightIds.split(',');
        $('#inventoryTable tbody tr').each(function() {
            const rowId = $(this).find('td').eq(1).text().trim(); // eq(1) for inv_id
            if (idArr.includes(rowId)) {
                const $row = $(this);
                const animClass = highlightType === 'edit' ? 'animated-warning' : 'animated-success';
                $row.addClass(animClass);
                this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => $row.removeClass(animClass), 5000);
            }
        });

        // Remove highlight from URL so it doesn't re-trigger
        params.delete('highlight');
        params.delete('type');
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }
}

