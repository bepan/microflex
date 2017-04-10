<?php
namespace Betopan\Controllers;

use Betopan\Repositories\MysqlPostRepo;

class PostController
{
	protected $postRepo;

	public function __construct(MysqlPostRepo $postRepo)
	{
       $this->postRepo = $postRepo;
	}

	public function index(\Betopan\Http\Response $res, $id)
	{
	    $res->send("Hi: {$this->postRepo->getAll()} {$id}");
	}
}