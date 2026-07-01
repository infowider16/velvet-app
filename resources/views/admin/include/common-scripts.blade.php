<script>

// ================================
// COMMON DELETE FUNCTION
// ================================

function commonDelete({
    id,
    url,
    table = null,
    button = null,
    message = 'Are you sure you want to delete this user?'
}) {

    Swal.fire({
        title: 'Delete User?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({

                url: url.replace(':id', id),

                type: 'DELETE',

                beforeSend: function () {

                    if (button) {
                        $(button).prop('disabled', true);
                    }
                },

                success: function (response) {

                    if (response.status == 1) {

                        toastr.success(response.message);

                       location.reload();

                    } else {

                        toastr.error(response.message);
                    }
                },

                error: function (xhr) {

                    let errorMessage = 'Something went wrong';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    toastr.error(errorMessage);
                },

                complete: function () {

                    if (button) {
                        $(button).prop('disabled', false);
                    }
                }
            });
        }
    });
}



// ================================
// COMMON STATUS CHANGE FUNCTION
// ================================

function commonStatusChange({
    id,
    status,
    url,
    table = null,
    button = null,
    extraData = {}
}) {

    let title = '';
    let text = '';
    let confirmButtonText = '';
    let confirmButtonColor = '';

    // Block / Unblock Message
    if (status == 1) {

        title = 'Block User?';
        text = 'Are you sure you want to block this user?';
        confirmButtonText = 'Yes, Block';
        confirmButtonColor = '#d33';

    } else {

        title = 'Unblock User?';
        text = 'Are you sure you want to unblock this user?';
        confirmButtonText = 'Yes, Unblock';
        confirmButtonColor = '#28a745';
    }

    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: confirmButtonColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Cancel'
    }).then((result) => {

        if (result.isConfirmed) {

            if (button) {
                $(button).prop('disabled', true);
            }

            $.ajax({

                url: url,

                type: 'POST',

                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    user_id: id,
                    status: status,
                    ...extraData
                },

                success: function (response) {

                    if (response.status == 1) {

                        toastr.success(response.message);
                        location.reload();

                    } else {

                        toastr.error(response.message);
                    }
                },

                error: function (xhr) {

                    let errorMessage = 'Something went wrong';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    toastr.error(errorMessage);
                },

                complete: function () {

                    if (button) {
                        $(button).prop('disabled', false);
                    }
                }
            });
        }
    });
}

</script>