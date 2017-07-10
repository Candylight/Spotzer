
$(document).ready(function(){
   $(document).on('click','.playTrack',function(){
       var trackId = $(this).data("songId");
       var platform = $(this).data("platform");
       $.ajax({
           type:'GET',
           url: urlGetSong,
           data: {
               trackId : trackId,
               platform: platform
           },
           success: function(view) {
               $('.popin-song').html(view);
           }
       })
   });
});
