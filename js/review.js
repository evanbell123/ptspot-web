//helpful links
//http://plugins.krajee.com/star-rating#pre-requisites
//http://plugins.krajee.com/star-rating/demo

var clarityRating, effectivenessRating, motivationRating, intensityRating;

$(document).ready(function () {



    $("#clarity").rating({
        captionElement: "#clarity_caption",
        starCaptions: {
            1: "(1) Huh?",
            2: "(2) Confused",
            3: "(3) Somewhat Clear",
            4: "(4) Understood",
            5: "(5) Got It!"
        },
        starCaptionClasses: {
            1: "label label-info",
            2: "label label-info",
            3: "label label-primary",
            4: "label label-primary",
            5: "label label-success"
        }
    });

    $("#intensity").rating({
        captionElement: "#intensity_caption",
        starCaptions: {
            1: "(1) Boring",
            2: "(2) Broke A Sweat",
            3: "(3) Pushed Me",
            4: "(4) Couldn't Breath",
            5: "(5) Almost Died"
        },
        starCaptionClasses: {
            1: "label info",
            2: "label primary",
            3: "label success",
            4: "label warning",
            5: "label danger"
        }
    });

    $("#effectiveness").rating({
        captionElement: "#effectiveness_caption",
        starCaptions: {
            1: "(1) Haven't Changed At All",
            2: "(2) Barely Notice Anything",
            //3: "(3) Think I Look and Feel Better",
            3: "(3) Somewhat Effective",
            4: "(4) Definitely Notice Results",
            5: "(5) I'm a Whole New Person!"
        },
        starCaptionClasses: {
            1: "label label-info",
            2: "label label-info",
            3: "label label-primary",
            4: "label label-primary",
            5: "label label-success"
        }
    });

    $("#motivation").rating({
        captionElement: "#motivation_caption",
        starCaptions: {
            1: "(1) What Support?",
            2: "(2) Seemed Distracted ",
            3: "(3) Somewhat Supportive",
            4: "(4) Motivated Me",
            5: "(5) Made The Workout!"
        },
        starCaptionClasses: {
            1: "label label-info",
            2: "label label-info",
            3: "label label-primary",
            4: "label label-primary",
            5: "label label-success"
        }
    });

    // initialize overall rating
    $("#overall").rating({
        starCaptions: {
            .5: ".5 Stars",
            1: "1 Star",
            1.5: "1.5 Stars",
            2: "2 Stars",
            2.5: "2.5 Stars",
            3: "3 Stars",
            3.5: "3.5 Stars",
            4: "4 Stars",
            4.5: "4.5 Stars",
            5: "5 Stars",
        }
    });
    $("#overall").rating('update', 1);
    $("#overall").rating('refresh', {
        showClear: false,
        starCaptionClasses: function (val) {
            if (val <= 2) {
                return 'label label-info';
            }
            else if (val <= 4) {
                return 'label label-primary';
            }
            else {
                return 'label label-success';
            }
        }
    });

    $('#clarity').on('rating.change', function (event, value, caption) {
        clarityRating = value;
    });

    $('#effectiveness').on('rating.change', function (event, value, caption) {
        effectivenessRating = value;
    });

    $('#motivation').on('rating.change', function (event, value, caption) {
        motivationRating = value;
    });

    $('#intensity').on('rating.change', function (event, value, caption) {
        intensityRating = value;
    });
    
    var reviewForm = $("#trainer_review");

    $("#review_submit").click(function () {
        reviewForm.submit();
    });
    
    reviewForm.submit(function (event) {

            // Stop form from submitting normally
            event.preventDefault();

            $.ajax({
                url: rootURL + "account/review",
                beforeSend: function (xhr) {

                    xhr.setRequestHeader("Authorization", "Bearer " + Cookies.getJSON("PTSpot").access_token);
                },
                data: $(this).serialize() + '&' +
                        $.param({'clarity': clarityRating}) + '&' +
                        $.param({'effectiveness': effectivenessRating}) + '&' +
                        $.param({'motivation': motivationRating}) + '&' +
                        $.param({'intensity': intensityRating}) + '&' +
                        $.param({'comment': $("#comment").val()}),
                type: 'POST',
                contentType: 'application/x-www-form-urlencoded',
                dataType: 'json',
                statusCode: {
                    401: function () {
                        $('#loginModal').modal('show');
                    }
                },
                success: function (response) {
                    if (response.success === false) {
                        alert(response.message);
                    } else {
                        window.location.replace(rootURL + "profile");
                    }
                },
                error: function (response) {

                }
            });

        });


    var heights = $(".review_category").map(function () {
        return $(this).height();
    }).get(),
            maxHeight = Math.max.apply(null, heights);

    $(".review_category").height(maxHeight);


});

