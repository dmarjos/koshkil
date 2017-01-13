<?php
namespace Koshkil\Framework\Core\Web\Widgets;

use Koshkil\Framework\Core\Web\Widget;
use Koshkil\Framework\Core\Web\Support\Request;

class FaceloginWidget extends Widget {

	public function run(Request $request) {
		$this->name="facelogin";
		$this->init($request);
		return $this->output($request);
	}

}