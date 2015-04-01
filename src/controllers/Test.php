<?php
namespace Example\Controllers;

class Test extends Controller
{
	public function get()
	{
		$this->response->setContent(json_encode(array(
			'test' => 'yes'
		)));
	}
}
?>