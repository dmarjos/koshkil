<?php
namespace Koshkil\Framework\DB\Models;

use Koshkil\Framework\DB\Model;
use Koshkil\Framework\Support\PasswordUtils;
use Koshkil\Framework\Core\Application;

class UserRole extends Model {

	protected $table="tbl_user_role";
	protected $structure=[];
	protected $fillable=array(
		'usr_id',
		'rol_id',
	);

	public $indexField="uxr_id";

	//public function
	protected function setupTableStructure() {
		$this->structure=[
			"fields"=>[
				"uxr_id"=>array("type"=>"int","length"=>"11","extra"=>"unsigned NOT NULL AUTO_INCREMENT",),
				"usr_id"=>array("type"=>"int","length"=>"11","extra"=>"not null DEFAULT 0",),
				"rol_id"=>array("type"=>"int","length"=>"11","extra"=>"not null DEFAULT 0",),
			],
			"keys"=>[
				["key_name"=>"","primary"=>true,"fields"=>"uxr_id"],
			],
			"initial_records"=>[
				[
					"uxr_id"=>1,
					"usr_id"=>1,
					"rol_id"=>1,
				]
			],
		];
	}

	public function role() {
		return $this->belongsTo('Roles','rol_id','rol_id');
	}
}