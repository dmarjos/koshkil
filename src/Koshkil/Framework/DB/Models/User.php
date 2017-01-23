<?php
namespace Koshkil\Framework\DB\Models;

use Koshkil\Framework\DB\Model;
use Koshkil\Framework\Support\PasswordUtils;
use Koshkil\Framework\Core\Application;

class User extends Model {

	protected $table="tbl_users";
	protected $structure=[];
	protected $dates=array('usr_created','usr_last_visit');
	protected $fillable=array(
		'usr_parent',
		'usr_status',
		'usr_username',
		'usr_password',
		'usr_first_name',
		'usr_last_name',
		'usr_email',
		'usr_hash',
		'usr_created',
		'usr_last_visit'
	);

	public $indexField="usr_id";

	protected function setupTableStructure() {
		$this->structure=[
			"fields"=>[
				"usr_id"=>array("type"=>"int","length"=>"11","extra"=>"unsigned NOT NULL AUTO_INCREMENT",),
				"usr_parent"=>array("type"=>"int","length"=>"11","extra"=>"DEFAULT NULL",),
				"usr_status"=>array("type"=>"int","length"=>"11","extra"=>"NOT NULL DEFAULT '0'",),
				"usr_username"=>array("type"=>"varchar","length"=>"15","extra"=>"NOT NULL DEFAULT ''",),
				"usr_password"=>array("type"=>"varchar","length"=>"40","extra"=>"NOT NULL DEFAULT ''",),
				"usr_first_name"=>array("type"=>"varchar","length"=>"255","extra"=>"NOT NULL DEFAULT ''",),
				"usr_last_name"=>array("type"=>"varchar","length"=>"255","extra"=>"NOT NULL DEFAULT ''",),
				"usr_email"=>array("type"=>"varchar","length"=>"255","extra"=>"NOT NULL DEFAULT ''",),
				"usr_created"=>array("type"=>"datetime","length"=>"","extra"=>"NOT NULL DEFAULT '0000-00-00 00:00:00'",),
				"usr_hash"=>array("type"=>"varchar","length"=>"255","extra"=>"NOT NULL",),
				"usr_last_visit"=>array("type"=>"datetime","length"=>"","extra"=>"NOT NULL DEFAULT '0000-00-00 00:00:00'",),
				"usr_reset_password"=>array("type"=>"datetime","length"=>"","extra"=>"NOT NULL DEFAULT '0000-00-00 00:00:00'",),
			],
			"keys"=>[
				["key_name"=>"","primary"=>true,"fields"=>"usr_id"],
			],
			"initial_records"=>[
				[
					"usr_id"=>1,
					"usr_parent"=>0,
					"usr_status"=>1,
					"usr_username"=>"root",
					"usr_password"=>PasswordUtils::createHash('admin'),
					"usr_first_name"=>"Super User",
					"usr_last_name"=>"",
					"usr_email"=>Application::Get('ADMIN_EMAIL'),
					"usr_created"=>date("Y-m-d H:i:s",time())
				]
			],
		];
	}
}