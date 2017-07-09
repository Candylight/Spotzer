$(document).ready(function () {
    $(".get-music-album").click( function(){
        var artist = $(this).data("artist");
        var album = $(this).data("album");
        $.ajax({
            type:'GET',
            url: url_get_song_from_album,
            data: {
                artist : artist,
                album: album
            },
            success: function(view) {
                $('.song-album-list').html(view);
            }
        })
    })
});