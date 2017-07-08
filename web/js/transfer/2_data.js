$(document).ready(function () {
    $("#type-choice").on('change', function(){
        var choice = $(this).val();
        url_playlist = url.replace('placeholder-placeholder', choice);
        $.ajax({
            type:'GET',
            url: url_playlist,
            data: {playlist: $(this).val()},
            success: function(view) {
                $('.playlists-choices').html(view);
                step_2_data = choice;
                $('.content-3').addClass("active");
            }
        })
    })
});