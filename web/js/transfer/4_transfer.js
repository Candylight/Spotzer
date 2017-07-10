
var step_1_plateform_start = null;
var step_2_playlist = null;
var step_3_plateform_end = null;

function alert_data(){
    alert(step_1_plateform_start+" "+step_2_playlist+" "+step_3_plateform_end);
}


function launchTransfer(){
    if( step_1_plateform_start != null && step_2_playlist != null && step_3_plateform_end!= null ){
        $(".all-steps").html('');
        $(".loader").addClass("active");
        $.ajax({
            type:'GET',
            url: url_transfer,
            data: {
                plateform_start: encodeURI(step_1_plateform_start),
                playlist: encodeURI(step_2_playlist),
                plateform_end: encodeURI(step_3_plateform_end)
            },
            success: function(view) {
                $(".all-steps").html('Transfert OK');
            }
        });
    }
    else{
        alert("Une erreur est survenue, veuillez recharger la page");
    }
}

