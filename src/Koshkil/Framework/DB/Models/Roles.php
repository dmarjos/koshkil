<?php
namespace Koshkil\Framework\DB\Models;

use Koshkil\Framework\DB\Model;
use Koshkil\Framework\Support\PasswordUtils;
use Koshkil\Framework\Core\Application;

class Roles extends Model {

	protected $table="tbl_roles";
	protected $structure=[];
	protected $fillable=array(
		'rol_name',
	);

	public $indexField="usr_id";

	//public function
	protected function setupTableStructure() {
		$this->structure=[
			"fields"=>[
				"rol_id"=>array("type"=>"int","length"=>"11","extra"=>"unsigned NOT NULL AUTO_INCREMENT",),
				"rol_name"=>array("type"=>"varchar","length"=>"64","extra"=>"NOT NULL DEFAULT ''",),
			],
			"keys"=>[
				["key_name"=>"","primary"=>true,"fields"=>"rol_id"],
			],
			"initial_records"=>[
				[
					"rol_id"=>1,
					"rol_name"=>'Superuser',
				]
			],
		];
	}
}