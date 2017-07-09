$(document).ready(function () {
    $(".get-music-album").click( function(){

        if( prefered_plateform == "spotify" || prefered_plateform == "deezer" ){
            var artist = $(this).data("artist");
            var album = $(this).data("album");
            $.ajax({
                type:'GET',
                url: url_get_song_from_album,
                data: {
                    artist : artist,
                    album: album,
                    plateform: prefered_plateform
                },
                success: function(view) {
                    $('.song-album-list').html(view);
                }
            })
        }
        else{
            $('.song-album-list').html("Recherche impossible. <br/> Vous devez être connecté à une plateforme musicale pour affocher les sont d'un album");
        }
    })
});