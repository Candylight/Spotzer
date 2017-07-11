$(document).ready(function(){
    $(document).on('click','.list-playlist',function(){
        var playlist_id = $(this).val();
        $.ajax({
            type:'GET',
            url: urlSongsFromPlaylist,
            data: {
                playlistid : playlist_id
            },
            success: function(view) {
                $('.tracks-from-playlist').html(view);
            }
        })
    });
});
