$(document).ready(function () {

    $("#register_form").validator().submit(function (event) {
        event.preventDefault()
        $("#register_button").replaceWith(getSpinner("register_spinner"));
        $.post(rootURL + 'account/register', $(this).serialize(), function (response) {

            if (response.success === true) {

                $('#registerModal').modal('hide');
                $('#loginModal').modal('show');
            } else {
                $("#register_spinner").replaceWith(getSubmitButton("register_button"));
                alert(response.error);
            }
        }, "json");
    });

    $("#login_form").submit(function (event) {
        event.preventDefault();
        $.ajax({
            url: rootURL + "account/login",
            beforeSend: function (xhr) {
                $("#login_button").replaceWith(getSpinner("login_spinner"));
                xhr.setRequestHeader("Authorization", "Basic " + btoa("TestClient:TestSecret"));
            },
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            //processData: false,
            data: '{"grant_type":"password", "username": "' + $("#loginEmail").val() + '", "password": "' + $("#loginPassword").val() + '"}',
            success: function (data) {

                if (data.hasOwnProperty("error")) {
                    $("#login_spinner").replaceWith(getSubmitButton("login_button"));
                    alert(data.error_description);
                } else {
                    //Store access token in cookie
                    Cookies.set("PTSpot", data, {expires: 1});

                    if (data.scope === "1") {
                        //fetch the trainer's unique id
                        $.ajax({
                            url: rootURL + "account/status",
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader("Authorization", "Bearer " + Cookies.getJSON("PTSpot").access_token);
                            },
                            type: 'POST',
                            dataType: 'json',
                            contentType: 'application/x-www-form-urlencoded',
                            success: function (response) {
                                Cookies.set("lastTrainerClicked", response.userID, {expires: 1});
                                window.location.assign(rootURL + "profile");
                            },
                            error: function () {
                                alert("Cannot get data");
                            }
                        });
                    } else {
                        location.reload();
                    }
                }
            },
            error: function () {
                $("#login_spinner").replaceWith(getSubmitButton("login_button"));
            }
        });
    });
});
function getSubmitButton(elementID) {
    return "<button type='submit' id='" + elementID + "' name='login_button' class='btn btn-primary'>Submit</button>";
}

function getSpinner(elementID) {
    return "<i id='" + elementID + "' class='fa fa-spinner fa-pulse'></i>";
}

/*
 * For example, a request to refresh an access token (Section 6) using
 the body parameters (with extra line breaks for display purposes
 only):
 
 POST /token HTTP/1.1
 Host: server.example.com
 Content-Type: application/x-www-form-urlencoded
 
 grant_type=refresh_token&refresh_token=tGzv3JOkF0XG5Qx2TlKWIA
 &client_id=s6BhdRkqt3&client_secret=7Fjfp0ZBr1KtDRbnfVdmIw
 */