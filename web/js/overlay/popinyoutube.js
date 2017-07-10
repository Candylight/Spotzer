
var url_embed_youtube = "https://www.youtube.com/watch?v=";


$(".video-link-popin").click( function(){
    var container_video = document.getElementById("overlay-video");

    container_video.innerHTML  = "";

    var overlay = $(".overlay-youtube");
    if( overlay.hasClass("active") ){
        overlay.removeClass("active");
    }
    else{
        $id_video = $(this).data("link");
        var iframe = document.createElement( "iframe" );

        iframe.setAttribute( "frameborder", "0" );
        iframe.setAttribute( "allowfullscreen", "" );
        iframe.setAttribute( "height", "315" );
        iframe.setAttribute( "width", "420" );
        iframe.setAttribute( "src", "https://www.youtube.com/embed/"+ $id_video +"?rel=0&showinfo=0&autoplay=1" );

        container_video.appendChild( iframe );

        overlay.addClass("active");
    }
});