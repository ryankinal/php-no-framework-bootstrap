<?php
namespace Example\Model;

class Test extends \stORM\DBRecord
{
	protected $columns = array(
		'test_id' => 'int',
		'test_name' => 'string'
	);
	protected $table = 'table';
	protected $IDColumn = 'test_id';
}
?>