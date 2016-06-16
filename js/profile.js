
var reviewCategoryCol = "col-sm-2 col-md-2 col-lg-2";
var ratingCol = "col-sm-2 col-md-2 col-lg-2";

$(document).ready(function () {

    if (Cookies.getJSON("lastTrainerClicked")) {
        $.ajax({
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Authorization", "Bearer " + Cookies.getJSON("PTSpot").access_token);
            },
            url: rootURL + "account/profile/" + Cookies.getJSON("lastTrainerClicked"),
            type: 'POST',
            contentType: 'application/x-www-form-urlencoded',
            dataType: 'json',
            statusCode: {
                401: function () {
                    window.location.replace(rootURL + "search");
                }
            },
            success: function (response) {
                if (response.success !== false) {
                    if (response.reviews) {
                        displayReviews(response.reviews);
                    } else {
                        $("#overall_rating_stars").html("");
                    }
                    
                    var linkID = "websiteProfile";
                    var linkIcon = "fa fa-globe";

                    if (_.isNull(response.contact)) {
                        var link = null;
                    } else {
                        var link = response.contact.link;
                    }





                    if (Cookies.getJSON("status").loggedIn === true
                            && Cookies.getJSON("status").userID === Cookies.getJSON("lastTrainerClicked")) { //if the current page is the user's profile and the user is logged in
                        $("#content").addClass("cssEffects");
                        if (!response.reviews) {
                            displayNoReviews("You have no reviews yet.");
                        }

                        if (link) {
                            displayContactInfo(linkID, linkIcon, link);
                            //$("#websiteProfile").parent().attr("href", link);
                        } else {
                            displayContactInfo(linkID, "fa fa-plus", "Link to your website");
                        }

                    } else {
                        $(".avatar-view").removeAttr("data-toggle").removeAttr("title").removeAttr("data-placement");
                        if (!response.reviews) {
                            displayNoReviews(response.summary[0].firstName + " has no reviews yet. You can be the first!");
                        }

                        if (link) {
                            displayContactInfo("websiteProfile", linkIcon, "Visit My Website");
                            $("#websiteProfile").click(function () {
                                var win = window.open(link, '_blank');
                                if (win) {
                                    //Browser has allowed it to be opened
                                    win.focus();
                                } else {
                                    //Broswer has blocked it
                                    alert('Please allow popups for this site');
                                }
                            });
                        }
                    }

                    displayTrainerProfile(response.summary[0]);



                } else {
                    console.log(response.message);
                }
            },
            error: function () {
            }
        });
    } else {
        window.location.replace(rootURL + "search");
    }
});

function displayContactInfo(id, icon, contactInfo) {
    $("#contact_info").append("<li class='clickable editable_container'><a><i class='" + icon + "'></i> <span id='" + id + "'>" + contactInfo + "</span></a></li>");
}

function displayNoReviews(message) {
    $("#reviews_tab").html("<h2>" + message + "</h2>");
}

var makeEditable = function () {
    $(function () {
        $('[data-toggle="tooltip"]').tooltip(); //enable tooltips
    });

    $("#review_button_container").html("");
    $('#firstNameProfile').addClass("clickable");
    $('#lastNameProfile').addClass("clickable");
    $('#emailProfile').addClass("clickable");

    $(".editable_container").find("span").after(" <i class='fa fa-pencil edit_icon'></i>");

    $.fn.editable.defaults.mode = 'popup';

    //Make the first name variable editable
    $(function () {
        $('#firstNameProfile').editable({
            title: 'Enter your first name',
            success: function (response, firstName) {
                $.ajax({
                    url: rootURL + "account/edit",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader("Authorization", "Bearer " + Cookies.getJSON("PTSpot").access_token);
                    },
                    type: 'POST',
                    dataType: 'json',
                    contentType: 'application/x-www-form-urlencoded',
                    data: $.param({'firstName': firstName}),
                    statusCode: {
                        401: function () {
                            $('#loginModal').modal('show');
                        }
                    },
                    success: function () {

                    },
                    error: function () {

                    }
                });
            }
        });
    });

    //Make the last name variable editable
    $(function () {
        $('#lastNameProfile').editable({
            title: 'Enter your last name',
            success: function (response, lastName) {
                $.ajax({
                    url: rootURL + "account/edit",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader("Authorization", "Bearer " + Cookies.getJSON("PTSpot").access_token);
                    },
                    type: 'POST',
                    dataType: 'json',
                    contentType: 'application/x-www-form-urlencoded',
                    data: $.param({'lastName': lastName}),
                    statusCode: {
                        401: function () {
                            $('#loginModal').modal('show');
                        }
                    },
                    success: function () {
                        //window.location.replace(rootURL + "profile");
                    },
                    error: function () {

                    }
                });
            }
        });
    });

    //Make the email variable editable
    $(function () {
        $('#emailProfile').editable({
            title: 'Enter your email address',
            success: function (response, email) {
                $.ajax({
                    url: rootURL + "account/editEmail",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader("Authorization", "Bearer " + Cookies.getJSON("PTSpot").access_token);
                    },
                    type: 'POST',
                    dataType: 'json',
                    contentType: 'application/x-www-form-urlencoded',
                    data: $.param({'email': email}),
                    statusCode: {
                        401: function () {
                            $('#loginModal').modal('show');
                        }
                    },
                    success: function (response) {
                        if (response.success === false) {
                            alert(response.message);
                        }
                    },
                    error: function () {

                    }
                });
            }
        });
    });

    //Make the website link variable editable
    $(function () {
        $('#websiteProfile').editable({
            title: 'Enter the full URL for your website',
            placeholder: "http://www.example.com",
            success: function (response, link) {
                $.ajax({
                    url: rootURL + "account/editWebsiteLink",
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader("Authorization", "Bearer " + Cookies.getJSON("PTSpot").access_token);
                    },
                    type: 'POST',
                    dataType: 'json',
                    contentType: 'application/x-www-form-urlencoded',
                    data: $.param({'link': link}),
                    statusCode: {
                        401: function () {
                            $('#loginModal').modal('show');
                        }
                    },
                    success: function (response) {
                        if (response.success === false) {
                            alert(response.message);
                        }
                    },
                    error: function () {

                    }
                });
            }
        });
    });

    loadScript("https://cdnjs.cloudflare.com/ajax/libs/cropper/2.2.4/cropper.min.js", makeAvatarEditable);
};

var makeAvatarEditable = function () {
    $(".avatar-view").addClass("clickable");
    //data-toggle="tooltip" title="Upload A Profile Pic"
    //$(".avatar-view").attr("data-toggle", "tooltip").attr("title", "Upload A Profile Pic");

    var html = "";
    html += "<!-- Cropping modal -->";
    html += "<div class='modal fade' id='avatar-modal' aria-hidden='true' aria-labelledby='avatar-modal-label' role='dialog' tabindex='-1'>";
    html += "<div class='modal-dialog modal-lg'>";
    html += "<div class='modal-content'>";
    html += "<form class='avatar-form' enctype='multipart/form-data'>";
    html += "<div class='modal-header'>";
    html += "<button type='button' class='close' data-dismiss='modal'>&times;</button>";
    html += "<h4 class='modal-title' id='avatar-modal-label'>Change Avatar</h4>";
    html += "</div>";
    html += "<div class='modal-body'>";
    html += "<div class='avatar-body'>";

    html += "<!-- Upload image and data -->";
    html += "<div class='avatar-upload'>";
    html += "<input type='hidden' class='avatar-src' name='avatar_src'>";
    html += "<input type='hidden' class='avatar-data' name='avatar_data'>";
    html += "<label for='avatarInput'>Local upload</label>";
    html += "<input type='file' class='avatar-input' id='avatarInput' name='avatar_file'>";
    html += "</div>";

    html += "<!-- Crop and preview -->";
    html += "<div class='row'>";
    html += "<div class='col-md-9'>";
    html += "<div class='avatar-wrapper'></div>";
    html += "</div>";
    html += "<div class='col-md-3'>";
    html += "<div class='avatar-preview preview-lg'></div>";
    html += "<div class='avatar-preview preview-md'></div>";
    html += "<div class='avatar-preview preview-sm'></div>";
    html += "</div>";
    html += "</div>";

    html += "<div class='row avatar-btns'>";
    html += "<div class='col-md-9'>";
    html += "<div class='btn-group'>";
    html += "<button type='button' class='btn btn-primary' data-method='rotate' data-option='-90' title='Rotate -90 degrees'>Rotate Left</button>";
    html += "<button type='button' class='btn btn-primary' data-method='rotate' data-option='-15'>-15deg</button>";
    html += "<button type='button' class='btn btn-primary' data-method='rotate' data-option='-30'>-30deg</button>";
    html += "<button type='button' class='btn btn-primary' data-method='rotate' data-option='-45'>-45deg</button>";
    html += "</div>";
    html += "<div class='btn-group'>";
    html += "<button type='button' class='btn btn-primary' data-method='rotate' data-option='90' title='Rotate 90 degrees'>Rotate Right</button>";
    html += "<button type='button' class='btn btn-primary' data-method='rotate' data-option='15'>15deg</button>";
    html += "<button type='button' class='btn btn-primary' data-method='rotate' data-option='30'>30deg</button>";
    html += "<button type='button' class='btn btn-primary' data-method='rotate' data-option='45'>45deg</button>";
    html += "</div>";
    html += "</div>";
    html += "<div class='col-md-3'>";
    html += "<button type='submit' class='btn btn-primary btn-block avatar-save'>Done</button>";
    html += "</div>";
    html += "</div>";
    html += "</div>";
    html += "</div>";
    html += "<!-- <div class='modal-footer'>";
    html += "<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>";
    html += "</div> -->";
    html += "</form>";
    html += "</div>";
    html += "</div>";
    html += "</div><!-- /.modal -->";

    html += "<!-- Loading state -->";
    html += "<div class='loading' aria-label='Loading' role='img' tabindex='-1'></div>";

    $("#crop-avatar").append(html);

    $("#crop-avatar-container").append("<script src='js/avatar.js'></script>");

};

function getAvatarSubmitButton() {
    return "<button id='avatar_submit' type='button' class='btn btn-primary avatar_submit'><i class='fa fa-upload'></i> Submit</button>";
}

//for previewing avatar before submit
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#profile_avatar').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function reviewSortBy(category) {
    var temp = _.sortBy(JSON.parse(sessionStorage.getItem("trainerReviews")), category);
    displayAllReviews(temp.reverse());
}

function displayTrainerProfile(summary) {
    readBlobFromStorage(summary.id, "profile_avatar", "https://placehold.it/120x120");

    if (summary.hasOwnProperty("email")) {
        $("#emailProfile").html(summary.email);
    } else {
        $("#emailProfile").html(" Contact");
    }

    $("#firstNameProfile").html(summary.firstName);
    $("#lastNameProfile").html(summary.lastName);

    if (summary.rating >= 1) {
        $("#clarityProfile").html(summary.clarity + "/5");
        $("#effectivenessProfile").html(summary.effectiveness + "/5");
        $("#motivationProfile").html(summary.motivation + "/5");

        var intensityInfo = determineIntensityDisplay(summary.intensity);

        $("#intensityProfile").html(intensityInfo.message);
        $("#ratingProfile").html(summary.rating + "/5");
        $("#overall").rating('update', summary.rating);
        $("#clarityProfile").addClass(determineColor(summary.clarity));
        $("#effectivenessProfile").addClass(determineColor(summary.effectiveness));
        $("#motivationProfile").addClass(determineColor(summary.motivation));
        $("#intensityProfile").addClass(intensityInfo.color);
        $("#ratingProfile").addClass(determineColor(summary.rating));
    }
}

/*
 * does each review really need its own id?
 */
function displayAllReviews(reviews) {
    $("#reviews").html("");
    for (var i = 0; i < reviews.length; i++) {
        $("#reviews").append(displayReview(reviews[i]));
        $("#review" + reviews[i].id).hover(function () {
            $(this).find("i").removeClass("fa fa-comment-o");
            $(this).find("i").addClass("fa fa-comment");
        }, function () {
            $(this).find("i").removeClass("fa fa-comment");
            $(this).find("i").addClass("fa fa-comment-o");
        });
    }
}

function displayReview(review) {
    var dataTarget = "reviewCollapse" + review.id;
    var collapseReviewCol = "col-sm-6 col-md-6 col-lg-6";
    var html = "";
    html += "<tr id='review" + review.id + "' role='button' data-toggle='collapse' data-target='#" + dataTarget + "' class='accordion-toggle'>";

    var intensityInfo = determineIntensityDisplay(review.intensity);
    html += "<td class='custom_label " + reviewCategoryCol + " " + intensityInfo.color + "'>" + intensityInfo.message + "</td>";
    html += "<td class='custom_label " + reviewCategoryCol + " " + determineColor(review.clarity) + "'>" + review.clarity + "</td>";
    html += "<td class='custom_label " + reviewCategoryCol + " " + determineColor(review.effectiveness) + "'>" + review.effectiveness + "</td>";
    html += "<td class='custom_label " + reviewCategoryCol + " " + determineColor(review.motivation) + "'>" + review.motivation + "</td>";
    html += "<td class='col-md-1 text-center comment'><i class='fa fa-comment-o'></i></td>";
    html += "<td class='custom_label " + ratingCol + " " + determineColor(review.rating) + "'>" + review.rating + "</td>";
    html += "</tr>";
    html += "<tr class='collapsed_container'>";
    html += "<td colspan='6'>";
    html += "<div id='" + dataTarget + "' class='accordion-body collapse'>";
    html += "<table class='table table-condensed'>";
    html += "<thead>";
    html += "<tr>";
    html += "<th class='" + collapseReviewCol + "'><span class='pull-left'>" + moment(review.date, 'YYYY-MM-DD HH:mm:ss').fromNow() + "</span></th>";
    if (review.recommend === "1") {
        html += "<th class='" + collapseReviewCol + "'><span class='pull-right'>Would Recommend   <i class='fa fa-check'></i></span></th>";
    }
    html += "</tr>";
    html += "</thead>";
    html += "<tbody>";
    html += "<tr><td colspan='3'>" + review.comment + "</td></tr>";
    html += "</tbody>";
    html += "</table>";
    html += "</div>";
    html += "</td>";

    //html += "<td colspan='6'><div id='recommend" + review.id + "'>" + "afd" + "</div></td>";
    html += "</tr>";
    return html;
}


function displayReviews(reviews) {
    var html = "";
    html += "<table class='table table-condensed'>";
    html += "<thead>";
    html += "<tr>";
    html += "<th class='custom_label " + reviewCategoryCol + "' id='intensityProfile'></th>";
    html += "<th class='custom_label " + reviewCategoryCol + "' id='clarityProfile'></th>";
    html += "<th class='custom_label " + reviewCategoryCol + "' id='effectivenessProfile'></th>";
    html += "<th class='custom_label " + reviewCategoryCol + "' id='motivationProfile'></th>";
    html += "<th class='col-sm-1 col-md-1 col-lg-1'></th>";
    html += "<th class='custom_label " + ratingCol + "' id='ratingProfile'></th>";
    html += "</tr>";
    html += "<tr>";
    html += "<th id='clarity_sort' class='" + reviewCategoryCol + " clickable hvr-grow'>Intensity <i class='fa fa-sort-amount-desc'></i></th>";
    html += "<th id='effectiveness_sort' class='" + reviewCategoryCol + " clickable hvr-grow'>Clarity <i class='fa fa-sort-amount-desc'></i></th>";
    html += "<th id='motivation_sort' class='" + reviewCategoryCol + " clickable hvr-grow'>Effectiveness <i class='fa fa-sort-amount-desc'></i></th>";
    html += "<th id='intensity_sort' class='" + reviewCategoryCol + " clickable hvr-grow'>Motivation <i class='fa fa-sort-amount-desc'></i></th>";
    html += "<th class='col-sm-1 col-md-1 col-lg-1'></th>";
    html += "<th id='rating_sort' class='" + ratingCol + " clickable hvr-grow'>Overall <i class='fa fa-sort-amount-desc'></i></th>";
    html += "</tr>";
    html += "</thead>";
    html += "<tbody id='reviews'>";
    html += "</tbody>";
    html += "</table>";

    $("#reviews_tab").html(html);

    sessionStorage.setItem("trainerReviews", JSON.stringify(reviews));

    displayAllReviews(reviews);
    $("#clarity_sort").click(function () {
        reviewSortBy("clarity");
    });
    $("#effectiveness_sort").click(function () {
        reviewSortBy("effectiveness");
    });
    $("#motivation_sort").click(function () {
        reviewSortBy("motivation");
    });
    $("#intensity_sort").click(function () {
        reviewSortBy("intensity");
    });
    $("#rating_sort").click(function () {
        displayAllReviews(JSON.parse(sessionStorage.getItem("trainerReviews")));
    });
}

function determineColor(rating) {
    if (rating <= 1) {
        return "danger";
    }
    if (rating < 2) {
        return "warning";
    }
    if (rating < 3) {
        return "info";
    }
    if (rating < 4) {
        return "primary";
    }
    if (rating <= 5) {
        return "success";
    }
}

function determineIntensityDisplay(rating) {
    if (rating < 2) {
        return {color: "info", message: "Boring"};
    }
    if (rating < 3) {
        return {color: "primary", message: "Broke A Sweat"};
    }
    if (rating <= 4) {
        return {color: "success", message: "Pushed Me"};
    }
    if (rating < 5) {
        return {color: "warning", message: "Couldn't Breath"};
    }
    if (rating == 5) {
        return {color: "danger", message: "Almost Died"};
    }
}