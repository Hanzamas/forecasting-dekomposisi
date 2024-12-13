$(document).ready(function () {
    // Menambahkan efek animasi saat form berhasil dikirim
    $("form").submit(function (event) {
        let email = $("input[name='email']").val();
        let password = $("input[name='password']").val();

        if (!email || !password) {
            alert("Email dan Password harus diisi!");
            event.preventDefault();
        } else {
            $("button[type='submit']").html("Loading...").attr("disabled", true);
        }
    });

    // Mengatasi form validation
    $("input").on("input", function () {
        if ($(this).val().length > 0) {
            $(this).removeClass("is-invalid");
        }
    });
});
