$(document).ready(function() {
    $('#tournament_table').DataTable();

    $('.clickable_row').click(function(e){
        var data = {};
        data[$(e.target).attr("name")] = $(e.target).attr("value");
        $(e.target).siblings().each(function(n, e) {
            data[$(e).attr("name")] = $(e).attr("value");
        });

        $.ajax({
            url: "../public/tournament_bracket.php",
            data: {
                "bracket_data": JSON.stringify(data)
            },
            method: "POST",
            dataType: "html",
            success: function(response) {
                $("div.container").html(response); 
            },
            error: function(response) {
                $("div.container").html(response.responseText); 
            }
        });
    });
});
