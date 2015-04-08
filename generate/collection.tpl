<?php
namespace {{namespace}}Model;

class {{pluralClass}} extends \stORM\Collection
{
	public function __construct()
	{
		parent::__construct(new {{class}}());
	}

	public function getBy($properties = array())
	{
		$rows = $this->getData($properties);
		$elements = array();

		if ($rows)
		{
			foreach ($rows as $row)
			{
				$elements[] = new {{class}}($row);
			}
		}

		return $elements;
	}
}
?>