<?php
	$home = `printf ~`;
	putenv("HOME=$home");
	putenv('PATH=$PATH:/bin:/usr/bin:/usr/local/bin:$HOME/bin');
	
	// =========================================================================

	// https://stackoverflow.com/a/34499778
	function ordinal($number) {
		return date("S", mktime(0, 0, 0, 0, $number, 0));
	}

	// Returns a random array item
	function random($array) {
		return $array[rand(0, count($array) - 1)];
	}

	function slugify($string) {
		return strtolower(trim(preg_replace('/[^A-z0-9-]+/', '-', $string), '-'));
	}
	
	
	// == SEARCH ===============================================================
	
	function contains($query, $string) {
		return strpos($string, $query) !== false;
	}

	// https://stackoverflow.com/a/55229702
	class Haystack {
		public $value;

		public function __construct($value) {
			$this->value = $value;
		}

		public function contains($needle) {
			if (strpos($this->value, $needle) !== false) return $this;
		}
	}

	function findItem($obj, $query) {
		return $obj[array_search($query, array_column($obj, 'id'))];
	}
	
	
	// == FILE RELATED =========================================================

	function removeDir($target) {
		$directory = new RecursiveDirectoryIterator($target,  FilesystemIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($files as $file) {
			if (is_dir($file)) {
				rmdir($file);
			} else {
				unlink($file);
			}
		}
		rmdir($target);
	}

	function removeDirs($targets) {
		foreach ($targets as $dir) {
			if (!is_dir($dir)) {
				mkdir($dir, 0777, true);
			}

			removeDir($dir);
		}
	}
	
	
	// == DEBUGGING & LOGGING ==================================================
	
	/*/
		tail -f ~/desktop/logs.log & tail -f ~/desktop/logs.json | pygmentize -s -l json -O style=one-dark
		--OR--
		yarn logger (might be more reliable)
	/*/
	
	function logger($message) {
		if (contains('.test', $_SERVER["HTTP_HOST"])) {
			$project_root = dirname(dirname($_SERVER["SCRIPT_FILENAME"]));
			$logfile = "logs.log";
	
			if (is_array($message)) {
				$message = json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
				$logfile = "logs.json";
			}
	
			file_put_contents("$project_root/$logfile", $message . "\n");
		}
	}
	
	function console($message) {
		echo "<script>console.log(" . json_encode($message) . ")</script>";
	}
	
	function loadData($filename) {
		return json_decode(file_get_contents("data/$filename"), true);
	}
	
	function printJSON($data) {
		header('Content-Type: application/json');
		echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}
	
	function printData($filename) {
		printJSON(json_decode(file_get_contents("$filename"), true));
	}

?>
