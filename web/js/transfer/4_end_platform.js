$(document).ready(function () {
    $("#plateform-end-choice").on('change', function(){
        step_4_plateform_end = $(this).val();
        alert_data();
        $('.content-5').addClass("active");
    })
});