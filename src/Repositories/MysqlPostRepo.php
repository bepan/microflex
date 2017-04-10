<?php
namespace Betopan\Repositories;

use Betopan\Repositories\DB;

class MysqlPostRepo
{
	private $db;

	public function __construct(DB $db)
	{
        $this->db = $db;
	}

    public function getAll()
    {
        return $this->db->message();
    }
}