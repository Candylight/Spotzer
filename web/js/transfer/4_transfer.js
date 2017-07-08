
var step_1_plateform_start = null;
var step_2_data = null;
var step_3_playlist = null;
var step_4_plateform_end = null;

function alert_data(){
    alert(step_1_plateform_start+" "+step_2_data+" "+step_3_playlist+" "+step_4_plateform_end);
}


function launchTransfer(){
    $.ajax({
        type:'GET',
        url: url_playlist,
        data: {playlist: $(this).val()},
        success: function(view) {
            $('.playlists-choices').html(view);
            step_2_data = choice;
            $('.content-3').addClass("active");
        }
    });
}

