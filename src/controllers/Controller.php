<?php

namespace RTAPI\Controllers;

class Controller
{
	protected $request;
	protected $response;
	protected $fieldMap;

	public function __construct($request, $response)
	{
		$this->request = $request;
		$this->response = $response;
	}

	public function getAuthorizedUser()
	{
		$authHeader = $this->request->headers->get('Authorization');

		if ($authHeader)
		{
			$authHeader = trim($authHeader);
			$parts = preg_split('/\s+/', $authHeader);

			if (strtolower($parts[0]) === 'bearer' && isset($parts[1]))
			{
				$auth = new \RTAPI\Util\Auth();
				$user = $auth->verifyToken($parts[1]);

				if ($user)
				{
					return $user;
				}
			}
		}

		return null;
	}

	public function incoming($data)
	{
		return $this->filter(array_flip($this->fieldMap), $data);
	}

	public function outgoing($data)
	{
		return $this->filter($this->fieldMap, $data);
	}

	protected function filter($fieldMap, $data)
	{
		$result = array();

		foreach ($data as $key => $value)
		{
			if (isset($fieldMap[$key]))
			{
				$result[$fieldMap[$key]] = $value;
			}
		}

		return $result;
	}

	public function respond($data, $code = 200)
	{
		$this->response->setStatusCode($code);
		$this->response->setContent(json_encode($data));
	}

	protected function search($table)
	{
		$limit = intval($this->request->query->get('limit', RESPONSE_LIMIT));
		$offset = intval($this->request->query->get('offset', RESPONSE_OFFSET));
		$searchParameters = $this->incoming($this->request->query->all());

		$sql = 'SELECT * FROM '.$table;
		$limitSQL = 'LIMIT :limit OFFSET :offset';
		$wheres = array();

		foreach ($searchParameters as $key => $value)
		{
			$wheres[] = $key.' = :'.$key;
		}

		if (count($wheres) > 0)
		{
			$whereStatement = 'WHERE '.implode(' AND ', $wheres);
		}
		else
		{
			$whereStatement = '';
		}

		$database = new \stORM\DataBase();
		$limitedSelect = $database->getInterface()->prepare($sql.' '.$whereStatement.' '.$limitSQL);
		$limitedSelect->bindValue(':limit', $limit, \PDO::PARAM_INT);
		$limitedSelect->bindValue(':offset', $offset, \PDO::PARAM_INT);

		foreach ($searchParameters as $key => $value)
		{
			$limitedSelect->bindValue(':'.$key, $value);
		}

		if ($limitedSelect->execute())
		{
			$meta = array(
				'limit' => $limit,
				'offset' => $offset
			);
			$data = $limitedSelect->fetchAll(\PDO::FETCH_ASSOC);

			$countSQL = 'SELECT count(*) AS total FROM '.$table.' '.$whereStatement;
			$countSelect = $database->getInterface()->prepare($countSQL);

			foreach ($searchParameters as $key => $value)
			{
				$countSelect->bindValue(':'.$key, $value);
			}

			if ($countSelect->execute())
			{
				$rows = $countSelect->fetchAll(\PDO::FETCH_ASSOC);

				if (count($rows) > 0)
				{
					$meta['count'] = intval($rows[0]['total']);	
				}
			}

			return array(
				'meta' => $meta,
				'data' => $data	
			);
		}
		else
		{
			$responseData = new \RTAPI\Util\APIResponse();
			$this->respond($responseData->error(print_r($limitedSelect->errorInfo())), 500);
		}
	}
}

?>