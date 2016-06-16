

$(document).ready(function () {
    $(".home_link").attr("href", rootURL);

    $("#register_link").click(function () {
        $('#loginModal').modal('hide');
        $('#registerModal').modal('show');
    });

    if (!Cookies.getJSON("PTSpot")) {
        var accessToken = new Object();
        accessToken.access_token = "";
        Cookies.set("PTSpot", accessToken, {expires: 1});
    }

    if (!Cookies.getJSON("status")) {
        var status = new Object();
        status.loggedIn = false;
        status.userID = -1;
        Cookies.set("status", status, {expires: 1});
    }

    if (!Cookies.getJSON("lastTrainerClicked")) {
        Cookies.set("lastTrainerClicked", "", {expires: 5});
    }

    if (Cookies.getJSON("PTSpot")) {
        $.ajax({
            url: rootURL + "account/status",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Authorization", "Bearer " + Cookies.getJSON("PTSpot").access_token);
            },
            type: 'POST',
            contentType: 'application/x-www-form-urlencoded',
            dataType: 'json',
            success: function (response) {

                Cookies.set("status", response, {expires: 1});

                if (response.loggedIn === false) { // if the user is not logged in

                    var html = "";
                    html += "<li><a href='#loginModal' data-toggle='modal' data-target='#loginModal'><span class='glyphicon glyphicon-log-in'></span> Log in</a></li>";
                    html += "<li><a href='#registerModal' data-toggle='modal' data-target='#registerModal'><span class='glyphicon glyphicon-user'></span> Sign up</a></li>";
                    $("#account_header").html(html);

                    $("#review_button_container").html("<a role='button' class='btn btn-default pull-right' id='review_button'>Fit For You? Leave A Review!</a>");

                    $("#review_button").addClass("login_modal_show");
                    $("#contact_pill").addClass("login_modal_show");

                    $(".login_modal_show").click(function () {
                        $('#loginModal').modal('show');
                    });



                } else { // the user is logged in

                    var html = "";

                    if (Cookies.getJSON("PTSpot").scope === "1") { // If the user is a trainer
                        html += "<li><a id='profile_link' class='clickable'><span class='glyphicon glyphicon-user'></span> Profile</a></li>";
                    }

                    html += "<li><a id='logout_button' class='clickable'><span class='glyphicon glyphicon-log-out'></span> Log Out</a></li>";
                    $("#account_header").html(html);

                    $("#profile_link").click(function () {
                        Cookies.set("lastTrainerClicked", response.userID, {expires: 1});
                        window.location.assign(rootURL + "profile");
                    });

                    $("#logout_button").click(function () {
                        $.ajax({
                            url: rootURL + "account/logout",
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader("Authorization", "Bearer " + Cookies.getJSON("PTSpot").access_token);
                            },
                            type: 'POST',
                            dataType: 'json',
                            contentType: 'application/x-www-form-urlencoded',
                            processData: false,
                            success: function () {
                                Cookies.remove("PTSpot");
                                window.location.replace(rootURL);
                            },
                            error: function () {

                            }
                        });
                    });

                    if (window.location.href === rootURL + "profile") { //if the current page is a profile page
                        if (response.userID !== Cookies.getJSON("lastTrainerClicked")) {// the current profile is not the user's profile page
                            
                            /*
                             * Get the status of the review meaning
                             * has the user reviewed this trainer or not?
                             */
                            $.ajax({
                                url: rootURL + "account/reviewStatus",
                                beforeSend: function (xhr) {
                                    xhr.setRequestHeader("Authorization", "Bearer " + Cookies.getJSON("PTSpot").access_token);
                                },
                                type: 'POST',
                                dataType: 'json',
                                contentType: 'application/x-www-form-urlencoded',
                                statusCode: {
                                    401: function () {
                                        $('#loginModal').modal('show');
                                    }
                                },
                                success: function (response) {
                                    if (response.reviewed === false) { // If the user has not reviewed this trainer
                                        $("#review_button_container").html("<a role='button' class='btn btn-default' id='review_button'>Fit For You? Leave A Review!</a>");
                                        $("#review_button").click(function () {

                                            $.ajax({
                                                url: rootURL + "account/status",
                                                beforeSend: function (xhr) {
                                                    xhr.setRequestHeader("Authorization", "Bearer " + Cookies.getJSON("PTSpot").access_token);
                                                },
                                                type: 'POST',
                                                dataType: 'json',
                                                contentType: 'application/x-www-form-urlencoded',
                                                statusCode: {
                                                    401: function () {
                                                        $('#loginModal').modal('show');
                                                    }
                                                },
                                                success: function () {
                                                    window.location.replace(rootURL + "review");
                                                },
                                                error: function () {

                                                }
                                            });
                                        });
                                    } else { // The user has reviewed this trainer
                                        $("#review_button_container").html("<strong class='label label-" + determineColor(response.rating) + " pull-right'>Reviewed  <span class='glyphicon glyphicon-ok'></span></strong>");
                                    }
                                },
                                error: function () {

                                }
                            });
                        } else {
                            loadScript("//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js", makeEditable);
                            
                        }
                    } else if (window.location.href === rootURL + "review") { //If the current page is the review page
                        readBlobFromStorage(Cookies.getJSON("lastTrainerClicked"), "review_avatar", "https://placehold.it/46x46");
                    }
                }
            }
        });
    }
});


function loadScript(url, callback)
{
    // Adding the script tag to the head as suggested before
    var head = document.getElementsByTagName('head')[0];
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;

    // Then bind the event to the callback function.
    // There are several events for cross browser compatibility.
    script.onreadystatechange = callback;
    script.onload = callback;

    // Fire the loading
    head.appendChild(script);
}

function readBlobFromStorage(userID, elementID, defaultSRC) {

    var storageName = "avatar" + userID;

    var blobStorage = sessionStorage.getItem(storageName);
    
    if (blobStorage) {
        // Reuse existing Data URL from localStorage
        document.getElementById(elementID).setAttribute("src", blobStorage);
    }
    else {
        // Create XHR and FileReader objects
        var xhr = new XMLHttpRequest();
        var fileReader = new FileReader();

        xhr.open("GET", rootURL + "api/avatar/" + userID, true);

        xhr.responseType = "blob";

        xhr.addEventListener("load", function () {
            if (xhr.status === 200) {
                if (xhr.response.size > 0) {
                    // onload needed since Google Chrome doesn't support addEventListener for FileReader
                    fileReader.onload = function (evt) {
                        // Read out file contents as a Data URL
                        var result = evt.target.result;
                        
                        // Set image src to Data URL
                        document.getElementById(elementID).setAttribute("src", result);
                        // Store Data URL in localStorage
                        try {
                            sessionStorage.setItem(storageName, result);
                        }
                        catch (e) {
                            console.log("Storage failed: " + e);
                        }


                    };
                    // Load blob as Data URL
                    fileReader.readAsDataURL(xhr.response);
                } else {
                    // Set image src to default
                    document.getElementById(elementID).setAttribute("src", defaultSRC);
                }
            }
        }, false);
        // Send XHR
        xhr.send();
    }
}