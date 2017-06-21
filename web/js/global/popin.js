
$(".header-icon").click( function(){
    var id = $(this).attr('id');
    var elm = $(".popin-"+id);


    $(".inpopin").removeClass("active");

    if( elm.hasClass("active")){
        $(".popin").removeClass("active");
    }
    else{
        $(".popin").addClass("active");
    }

    $(".popin-"+elm).addClass("active");

});