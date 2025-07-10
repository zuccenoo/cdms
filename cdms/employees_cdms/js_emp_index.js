////////////////////////////////////////////////SIDEBAR NA BAGO////////////////////////////////////////////////



//////////////////////////////////////////////////ADDRESS APA////////////////////////////////////////////////
// --- PSGC API Address Dropdowns ---

function populateSelect(selectId, items, placeholder) {
    const select = document.getElementById(selectId);
    select.innerHTML = `<option value="">${placeholder}</option>`;
    items.forEach(item => {
        select.innerHTML += `<option value="${item.code}">${item.name}</option>`;
    });
}
// When modal is shown, load regions and reset hidden fields
document.getElementById('addEmployeeModal').addEventListener('shown.bs.modal', function () {
    fetch('https://psgc.gitlab.io/api/regions/')
        .then(res => res.json())
        .then(data => {
            populateSelect('region', data, 'Select Region');
            document.getElementById('province').innerHTML = '<option value="">Select Province</option>';
            document.getElementById('city').innerHTML = '<option value="">Select City/Municipality</option>';
            document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>';
            // Reset hidden fields
            document.getElementById('region_name').value = '';
            document.getElementById('province_name').value = '';
            document.getElementById('city_name').value = '';
            document.getElementById('barangay_name').value = '';
        });
});
// When region changes, load provinces and set region name
document.getElementById('region').onchange = function() {
    const selected = this.options[this.selectedIndex];
    document.getElementById('region_name').value = selected.text;
    const code = this.value;
    fetch(`https://psgc.gitlab.io/api/regions/${code}/provinces/`)
        .then(res => res.json())
        .then(data => {
            populateSelect('province', data, 'Select Province');
            document.getElementById('province_name').value = '';
            document.getElementById('city').innerHTML = '<option value="">Select City/Municipality</option>';
            document.getElementById('city_name').value = '';
            document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>';
            document.getElementById('barangay_name').value = '';
        });
};
// When province changes, load cities/municipalities and set province name
document.getElementById('province').onchange = function() {
    const selected = this.options[this.selectedIndex];
    document.getElementById('province_name').value = selected.text;
    const code = this.value;
    fetch(`https://psgc.gitlab.io/api/provinces/${code}/cities-municipalities/`)
        .then(res => res.json())
        .then(data => {
            populateSelect('city', data, 'Select City/Municipality');
            document.getElementById('city_name').value = '';
            document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>';
            document.getElementById('barangay_name').value = '';
        });
};
// When city/municipality changes, load barangays and set city name
document.getElementById('city').onchange = function() {
    const selected = this.options[this.selectedIndex];
    document.getElementById('city_name').value = selected.text;
    const code = this.value;
    fetch(`https://psgc.gitlab.io/api/cities-municipalities/${code}/barangays/`)
        .then(res => res.json())
        .then(data => {
            populateSelect('barangay', data, 'Select Barangay');
            document.getElementById('barangay_name').value = '';
        });
};
// When barangay changes, set barangay name
document.getElementById('barangay').onchange = function() {
    const selected = this.options[this.selectedIndex];
    document.getElementById('barangay_name').value = selected.text;
};


////////////////////////////////////////////////ADD EMPLOYEE MODAL////////////////////////////////////////////////
function openAddEmployeeModal() {
    const now = new Date();
    // Optionally set a date/time preview if needed

    // Fetch the generated employee code from the server
    fetch('employee_index.php?generate_employee_code=1')
        .then(response => response.text())
        .then(employeeCode => {
            document.getElementById('employee_id_preview').value = employeeCode.trim();
        })
        .catch(error => console.error('Error fetching employee code:', error));

    // Show the modal using Bootstrap's modal API
    const addEmployeeModal = new bootstrap.Modal(document.getElementById('addEmployeeModal'));
    addEmployeeModal.show();
}

function addEmployee() {
    const form = document.getElementById('addEmployeeForm');
    const formData = new FormData(form);

    // Validate required fields
    const requiredFields = ['first_name', 'last_name', 'job_position', 'contact', 'sex', 'salary'];
    for (const field of requiredFields) {
        const input = document.getElementById(field);
        if (!input || !input.value.trim()) {
            alert(`Please fill out the ${field.replace('_', ' ')} field.`);
            return;
        }
    }

    fetch('employee_index.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => {
        if (response.ok) {
            // Wait for add modal to be fully hidden before showing success modal
            const addEmployeeModalEl = document.getElementById('addEmployeeModal');
            addEmployeeModalEl.addEventListener('hidden.bs.modal', function handler() {
                addEmployeeModalEl.removeEventListener('hidden.bs.modal', handler);
                showEmployeeSuccessModal();
            });
            closeAddEmployeeModal();
        } else {
            alert('Error adding employee. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding employee. Please try again.');
    });
}

////////////////////////////////////////////IMAGE PREVIEW////////////////////////////////////////////////
// Add preview for Add Employee modal
document.getElementById('employee_picture').addEventListener('change', function(event) {
    const preview = document.getElementById('employee_picture_preview');
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.src = '#';
        preview.style.display = 'none';
    }
});

// Add preview for Edit Employee modal
document.getElementById('edit_employee_picture').addEventListener('change', function(event) {
    const preview = document.getElementById('edit_employee_picture_preview');
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.src = '#';
        preview.style.display = 'none';
    }
});


//////////////////////////////////////////////////SUCCESS MODAL////////////////////////////////////////////////
function showEmployeeSuccessModal() {
    const successModal = new bootstrap.Modal(document.getElementById('employeeSuccessModal'));
    successModal.show();

    // Reload the page when the modal is hidden
    const modalElement = document.getElementById('employeeSuccessModal');
    const reloadOnHide = () => {
        location.reload();
        modalElement.removeEventListener('hidden.bs.modal', reloadOnHide);
    };
    modalElement.addEventListener('hidden.bs.modal', reloadOnHide);
}

function closeEmployeeSuccessModal() {
    const successModal = bootstrap.Modal.getInstance(document.getElementById('employeeSuccessModal'));
    if (successModal) {
        successModal.hide();
    }
}

//////////////////////////////////////////DELETE EMPLOYEE MODAL////////////////////////////////////////////////
// Open Delete Modal for Employee
function openDeleteEmployeeModal(employeeId) {
    // Find the row in the info table
    const rows = document.querySelectorAll('#info tbody tr');
    let employeeName = '';
    rows.forEach(row => {
        if (row.cells[0].innerText === employeeId) {
            employeeName = `${row.cells[1].innerText} ${row.cells[2].innerText} ${row.cells[3].innerText}`;
        }
    });

    document.getElementById('employeeIdToDelete').value = employeeId;
    document.getElementById('employeeNameToDelete').value = employeeName;

    const deleteEmployeeModal = new bootstrap.Modal(document.getElementById('deleteEmployeeModal'));
    deleteEmployeeModal.show();
}

// Confirm Delete
function deleteEmployeeConfirmed() {
    const employeeId = document.getElementById('employeeIdToDelete').value;

    fetch('employee_index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `employee_id=${encodeURIComponent(employeeId)}&delete_employee=1`
    })
    .then(response => {
        if (response.ok) {
            // Optionally show a success modal or just reload
            location.reload();
        } else {
            alert('Failed to delete employee.');
        }
    })
    .catch(error => {
        console.error('Error deleting employee:', error);
        alert('An error occurred while deleting the employee.');
    });
}

///////////////////////////////////////////////EDIT EMPLOYEE MODAL////////////////////////////////////////////////
// Open Edit Modal for Employee
function openEditEmployeeModal(employeeId) {
    // Find the row in the info table
    const rows = document.querySelectorAll('#info tbody tr');
    let employeeData = {};
    rows.forEach(row => {
        if (row.cells[0].innerText === employeeId) {
            employeeData = {
                employee_id: row.cells[0].innerText,
                // Photo is in cell 1 (img), so shift indices for other fields
                first_name: row.cells[2].innerText,
                middle_name: row.cells[3].innerText,
                last_name: row.cells[4].innerText,
                job_position: row.cells[5].innerText,
                contact: row.cells[6].innerText,
                address: row.cells[7].innerText,
                age: row.cells[8].innerText,
                sex: row.cells[9].innerText,
                // You may want to fetch salary via AJAX if not in the table
            };
            // Get photo src
            const img = row.cells[1].querySelector('img');
            employeeData.emp_picture = img ? img.src : '';
        }
    });

    // Populate modal fields
    document.getElementById('edit_employee_id').value = employeeData.employee_id || '';
    document.getElementById('edit_first_name').value = employeeData.first_name || '';
    document.getElementById('edit_middle_name').value = employeeData.middle_name || '';
    document.getElementById('edit_last_name').value = employeeData.last_name || '';
    document.getElementById('edit_job_position').value = employeeData.job_position || '';
    document.getElementById('edit_contact').value = employeeData.contact || '';
    document.getElementById('edit_age').value = employeeData.age || '';
    document.getElementById('edit_sex').value = employeeData.sex || '';

    // Set image preview
    const preview = document.getElementById('edit_employee_picture_preview');
    if (employeeData.emp_picture) {
        preview.src = employeeData.emp_picture;
        preview.style.display = 'block';
    } else {
        preview.src = '#';
        preview.style.display = 'none';
    }

    // Parse address string and set address fields
    // Address format: Region, Province, City, Barangay, Details
    const addressParts = (employeeData.address || '').split(',').map(s => s.trim());
    document.getElementById('edit_region_name').value = addressParts[0] || '';
    document.getElementById('edit_province_name').value = addressParts[1] || '';
    document.getElementById('edit_city_name').value = addressParts[2] || '';
    document.getElementById('edit_barangay_name').value = addressParts[3] || '';
    document.getElementById('edit_address_details').value = addressParts.slice(4).join(', ') || '';

    // Optionally, you can also set the select dropdowns for region/province/city/barangay
    // This requires fetching and setting the correct option values based on the names

    const editEmployeeModal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
    editEmployeeModal.show();
}