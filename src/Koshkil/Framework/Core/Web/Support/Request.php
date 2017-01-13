<?php
namespace Koshkil\Framework\Core\Web\Support;

use Koshkil\Framework\Support\Collection;

class Request extends Collection {

	private $variables=[];

	public function __construct($src=[]) {
		if ($src) {
			foreach($src as $key=>$value)
				$this->addItem($value,$key);
		}
	}


}