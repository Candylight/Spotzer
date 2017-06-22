var critere_1 = false;
var critere_2 = false;


$(".type-list-start").change( function(){
    $(".type").removeClass("display");
    $(".type").removeClass("active");
    critere_2 = false;

    var val = $(this).val();
    $(".type-"+val).addClass("display");

    critere_1 = true;

    launchButton();
});



$(".type").click(function(){
    $(".type").removeClass("active");

    $(this).addClass("active");

    critere_2 = true;

    launchButton();
});




function launchButton(){
    var button = $(".launch-transfer");
    if( critere_1 && critere_2 ){
        button.addClass("active");
    }
    else{
        button.removeClass("active");
    }
}