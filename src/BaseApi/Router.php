<?php
declare(strict_types=1);

namespace BaseApi;
use ShinePHP\Database\{Crud, CrudException};

final class Router {

	private $file_path;

	// This is accessed in any index.php by calling $this->Crud
	// Because even though this is a private class member, all top level required files from this class have access to all class members
	public $Crud;

	public function __construct($raw_path) {

		$this->file_path = self::get_required_php_file($raw_path);

	}

	public function set_db_connection(): void {

		try {
			$Crud = new Crud();
			$this->Crud = $Crud;
		} catch (PDOException|CrudException $pex) {
			throw new RouterException($pex->getMessage());
		}

	}

	public function route(): void {

		require_once $this->file_path;

	}

	public static function get_required_php_file(string $path): string {

		// just require the index file
		if ($path === '/') return 'index.php';

		// getting the true php path (no beginning or ending slashes and index.php at the end)
		$full_php_path = \ltrim(\rtrim($path, '/'), '/').'/index.php';

		// returning the path as long as it exists, if it doesn't, return 404.php
		// adding in the src is there so that the tests pass
		if (\file_exists($full_php_path) || \file_exists('api/'.$full_php_path)) {
			return $full_php_path;
		} else {
			throw new RouterException('This file doesn\'t exist');
		}

	}

}

final class RouterException extends \Exception {}
