<?php
ini_set('display_startup_errors', 1);

require(__DIR__.'/../config.php');
$config = include(__DIR__.'/config.php');

$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';', DB_USER, DB_PASSWORD);
$result = $pdo->query('SHOW TABLES');
$tables = $result->fetchAll(PDO::FETCH_NUM);
$namespace = (isset($config['namespace']) ? $config['namespace']."\\" : '');

$classTemplate = file_get_contents(__DIR__.'/class.tpl');
$collectionTemplate = file_get_contents(__DIR__.'/collection.tpl');

foreach ($tables as $table) {
	$tableName = $table[0];

	if (isset($config['map'][$tableName])) {
		$className = $config['map'][$table[0]];
	} else {
		$tableParts = explode('_', $tableName);
		$classParts = array();
		foreach ($tableParts as $part) {
			$classParts[] = ucfirst($part);
		}
		$className = rtrim(implode('', $classParts), 's');
	}

	$pluralClass = $className.'s';

	$result = $pdo->query('DESCRIBE '.$tableName);
	$columns = $result->fetchAll(PDO::FETCH_ASSOC);

	$columnParts = array();

	foreach ($columns as $column) {
		$columnString = "'".$column['Field']."' => ";
		$type = preg_replace('/\(\d+\)$/', '', $column['Type']);

		switch ($type) {
			case 'varchar':
				$columnString .= "'string'";
				break;
			case 'datetime':
			case 'date':
			case 'timestamp':
				$columnString .= "'date'";
				break;
			default:
				$columnString .= "'".$type."'";
		}

		if ($column['Key'] === 'PRI') {
			$IDColumn = $column['Field'];
		}

		$columnParts[] = $columnString;
	}


	$currentTemplate = str_replace('{{namespace}}', $namespace, $classTemplate);
	$currentTemplate = str_replace('{{columns}}', implode(",\n\t\t", $columnParts), $currentTemplate);
	$currentTemplate = str_replace('{{IDColumn}}', $IDColumn, $currentTemplate);
	$currentTemplate = str_replace('{{table}}', $tableName, $currentTemplate);
	$currentTemplate = str_replace('{{class}}', $className, $currentTemplate);

	file_put_contents($config['model_dir'].'/'.$className.'.php', $currentTemplate);

	$currentTemplate = str_replace('{{namespace}}', $namespace, $collectionTemplate);
	$currentTemplate = str_replace('{{pluralClass}}', $pluralClass, $currentTemplate);
	$currentTemplate = str_replace('{{class}}', $className, $currentTemplate);

	file_put_contents($config['model_dir'].'/'.$pluralClass.'.php', $currentTemplate);
}
?>