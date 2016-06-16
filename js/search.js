

$(document).ready(function () {
    $.post(rootURL + 'api/search', function (response) {
        if (response !== "fail") {
            //sessionStorage.clear();
            if (response) {
                displaySearchResultPage();
                sessionStorage.setItem("trainerResults", JSON.stringify(response)); //save trainer results
                displayTrainerResults(response);
                makeSortable(response);
                makeSearchable();
            } else {
                displayNoTrainers();
            }
        }
    }, "json");
});

function makeSearchable() {

    //bold search characters
    $.fn.wrapInTag = function (opts) {
        var tag = opts.tag || 'strong',
                words = opts.words || [],
                regex = RegExp(words.join('|'), 'gi'), // case insensitive
                replacement = '<' + tag + '>$&</' + tag + '>';

        return this.html(function () {
            return $(this).text().replace(regex, replacement);
        });
    };

    //disable enter key
    $('#search_form').on('keyup keypress', function (e) {
        var code = e.keyCode || e.which;
        if (code === 13) {
            e.preventDefault();
            return false;
        }
    });

    $('#search').keyup(function (e) {
        var searchField = $('#search').val();
        var regex = new RegExp(searchField, "i");
        var name = "";
        var avatarElementID = "";
        var defaultAvatar = "";
        var trainerResult = JSON.parse(sessionStorage.getItem("trainerResults"));
        $("#trainer_results").html("");
        $.each(trainerResult, function (key, val) {
            name = val.firstName + " " + val.lastName;
            if (name.search(regex) !== -1) {
                avatarElementID = "search_avatar" + val.id;
                defaultAvatar = 'https://placehold.it/85x85';
                $("#trainer_results").append(displayTrainer(val, avatarElementID, defaultAvatar));
                readBlobFromStorage(val.id, avatarElementID, defaultAvatar);
            }

            $('.trainer_name').wrapInTag({
                tag: 'strong',
                words: [searchField]
            });

            $(".trainer_result").click(function () {
                Cookies.set("lastTrainerClicked", this.id, {expires: 1});
                window.location.assign(rootURL + "profile");
            });
        });
    });
}

function makeSortable(trainerResults) {
    $("#average_rating_sort").click(function () {
        searchSortBy(trainerResults, "rating");
    });

    $("#total_reviews_sort").click(function () {
        searchSortBy(trainerResults, "totalReviews");
    });

    $("#ptscore_sort").click(function () {
        displayTrainerResults(trainerResults);
    });
}

function searchSortBy(trainerJSON, category) {
    var temp = _.sortBy(trainerJSON, category);
    displayTrainerResults(temp.reverse());

}

function displayTrainerResults(trainers) {
    $("#trainer_results").html("");
    var avatarElementID = "";
    var defaultAvatar = 'https://placehold.it/85x85';

    for (var i = 0; i < trainers.length; i++) {
        avatarElementID = "search_avatar" + trainers[i].id;
        $("#trainer_results").append(displayTrainer(trainers[i], avatarElementID, defaultAvatar));
        readBlobFromStorage(trainers[i].id, avatarElementID, defaultAvatar);
    }
    
    $(".trainer_result").click(function () {
        Cookies.set("lastTrainerClicked", this.id, {expires: 1});
        window.location.assign(rootURL + "profile");
    });
}

function displayTrainer(trainer, avatarElementID, defaultAvatar) {
    var clarityColor = determineColor(trainer.clarity);
    var effectivenessColor = determineColor(trainer.effectiveness);
    var motivationColor = determineColor(trainer.motivation);
    var intensityDisplay = determineIntensityDisplay(trainer.intensity);
    //var intensityColor = intensityDisplay.color;
    var overallColor = determineColor(trainer.rating);

    var html = "";
    html += "<tr class='trainer_result' id='" + trainer.id + "'>";
    html += "<td class='col-md-2 results_photo text-center'><img id='" + avatarElementID + "' src='" + defaultAvatar + "' alt='trainer avatar' class='img-circle img-responsive img-thumbnail avatar-view search_avatar center-block'>";
    html += "<span class='trainer_name'>" + trainer.firstName + " " + trainer.lastName + "</span></td>";

    html += "<td class='col-md-4'>";
    html += "<table class='table table-condensed category_table'>";
    if (trainer.rating >= 1) {
        html += "<tr>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_left " + clarityColor + "'>Clarity</td>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_right " + clarityColor + "'>" + trainer.clarity + "/5" + "</td>";
        html += "</tr><tr>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_left " + effectivenessColor + "'>Effectiveness</td>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_right " + effectivenessColor + "'>" + trainer.effectiveness + "/5" + "</td>";
        html += "</tr><tr>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_left " + motivationColor + "'>Motivation</td>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_right " + motivationColor + "'>" + trainer.motivation + "/5" + "</td>";
        html += "</tr><tr>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_left " + intensityDisplay.color + "'>Intensity</td>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_right " + intensityDisplay.color + "'>" + intensityDisplay.message + "</td>";
        html += "</tr></table></td>";
        html += "<td class='col-md-2 custom_label " + overallColor + "'>" + trainer.rating + "</td>";
        html += "<td class='col-md-2 custom_label info'>" + trainer.totalReviews + "</td>";
        html += "<td class='col-md-2 custom_label info'>" + trainer.PTScore + "</td>";
        html += "</tr>";
    } else {
        html += "<tr>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_left na'>Clarity</td>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_right na'>N/A</td>";
        html += "</tr><tr>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_left na'>Effectiveness</td>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_right na'>N/A</td>";
        html += "</tr><tr>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_left na'>Motivation</td>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_right na'>N/A</td>";
        html += "</tr><tr>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_left na'>Intensity</td>";
        html += "<td class='col-sm-6 col-md-6 custom_label category_right na'>N/A</td>";
        html += "</tr></table></td>";
        html += "<td class='col-md-2 custom_label na'>N/A</td>";
        html += "<td class='col-md-2 custom_label na'>0</td>";
        html += "<td class='col-md-2 custom_label na'>N/A</td>";
        html += "</tr>";
    }
    return html;
}

function displayNoTrainers() {
    $(".page_content").html("<h1 class='text-center'>No trainers yet. Be the first to signup!</h1>");
}

function displaySearchResultPage() {
    var html = "";
    html += "<form id='search_form' role='form'>";
    html += "<div class='form-group'>";
    html += "<input type='text' class='form-control input-lg' id='search' placeholder='Find a trainer'>";
    html += "</div>";
    html += "</form>";
    html += "<table class='table table-condensed results_table'>";
    html += "<thead>";
    html += "<tr>";
    html += "<th class='col-sm-2 col-md-2 col-lg-2'></th>";
    html += "<th class='col-sm-4 col-md-4 col-lg-4'></th>";
    html += "<th id='average_rating_sort' class='col-sm-2 col-md-2 col-lg-2 clickable hvr-grow'>Overall <i class='fa fa-sort-amount-desc'></i></th>";
    html += "<th id='total_reviews_sort' class='col-sm-2 col-md-2 col-lg-2 clickable hvr-grow'>Total Reviews <i class='fa fa-sort-amount-desc'></i></th>";
    html += "<th id='ptscore_sort' class='col-sm-2 col-md-2 col-lg-2 clickable hvr-grow'>PTScore <i class='fa fa-sort-amount-desc'></i></th>";
    html += "</tr>";
    html += "</thead>";
    html += "<tbody id='trainer_results'>";
    html += "</tbody>";
    html += "</table>";

    $(".page_content").html(html);
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

 