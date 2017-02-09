$("#main-container > .right > .container-tab > .thumbnails > .item").click( function(){
    var nb = $(this).data("thumb");

    // Desactivation des onglets et activation de l'onglet cliqué
    $("#main-container > .right > .container-tab > .thumbnails > .item").removeClass("active");
    $(this).addClass("active");

    // Désactivation des contenu et activation du contenu de l'onglet cliqué
    $(".content-thumb").removeClass("active");
    $(".thumb-"+nb).addClass("active");
});