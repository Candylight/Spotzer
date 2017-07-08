$(document).ready(function () {
    $("#plateform-end-choice").on('change', function(){
        step_4_plateform_end = $(this).val();
        $('.content-5').addClass("active");
    })
});