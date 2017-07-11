
$(document).ready(function(){
    $(document).on('click','.add-to-playlist',function(){
        if( $(".overlay-add-playlist").hasClass("active")){
            $(".overlay-add-playlist").removeClass("active");
        }
        else{
            var type = $(this).data("type");
            var name = $(this).data("name");
            var idToAdd = $(this).data("id");

            $.ajax({
                type:'GET',
                url: url_get_playlists,
                data: {
                    platform : type,
                    name: name,
                    trackId: idToAdd
                },
                success: function(view) {
                    $('#overlay-add-playlist').html(view);
                    $("#overlay-add-playlist").addClass("active");
                }
            });
        }
    });

    $(document).on('click', '#addToPlaylistSubmit', function (e) {
        e.preventDefault();
        $.ajax({
            type:'POST',
            url: $(this).parent().attr('action'),
            data: {
                playlistId: $(this).parent().find("#idPlaylist").val(),
                trackId: $(this).parent().find("#idTrack").val()
            },
            success: function() {
                alert("L'item a bien été ajouté à la playlist");
                $("#overlay-add-playlist").removeClass("active");
            }
        });
    });
});
