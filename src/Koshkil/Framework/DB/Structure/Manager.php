<?php
namespace Koshkil\Framework\DB\Structure;

use Koshkil\Framework\Core\Application;
use Koshkil\Framework\Support\StringUtils;
class Manager {

	public static function checkTable($model) {
		$structure=$model->getStructure();
		$tbl_name=$model->getTableName();
		foreach(["fields","keys"] as $section) {
			if (!isset($structure[$section])) return false;
		}
		$columns=array();
		foreach($structure["fields"] as $fieldName=>$column) {
			$columnDef="`{$fieldName}` {$column["type"]}";
			if ($column["length"]) $columnDef.="({$column["length"]})";
			if ($column["extra"]) $columnDef.=" ".$column["extra"];
			$columns[]=$columnDef;
		}
		$keys=array();
		$indexField=null;
		foreach($structure["keys"] as $keyDef) {
			if ($keyDef["primary"]) {
				$keys[]="PRIMARY KEY ({$keyDef["fields"]})";
				$indexField=$keyDef["fields"];
			} else if (strtolower($keyDef["key_type"])!="fulltext")
				$keys[]="KEY `{$keyDef["key_name"]}` ({$keyDef["fields"]})";
		}
		$createTable="CREATE TABLE {$tbl_name} (".implode(", ",$columns).($keys?", ".implode(",",$keys):'').") ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE latin1_spanish_ci;";
		$db=Application::get("DB");
		$tableExists=$db->getRow("show tables like '{$tbl_name}'");
		if (!$tableExists) {
			$db->execute($createTable);
			if ($structure["initial_records"]) {
				foreach($structure["initial_records"] as $data)
					$model->create($data);
			}
		} else {
			$rec=$db->getRow("show create table {$tbl_name}");
			$sql=$rec["Create Table"];
			preg_match_all("/\n[\s]*([^\s]+)\s([\w]+)(\([^\)]+\))?(\n\))?/s", $sql, $matches);

			if (!preg_match("/DEFAULT CHARSET=latin1 COLLATE=latin1_spanish_ci/s",$sql))
				self::$db->execute("ALTER TABLE {$tbl_name} CONVERT TO CHARACTER SET latin1 COLLATE latin1_spanish_ci");

			$fields=$matches[1];
			$types=$matches[2];
			$sizes=$matches[3];
			$fieldsInTable=array();
			foreach($fields as $idx=>$field){
				if (substr($field,0,1)=="`" && substr($field,-1)=="`") {
					$fieldName=substr($field,1,-1);
					$size=$sizes[$idx];
					$size=str_replace("(","",$size);
					$size=str_replace(")","",$size);
					$fieldsInTable[$fieldName]=array("type"=>$types[$idx],"length"=>$size);
				}
			}
			foreach($structure["fields"] as $fieldName=>$field) {
				if (!$fieldsInTable[$fieldName]) {
					$columnDef="`{$fieldName}` {$field["type"]}";
					if ($field["length"]) $columnDef.="({$field["length"]})";
					if ($field["extra"]) $columnDef.=" ".$field["extra"];
					$db->execute("ALTER TABLE {$tbl_name} ADD {$columnDef}");
				} else {
					if ($field["type"]!=$fieldsInTable[$fieldName]["type"] || $field["length"]!=$fieldsInTable[$fieldName]["length"]) {
						$columnDef="`{$fieldName}` {$field["type"]}";
						if ($field["length"]) $columnDef.="({$field["length"]})";
						if ($field["extra"]) $columnDef.=" {$field["extra"]}";
						try {
							$db->execute("ALTER TABLE {$tbl_name} CHANGE `{$fieldName}` {$columnDef}");
						} catch (Exception $e) {
							echo "ALTER TABLE {$tbl_name} CHANGE `{$fieldName}` {$columnDef}<hr/>";
							dump_var($e);
						}
					}
				}
			}
			foreach($fieldsInTable as $fieldName=>$fieldDef) {
				if (!isset($structure["fields"][$fieldName]))
					$db->execute("ALTER TABLE {$tbl_name} DROP `{$fieldName}`");
			}
			if (!isset($structure["keys"]))
				$structure["keys"]=array();
			foreach($structure["keys"] as $key) {
				if ($key["primary"]) continue;
				$fields=explode(",",$key["fields"]);
				foreach($fields as &$field) {
					$field=trim($field);
					$field=StringUtils::replace_all("`","",$field);
					$field="`{$field}`";
				}
				$key["fields"]=implode(",",$fields);
				$regExp="/KEY `{$key["key_name"]}` \({$key["fields"]}\)/sim";
				if (!@preg_match_all($regExp,$rec["Create Table"])) {
					if (preg_match("/KEY `{$key["key_name"]}`/s",$rec["Create Table"])) {
						$db->execute("DROP INDEX `{$key["key_name"]}` on `{$tbl_name}`");
						$db->execute("CREATE ".(strtoupper($key["key_type"])=="FULLTEXT"?"FULLTEXT ":"")."INDEX `{$key["key_name"]}` on `{$tbl_name}` ({$key["fields"]})");
					} else {
						$db->execute("CREATE ".(strtoupper($key["key_type"])=="FULLTEXT"?"FULLTEXT ":"")."INDEX `{$key["key_name"]}` on `{$tbl_name}` ({$key["fields"]})");
					}
				}
			}
			return true;
		}
	}

}