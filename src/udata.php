<?php
	use DeviceDetector\ClientHints;
	use DeviceDetector\DeviceDetector;
	use DeviceDetector\Parser\Client\Browser;

	// Get Location Data from IP address
	class UData extends \stdClass {
		public function __construct($apiKey = null) {
			$this->apiKey = $apiKey;
			$this->ip = $this->getIP();
			$protocol = (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == "on") ? "https" : "http";
			$this->referer = $_SERVER['HTTP_REFERER'] ?? "$protocol://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

			$this->url = $this->apiKey
				? "https://pro.ip-api.com/json/{$this->ip}?key={$this->apiKey}"
				: "http://ip-api.com/json/{$this->ip}";
		}

		public function getIP() {
			$ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

			$ip = filter_var($ip, FILTER_VALIDATE_IP) ? $ip : $_SERVER['REMOTE_ADDR'];

			if ($ip == '127.0.0.1') {
				$ip = trim(file_get_contents('https://api.ipify.org') ?: '');
			}

			return $ip;
		}

		public function getData() {
			if (is_null($this->userAgent)) {
				return [];
			}

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

			$ch = curl_init($this->url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3);
			$response = curl_exec($ch);
			curl_close($ch);

			if ($response) {
				$location = json_decode($response, true);
				if ($location) {
					$data['location'] = $location;
				}
			}

			$this->data = $data;

			return $data;
		}

		public function __toString() {
			header('Content-Type: application/json');
			return json_encode($this->data ?? [], JSON_PRETTIER);
		}
	}
?>
