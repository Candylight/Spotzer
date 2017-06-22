/**
 * Created by sylva on 30/05/2017.
 */

$(document).ready(function () {
    $('#lastFMSearch').autocomplete({
        source: function( request, response ) {
            $.ajax({
                url: "https://ws.audioscrobbler.com/2.0/?method=artist.search&artist="+$('#lastFMSearch').val()+"&api_key=f6734ae2b9887488059fc9507f2c6c60&format=json&limit=10",
                dataType: "json",
                success: function(data) {
                    var results = [];
                    for(var cptArtist = 0; cptArtist < data.results.artistmatches.artist.length; cptArtist++)
                    {
                        results.push({
                            label: data.results.artistmatches.artist[cptArtist].name,
                            icon: data.results.artistmatches.artist[cptArtist].image[1]["#text"]
                        })
                    }
                    response(results);
                }
            });
        },
        select: function( event, ui ) {
            $("#lastFMSearch").val(ui.item.value);
            $("#lastFMSearch").parent().submit();
        },
        minLength: 2
    } )
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
        var img = item.icon;
        if( img == ""){
            img = "http://localhost/Spotzer/web/img/global/placeholder-search.png";
        }
        return $( "<li>" )
            .append( '<div class="search-result"><img src="'+img+'">' + item.label + '</div>' )
            .appendTo( ul );
    };
});
