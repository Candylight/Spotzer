$(document).ready(function () {
    $("#plateform-choice").on('change', function(){
        var playlist = $(this).val();
        url = url.replace('placeholder-placeholder', playlist);
        $.ajax({
            type:'GET',
            url: url_playlist,
            data: {playlist: $(this).val()},
            success: function(view) {
                $('.playlists-choices').html(view);
            }
        })
    })
});