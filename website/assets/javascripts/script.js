$(function() {
	// save wifi settings when the user clicks the "Update Wifi Settings" button
 	$("#updatewpa").click(function(event) {
 		event.preventDefault();
 		var $this = $(this);
 		var $form = $(this).closest("form");

 		// disableb utton
 		$this.prop("disabled", true);

 		// grab ajax parameters
 		var url = $form.action;
 		var method = $form.method;
 		var accesspoint = $form.find("#accesspoint").val();
 		var password = $form.find("#accesspoint_password").val();

 		// create json data string
 		var jsonData = { "accesspoint": accesspoint, "password": password };
 		var stringData = JSON.stringify(jsonData);


 		$.ajax({
			method: method,
			url: url,
			data: stringData,
			dataType: "json",
			success: function(response, textStatus, jqXHR) {
 				$this.prop("disabled", false);
 				console.log("Settings were changed.");
 				console.log("Console will now restart in Client mode.");
 				console.log("This website will no longer be available.");
			},
			error: function(jqXHR, textStatus, errorThrown) {
 				$this.prop("disabled", false);
				console.log("ajax error");
				console.log(textStatus);
				console.log(errorThrown);
			}
		});


 	});
});
