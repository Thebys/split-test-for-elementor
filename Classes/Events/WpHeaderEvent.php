<?php

namespace SplitTestForElementor\Classes\Events;

class WpHeaderEvent {

	public function fire() {
		?>

		<script type="text/javascript">
				window.rocketSplitTest = { 'config': { 'page': { 'base': { 'protocol': 'http<?php echo(is_ssl() ? "s" : ""); ?>://', 'host': '<?php echo(parse_url(home_url('/'), PHP_URL_HOST)); ?>', 'path': '<?php echo(parse_url(home_url('/'), PHP_URL_PATH)); ?>' } } } };
				window.rocketSplitTest.cookie = { };
				window.rocketSplitTest.cookie.create = function (name, value, days) {
					var date = new Date();
					date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
					document.cookie = name + "=" + value + "; expires=" + date.toGMTString() + "; path=" + window.rocketSplitTest.config.page.base.path;
				};
				window.rocketSplitTest.cookie.read = function (name) {
					var parts = ("; " + document.cookie).split("; " + name + "=");
					return (parts.length === 2) ? parts.pop().split(";").shift() : null;
				};
		</script>

		<?php
	}

}
