<?php
	use DeviceDetector\ClientHints;
	use DeviceDetector\DeviceDetector;
	use DeviceDetector\Parser\Client\Browser;
	
	// Get Location Data from IP address
	class UData extends \stdClass {
		public function __construct() {
			$this->ip = $this->getIP();
			$protocol = (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == "on") ? "https" : "http";
			$this->referer = $_SERVER['HTTP_REFERER'] ?? "$protocol://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
			
			$urls = [
				"http://ip-api.com/json/$this->ip",
				"http://ipinfo.io/$this->ip/json",
				"http://api.db-ip.com/v2/free/$this->ip"
			];
			
			$version = $_GET['v'] ?? 0;
			
			$this->url = $urls[$version];
		}
		
		public function getIP() {
			$ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
			
			if ($ip == '127.0.0.1') {
				$ip = trim(`dig +short myip.opendns.com @resolver1.opendns.com` ?? '');
			}
			
			return $ip;
		}
		
		public function getData() {
			// Build and Return JSON Array
			$data = [
				'lang' => substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '??', 0, 2),
				'uagent' => [
					'query' => $this->userAgent,
					'browser' => '??',
					'os' => '??'
				],
				'location' => [
					"ip" => $this->ip
				],
				'referer' => "$this->referer"
			];
			
			// Get Browser and OS from User Agent
			$clientHints = ClientHints::factory($_SERVER);
			$dd = new DeviceDetector($this->userAgent, $clientHints);
			
			$dd->parse();
			
			if ($dd->isBot()) {
				$botInfo = $dd->getBot();
				$data['uagent']['browser'] = "{$botInfo['name']} ({$botInfo['category']})";
			} else {
				$clientInfo = $dd->getClient();
				$osInfo = $dd->getOs();
				
				if ($clientInfo) {
					$data['uagent']['browser'] = "{$clientInfo['name']} {$clientInfo['version']}";
				}
				
				if ($osInfo) {
					$data['uagent']['os'] = "{$osInfo['name']} {$osInfo['version']}";
				}
			}
			
			if ($location = `curl $this->url` ?? false) {
				$location = json_decode($location, true);
				$data['location'] = $location;
			}
			
			$this->data = $data;
			
			return $data;
		}
		
		public function __toString() {
			header('Content-Type: application/json');
			return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		}
	}
?>
