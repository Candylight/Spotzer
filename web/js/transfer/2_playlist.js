$(document).ready(function () {
    $("#playlist-choice").on('change', function(){
        var value = $(this).val();
        if( value != "null" ){
            step_2_playlist = value;
            $('.content-3').addClass("active");
        }
        else{
            alert("Veuillez selectionner une playlist pour continuer");
        }
    })
});