$(document).ready(function () {
    $(".plateform-choice").on("click", function(){
        $(".plateform-choice").removeClass("active");
        $(this).addClass("active");

        var value = $(this).data("value");
        $("#appbundle_user_preferedPlatform").val(value);
    });
});