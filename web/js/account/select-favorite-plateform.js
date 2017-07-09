$(".plateform-choice").click( function(){
    $(".plateform-choice").removeClass("active");
    $(this).addClass("active");

    var value = $(this).data("value");
    $("#appbundle_user_preferedPlatform").val(value);
});