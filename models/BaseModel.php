<?php

use Phalcon\Db\Enum;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\InclusionIn;

class BaseModel extends Phalcon\Mvc\Model
{
	public $id;
	public $state;
	public $name;
	public $created_time;
	public $updated_time;
	public $account_id;

	public function rawSql($sql)
    {
        $di             = \Phalcon\DI::getDefault();
        $db             = $di['db'];
        $data           = $db->query( $sql );
        $data->setFetchMode(Enum::FETCH_OBJ);
        $results        = $data->fetchAll();
        return $results;
    }

	public function getSchema()
    {
		$schema = "public";
		$config = $this->getDI()->getConfig();

		if (!array_key_exists("database", $config))
			return $schema;

		$db = $config["database"];

		if (!array_key_exists("schema", $db))
			return $schema;

		$schema = $db["schema"];

		return $schema;
    }
	
	public function updateFile($file)
	{
		$dir = Utils::UPLOAD_DIR.'/'.get_class($this).'/'.$this->id.'/'.$file->getKey().'/';
		Utils::CreateDir($dir);
		
		//remove all files there
		$olds = glob($dir.'*');
		foreach($olds as $old)
		{
			if(is_file($old))
				unlink($old);
		}
		
		$file->moveTo($dir.$file->getName());
	}
	
	public function updateFiles($files)
	{
		foreach ($files as $file)
		{
			$this->updateFile($file);
		}
	}
}