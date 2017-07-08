$(document).ready(function () {
    $("#plateform-start-choice").on('change', function(){
        var choice = $(this).val();
        url_playlist = url_playlist.replace('placeholder-placeholder', choice);
        $.ajax({
            type:'GET',
            url: url_playlist,
            data: {playlist: $(this).val()},
            success: function(view) {
                $('.playlists-choices').html(view);
                step_2_data = choice;
                $('.content-2').addClass("active");
            }
        })
    })
});