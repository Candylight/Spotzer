$(document).ready(function () {
    $("#playlist-choice").on('change', function(){
        step_3_playlist = $(this).val();
        $('.content-3').addClass("active");
    })
});