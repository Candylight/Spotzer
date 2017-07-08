$(document).ready(function () {
    $("#plateform-start-choice").on('change', function(){
        step_1_plateform_start = $(this).val();
        $('.content-2').addClass("active");
    })
});