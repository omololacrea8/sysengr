$(document).ready(function() {

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
	});

	// Select 2 Init
	// ==================================================================
	$(".special_select").select2({
		minimumResultsForSearch: 15
	});

	$(".ajax_submit").submit(function(event) {

		// Stop the browser from submitting the form.
		event.preventDefault();
		var formData = $(this).serialize() + "&ajax_processed=true";
		$.ajax({
			type: "POST",
			url: $(this).attr("action"),
			data: formData,
			beforeSend: function() {

				$(".form_loading_controller").prop("disabled", true);
				notify( "Processing..." , "warning" );
			}
		}).done(function(response) {

			$("body").append(response);
			$(".form_loading_controller").prop("disabled", false);
		}).fail(function(data) {

			notify( "Error while submiting. Please check your internet connection" , "danger" );
			$(".form_loading_controller").prop("disabled", false);
		});
	});
});

function notify( f_message, f_type ) {

	$(".bootstrap-growl").remove();

	$.bootstrapGrowl( f_message, {
		ele: "body",
		type: f_type,
		offset: {
			from: "top",
			amount: 20
		},
		// 'top', or 'bottom'
		align: "right",
		// ('left', 'right', or 'center')
		width: 275,
		delay: 4e3,
		allow_dismiss: true,
		stackup_spacing: 10
	});
}