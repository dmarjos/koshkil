<?php
namespace Koshkil\Framework\Core\Web\Widgets;

use Koshkil\Framework\Core\Web\Widget;

class RecaptchaWidget extends Widget {

	protected function init(Request $request) {
		return true;
	}

}