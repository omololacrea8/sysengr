$(document).ready(function(){

    "use strict";

    // Focus Effect
    // ==================================================================
    $(".input3").each(function() {
        $(this).on("blur", function() {
            if ($(this).val().trim() != "") {
                $(this).addClass("has-val");
            } else {
                $(this).removeClass("has-val");
            }
        });
    })

    // Select 2 Init
    // ==================================================================
    $(".special_select").select2({
        minimumResultsForSearch: 15
    });

});