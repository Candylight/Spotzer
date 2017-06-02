
$(".header-icon").click( function(){
    var id = $(this).attr('id');
    var elm = $(".popin-"+id);

    if( elm.hasClass("active")){
        $(".popin-"+id).removeClass("active");
    }
    else{
        $(".popin-"+id).addClass("active");
    }
});