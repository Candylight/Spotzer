
$(".header-icon").click( function(){
    var elm = $(".popin");
    if( elm.hasClass("active")){
        elm.removeClass("active");
    }
    else{
        elm.addClass("active");
    }

});