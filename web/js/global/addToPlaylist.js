
$(document).ready(function(){
    $(".add-to-playlist").click( function(){

        if( $(".overlay-add-playlist").hasClass("active")){
            $(".overlay-add-playlist").removeClass("active");
        }
        else{
            var type = $(this).data("type");
            var name = $(this).data("name");
            var idToAdd = $(this).data("id");


            $(".add-form").removeClass("active");
            $("."+type+"-add-form").addClass("active");

            $(".name-add-to").html(name);
            $(".id-track").val(idToAdd);

            $(".overlay-add-playlist").addClass("active");
        }
    });
});
