
var url_embed_youtube = "https://www.youtube.com/watch?v=";


$(".video-link-popin").click( function(){
    var overlay = $(".overlay-youtube");
    if( overlay.hasClass("active") ){
        overlay.removeClass("active");
    }
    else{
        $id_video = $(this).data("link");
        $("#video-popin").attr("src",url_embed_youtube+$id_video);
        overlay.addClass("active");
    }
});