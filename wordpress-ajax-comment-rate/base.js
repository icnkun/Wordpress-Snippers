jQuery(document).on("click", ".comment--like a",
function() {
	var $this = jQuery(this);
	var comment_id = $this.parent().data("commentid");
	var event = $this.data("event");
	var count = $this.children(".count");
	if ($this.parent().hasClass("rated")) {
		alert("you've rated");
		return false;
	} else {
		var ajax_data = {
			action: "do_comment_rate",
			comment_id: comment_id,
			event: event
		};
		jQuery.ajax({
			url: '/wp-admin/admin-ajax.php',
			type: "POST",
			data: ajax_data,
			dataType: "json",
			success: function(data) {
				if (data.status == 200) {
					if (event == "up") {
						count.html(data.data._comment_up);
					} else {
						count.html(data.data._comment_down);
					}
					$this.parent().addClass("rated");
				} else {
					console.log(data.data)
				}
			}
		});
	}
	return false;
});