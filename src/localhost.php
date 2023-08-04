<?php

	/*/
		LocalHost API Wrapper
		Stephen Ginn at Crema Design Studio
		Created on August 02, 2023
	/*/

	namespace Crema;
	Use stdClass;
	
	class LocalhostApi extends \stdClass {
		public function __construct() {
			$home = `printf ~`;
			putenv('PATH=$PATH:/usr/bin:/opt/homebrew/bin');
			
			$this->cfgfile = file("$home/bin/config/apache/sites.conf");
			$this->apachectl = shell_exec("apachectl -S");
			$this->parseVariables();
			
			$this->regex = (object) [
				'cfgfile' => (object) [
					'domain' => '/[^ ]+.test/',
					'macro' => '/(?<=Use )[A-z]+/',
					'path' => '/(?<=")[^\\"]+/'
				],
				'apachectl' => (object) [
					'domain' => '/(?<=namevhost )\S+/',
					'macro' => '/(?<=macro \')[^\']+/',
					'conf' => '/[^"]+(?=":[0-9]+\)$)/',
					'lnum' => '/(?<=used on line )[0-9]+/',
					'port' => '/(?<=port )[0-9]+/'
				]
			];
		}
		
		private function render($string) {
			// Replace variables with values
			if (preg_match('/\${.+}/', $string)) {
				preg_match('/(?<=\${).+(?=})/', $string, $key);
				$replacement = $this->variables[$key[0]];
				return preg_replace('/\${.+}/', $replacement, $string);
			}
			
			return $string;
		}
		
		public function server() {
			return shell_exec("apachectl -V | grep =");
		}
		
		public function vhosts() {
			return shell_exec("apachectl -D DUMP_VHOSTS");
		}
		
		// Get Sites from our Config Files
		public function getSiteConfig($domain = null) {
			$lines = $this->cfgfile;
			$sites = [];
			
			foreach ($lines as $line) {
				if (str_contains($line, '.test') && ! str_contains($line, "#")) {
					$site = new stdClass();
					
					foreach ($this->regex->cfgfile as $i => $pattern) {
						preg_match($pattern, $line, $match);
						$site->$i = $this->render($match[0]);
					}
					
					$domain = $site->domain;
					unset($site->domain);
					
					preg_match_all("/.+namevhost $domain.+/", $this->apachectl, $cfg_lines);
					
					// Figure out what port(s) this vhost uses
					foreach ($cfg_lines[0] as $cfg_line) {
						preg_match($this->regex->apachectl->port, $cfg_line, $port);
						$site->port[] = $port[0];
					}
					
					$site->port = implode(",", $site->port);
					
					$sites[$domain] = $site;
				}
			}
			
			return $sites;
		}
		
		// Get Sites from Apache Controller
		public function getVhostConfig($domain = null) {
			$lines = explode("\n", $this->apachectl);
			$lines = array_values(array_filter($lines, fn($line) => str_contains($line, 'namevhost')));
			$sites = [];
			
			if ($domain) {
				$lines = array_values(array_filter($lines, fn($line) => str_contains($line, 'namevhost')));
			}
			
			foreach ($lines as &$line) {
				$line = trim($line);
				$site = new stdClass();
				$regex = $this->regex->apachectl;
				
				foreach ($this->regex->apachectl as $i => $pattern) {
					preg_match($pattern, $line, $match);
					$site->$i = $match[0];
				}
				
				$domain = $site->domain;
				unset($site->domain);
				
				// Get local server path to file if the domain hasn't been set
				if (! isset($sites[$domain])) {
					$cfg_line = file($site->conf)[$site->lnum - 1];
					preg_match($this->regex->cfgfile->path, $cfg_line, $path);
					$site->path = $this->render($path[0]);
					unset($site->conf, $site->lnum);
					$sites[$domain] = $site;
				} else {
					$sites[$domain]->port .= ",$site->port";
				}
			}
			
			return $sites;
		}
		
		private function parseVariables() {
			preg_match_all("/(?<=Define: ).+=.+/", $this->apachectl, $matches);
			$lines = $matches[0];
			$variables = [];
			
			foreach ($lines as &$line) {
				list($key, $value) = explode("=", $line);
				$variables[$key] = $value;
			}
			
			$this->variables = $variables;
		}
		
		public function getVariables() {
			return $this->variables;
		}
	}
?>
