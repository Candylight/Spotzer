
$(".add-to-playlist").click( function(){
    var type = $(this).data("type");
    var idObject = $(this).data("idsong");


    $(".add-form").addClass("active");
    $("."+type+"-add-form").addClass("active");


    $(".overlay-add-playlist").addClass("active");
});