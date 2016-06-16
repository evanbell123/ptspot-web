var defaultText = "Get Fit";
var findTrainerText = "Find A Trainer In Your Area";
var findGymText = "Find A Trainer At Your Gym";
var rateTrainerText = "Your Trainer Fit For You?";

$(document).ready(function () {
    buttonDescriptionToggle();

    $("#find_trainer").click(function () {
        window.location.assign(rootURL + "search");
    });
});

function buttonDescriptionToggle() {
    $("#find_trainer").hover(function () {
        $("#button_description").html(findTrainerText);
    }, function () {
        $("#button_description").html(defaultText);
    });

    $("#find_gym").hover(function () {
        $("#button_description").html(findGymText);
    }, function () {
        $("#button_description").html(defaultText);
    });

    $("#rate_trainer").hover(function () {
        $("#button_description").html(rateTrainerText);
    }, function () {
        $("#button_description").html(defaultText);
    });

}
