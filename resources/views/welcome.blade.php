<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <div class="col-sm-12 col-md-5">
            <h2>Create User</h2>
            <form id="user-form">
                <div class="mb-3">
                    <label for="name" class="form-label">Name*</label>
                    <input type="text" class="form-control" id="name">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email*</label>
                    <input type="email" class="form-control" id="email">
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone*</label>
                    <input type="text" class="form-control" id="phone" Placeholder="+918503034551">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description*</label>
                    <textarea class="form-control" id="description"></textarea>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role*</label>
                    <select id="role" class="form-select">
                        <option value="">Select Role</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="profile_image" class="form-label">Profile Image*</label>
                    <input type="file" class="form-control" id="profile_image">
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
        <div class="col-sm-12 col-md-7">
            <h2>Users List</h2>
            <table class="table" id="users-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Description</th>
                        <th>Role</th>
                        <th>Profile Image</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include Toastr JS -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>

<script>
    // Initialize Toastr
    toastr.options = {
        "closeButton": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000",
        "progressBar": true
    };

    const base_url = `${window.location.href}`;    

    // Load roles and users on page load
    window.onload = function () {
        axios.get(base_url + 'api/users')
            .then(response => {
                const users = response.data;
                const tableBody = document.querySelector('#users-table tbody');
                tableBody.innerHTML = users.map(user => `
                    <tr>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td>${user.phone}</td>
                        <td>${user.description}</td>
                        <td>${user.role.name}</td>
                        <td><img src="${base_url}/${user.profile_image}" width="50" height="50" alt="Profile Image"></td>
                    </tr>
                `).join('');
            });

        axios.get(base_url + 'api/roles')
            .then(response => {
                const roles = response.data;
                const roleSelect = document.querySelector('#role');
                roles.forEach(role => {
                    const option = document.createElement('option');
                    option.value = role.id;
                    option.textContent = role.name;
                    roleSelect.appendChild(option);
                });
            });
    }

    // Client-side validation function
    function validateForm() {
        let isValid = true;
        let errorMessage = '';

        const name = $('#name').val();
        const email = $('#email').val();
        const phone = $('#phone').val();
        const role = $('#role').val();
        const description = $('#description').val();
        const profileImage = $('#profile_image')[0].files[0]; 

        if (name.trim() === '') {
            isValid = false;
            errorMessage += 'Name is required.\n';
        }

        const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailRegex.test(email)) {
            isValid = false;
            errorMessage += 'Please enter a valid email.\n';
        }

        const phoneRegex = /^(\+91)?[789]\d{9}$/;
        if (!phoneRegex.test(phone)) {
            isValid = false;
            errorMessage += 'Please enter a valid Indian phone number.\n';
        }

        if (role === '') {
            isValid = false;
            errorMessage += 'Role is required.\n';
        }

        if (description.trim() === '') {   
            isValid = false;
            errorMessage += 'Description is required.\n';
        }

        if (profileImage) {
            const allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif']; 
            if (!allowedImageTypes.includes(profileImage.type)) {
                isValid = false;
                errorMessage += 'Please upload a valid image (JPEG, PNG, GIF).\n';
            }
        } else {
            errorMessage += 'Profile image is required.\n';  
        }

        if (!isValid) {
            toastr.error(errorMessage.replace(/\n/g, '<br/>'), 'Validation Errors');
        }

        return isValid;
}


    // Form submission with validation
    $('#user-form').submit(function (e) {
        e.preventDefault();

        // Client-side validation
        if (!validateForm()) {
            return;
        }

        const formData = new FormData();
        formData.append('name', $('#name').val());
        formData.append('email', $('#email').val());
        formData.append('phone', $('#phone').val());
        formData.append('description', $('#description').val());
        formData.append('role_id', $('#role').val());
        formData.append('profile_image', $('#profile_image')[0].files[0]);

        axios.post(base_url + 'api/user', formData)
            .then(response => {
                toastr.success('User added successfully', 'Success');
                $('#user-form')[0].reset();
                loadUsers(); 
            })
            .catch(error => {
                if (error.response && error.response.status === 422) {                    
                    const errors = error.response.data.errors;
                    for (const key in errors) {
                        if (errors.hasOwnProperty(key)) {
                            toastr.error(errors[key].join(', '), 'Validation Error');
                        }
                    }
                } else {
                    toastr.error(error.response?.data?.message || 'Error occurred while adding user', 'Error');
                }
            });
    });

    // Load Users dynamically without page reload
    function loadUsers() {
        axios.get(base_url + 'api/users')
            .then(response => {
                const users = response.data;
                const tableBody = $('#users-table tbody');
                tableBody.empty();

                if (users.length === 0) {                    
                    tableBody.append('<tr><td colspan="6" class="text-center">No users found</td></tr>');
                } else {                    
                    users.forEach(user => {
                        tableBody.append(`
                            <tr>
                                <td>${user.name}</td>
                                <td>${user.email}</td>
                                <td>${user.phone}</td>
                                <td>${user.description}</td>
                                <td>${user.role.name}</td>
                                <td><img src="/storage/${user.profile_image}" width="50" height="50" alt="Profile Image"></td>
                            </tr>
                        `);
                    });
                }
            });
    }
</script>

</body>
</html>
