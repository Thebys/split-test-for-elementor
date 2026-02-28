<?php

namespace SplitTestForElementor\Classes\Events;

use SplitTestForElementor\Classes\Misc\SettingsManager;
use SplitTestForElementor\Classes\Repo\TestRepo;

class WpHeaderEvent {

	private static $testRepo;
	private static $settingsManager;

	public function __construct() {
		if (self::$testRepo == null) {
			self::$testRepo = new TestRepo();
			self::$settingsManager = new SettingsManager();
		}
	}

	public function fire() {
		// TODO@kberlau: May not use global
		global $rocketSplitTestRunningTests;

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


		if (!self::$settingsManager->getRawValue(SettingsManager::CACHE_BUSTER_ACTIVE)) {
			return;
		}

		$postId = url_to_postid($_SERVER['REQUEST_URI']);
		if ($postId == 0) {
			$postId = (int) get_option('page_on_front');
		}
		$conversionPageTests = self::$testRepo->getTestsByConversionPagePostId($postId);
		$conversionTestIds = [];
		foreach ($conversionPageTests as $test) {
			$conversionTestIds[] = $test->id;
		}

		$currentLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$currentLink = explode("?", $currentLink)[0];
		$currentLink = trim($currentLink, "/");
		$conversionPageTests = self::$testRepo->getTestsByConversionUrl($currentLink);
		foreach ($conversionPageTests as $test) {
			$conversionTestIds[] = $test->id;
		}

		?>
			<script type="text/javascript">
				window.rocketSplitTest.conversion = { };
				window.rocketSplitTest.helpers = { };
				window.rocketSplitTest.tests = <?php echo(json_encode($rocketSplitTestRunningTests)); ?>;
				window.rocketSplitTest.conversion.testIds = <?php echo(json_encode($conversionTestIds)); ?>;
                window.rocketSplitTest.distributeType = "<?php echo(self::$settingsManager->getRawValue(SettingsManager::VARIANT_DISTRIBUTION_TYPE)); ?>";

				window.rocketSplitTest.helpers.getRequest = function (path, callback) {
					var urlBase = window.rocketSplitTest.config.page.base;
					path = path.charAt(0) === "/" ? path.substr(1) : path;
					var xhr = new XMLHttpRequest();
					xhr.open('GET', urlBase.protocol + urlBase.host + urlBase.path + path + "&rnd=" + Math.floor(Math.random() * 1000000000000));
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
					xhr.onreadystatechange = function () {
						if (xhr.readyState !== 4) {
							return;
						}
						if (xhr.status >= 200 && xhr.status < 300) {
							if (typeof callback !== 'undefined' && callback != null) {
								callback(JSON.parse(xhr.responseText));
							}
						}
					};
					xhr.send();
				}

				window.rocketSplitTest.helpers.postRequest = function (path, data, callback) {
					var urlBase = window.rocketSplitTest.config.page.base;
					path = path.charAt(0) === "/" ? path.substr(1) : path;
					var xhr = new XMLHttpRequest();
					xhr.open('POST', urlBase.protocol + urlBase.host + urlBase.path + path + "?rnd=" + Math.floor(Math.random() * 1000000000000));
					xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
					xhr.onreadystatechange = function () {
						if (xhr.readyState !== 4) {
							return;
						}
						if (xhr.status >= 200 && xhr.status < 300) {
							if (typeof callback !== 'undefined' && callback != null) {
								callback(JSON.parse(xhr.responseText));
							}
						}
					};
					xhr.send(encodeURI(data));
				};

				window.rocketSplitTest.setInnerHtml = function(elm, html) {
					elm.innerHTML = html;
					var scripts = elm.getElementsByTagName("script");
					// If we don't clone the results then "scripts"
					// will actually update live as we insert the new
					// tags, and we'll get caught in an endless loop
					var scriptsClone = [];
					for (var i = 0; i < scripts.length; i++) {
						scriptsClone.push(scripts[i]);
					}
					for (var i = 0; i < scriptsClone.length; i++) {
						var currentScript = scriptsClone[i];
						var s = document.createElement("script");
						// Copy all the attributes from the original script
						for (var j = 0; j < currentScript.attributes.length; j++) {
							var a = currentScript.attributes[j];
							s.setAttribute(a.name, a.value);
						}
						s.appendChild(document.createTextNode(currentScript.innerHTML));
						currentScript.parentNode.replaceChild(s, currentScript);
					}
				}

				window.rocketSplitTest.addActiveTestData = function (test) {
					var cookieName = "elementor_split_test_" + test.id + "_variation";
					var cookieValue = window.rocketSplitTest.cookie.read(cookieName);
					if (cookieValue != null) {
						for (var j = 0; j < test.variations.length; j++) {
							if (test.variations[j].id === parseInt(cookieValue)) {
								test.variations[j].active = true;
								break;
							}
						}
						return test;
					}

					if (window.rocketSplitTest.distributeType === "database") {
						test.variantExamination = true;

						var url = "/wp-json/splitTestForElementor/v1/tests/getVariationToDisplay/?testId=" + test.id;
						window.rocketSplitTest.helpers.getRequest(url, function (response) {
							for (var k = 0; k < test.variations.length; k++) {
								console.log("process", test.variations[k], response);
								if (test.variations[k].id === response.variant.id) {
									test.variations[k].active = true;
									window.rocketSplitTest.cookie.create(cookieName, test.variations[k].id, 365);
									var views = 'views=' + JSON.stringify([{testId: test.id, variationId: test.variations[k].id}]);
									window.rocketSplitTest.ajax.post('/wp-json/splitTestForElementor/v1/tracking/view/store-multi/', views, function (data) {
										window.rocketSplitTest.cookie.create('elementor_split_test_client_id', data.clientId, 365);
									});
									test.variantExamination = false;
								} else {
									test.variations[k].active = false;
								}
							}
						});

					} else {
						var random = Math.floor(Math.random() * 100) + 1;
						var counter = 0;
						for (var k = 0; k < test.variations.length; k++) {
							var variation = test.variations[k];
							if (random > counter && random <= counter + variation.percentage) {
								<?php // TODO@kberlau: Test changing options ?>
								test.variations[k].active = true;
								window.rocketSplitTest.cookie.create(cookieName, variation.id, 365);
							} else {
								test.variations[k].active = false;
							}
							counter += variation.percentage;
						}
						test.variantExamination = false;
					}

					return test;
				};

				window.rocketSplitTest.isActiveVariation = function(testId, variationId) {
					for (var i = 0; i < window.rocketSplitTest.tests.length; i++) {
						var test = window.rocketSplitTest.tests[i];
						if (test.id === testId) {
							for (var k = 0; k < test.variations.length; k++) {
								var variation = test.variations[k];
								if (variation.id === variationId){
									return variation.active;
								}
							}
						}
					}
					return false;
				};

				window.rocketSplitTest.addTest = function (test) {
					<?php // TODO@kberlau: Check if test already there ?>
					for (var i = 0; i < window.rocketSplitTest.tests.length; i++) {
						if (window.rocketSplitTest.tests[i].id === test.id) {
							return;
						}
					}
					window.rocketSplitTest.tests.push(window.rocketSplitTest.addActiveTestData(test));
				};

				window.rocketSplitTest.getTestById = function (testId) {
					for (var i = 0; i < window.rocketSplitTest.tests.length; i++) {
						var test = window.rocketSplitTest.tests[i];
						if (test.id === testId) {
							return test;
						}
					}
					return null;
				};

				for (var i = 0; i < window.rocketSplitTest.tests.length; i++) {
					window.rocketSplitTest.tests[i] = window.rocketSplitTest.addActiveTestData(window.rocketSplitTest.tests[i]);
				}

				window.rocketSplitTest.maybeRenderTestVariant = function (testId, variantId, content, containerId) {

					if (window.rocketSplitTest.distributeType === "database") {
						var test = window.rocketSplitTest.getTestById(testId);
						if (test == null) {
							setTimeout(function () { window.rocketSplitTest.maybeRenderTestVariant(testId, variantId, content, containerId); }, 500);
							return;
						}

						if (test.variantExamination) {
							setTimeout(function () { window.rocketSplitTest.maybeRenderTestVariant(testId, variantId, content, containerId); }, 500);
							return;
						}

						if (!window.window.rocketSplitTest.isActiveVariation(testId, variantId)) {
							document.getElementById(containerId).parentElement.innerHTML = "";
						}
						window.rocketSplitTest.setInnerHtml(document.getElementById(containerId).parentElement, content);
					} else {
						if (!window.window.rocketSplitTest.isActiveVariation(testId, variantId)) {
							document.getElementById(containerId).parentElement.innerHTML = "";
						}
						window.rocketSplitTest.setInnerHtml(document.getElementById(containerId).parentElement, content);
					}
				}

			</script>
		<?php

	}

}