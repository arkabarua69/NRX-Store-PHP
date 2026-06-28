<script type="text/javascript">
	function payNow(pay_now_id) {
		$.ajax({
			url: '../inc/logged/pay_now.php',
			type: 'post',
			data: {
				pay_now_id: pay_now_id
			},
			beforeSend: function() {
				$("#pay-now #pay-now-" + pay_now_id).prop("disabled", true);
				$("#pay-now #pay-now-" + pay_now_id).html(
					"Pay  <span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span>"
				);
			},
			success: function(response) {
				$("#checkout_response").html(response);
				var spanText = $("#checkout_response span").text();
				if (spanText !== "") {
					alert(spanText);
				}

				$("#pay-now #pay-now-" + pay_now_id).prop("disabled", false);
				$("#pay-now #pay-now-" + pay_now_id).html("Pay");
			}
		});
	}
</script>
