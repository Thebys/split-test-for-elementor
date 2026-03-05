<?php

namespace SplitTestForElementor\Classes\Events;

class SectionShouldRenderEvent
{

	public function fire($shouldRender, $element) {
		return true;
	}

}
