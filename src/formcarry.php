<?php

	namespace Crema;
	
	require_once '../vendor/autoload.php';
	use Crema\CurlRequest;
	use DateTime;
	
	class Formcarry extends \stdClass {
		public function __construct($api_key) {
			$this->api_key = $api_key;
			$this->api = "https://formcarry.com/api";
		}
		
		public function setClients($clients) {
			$this->clients = $clients;
		}
		
		public function getForms($remap = false) {
			$url = "$this->api/forms?api_key=$this->api_key";
			
			$curl = new CurlRequest();
			$response = $curl->get($url);
			$forms = $response->json()['forms'];
			
			if ($remap) {
				foreach ($forms as &$form) {
					$date = new DateTime($form['createdAt']);
					$name = $form['name'];
					
					$form = [
						'client' => $form['name'],
						'name' => $form['name'],
						'id' => $form['_id'],
						'date' => $date->format('Y-m-d'),
						'time' => $date->format('h:i A'),
						'emails' => array_column($form['notify'], 'email')
					];
					
					if ($this->clients) {
						foreach ($this->clients as $key => $client) {
							$form['emailString'] = implode(",", $form['emails']);
							
							if (str_contains($name, $key)) {
								$name = trim(preg_replace("/$key|:| of /", "", $name));
								$form['client'] = $client;
								$form['name'] = $name;
							}
						}
					}
				}
				
				if ($this->clients) {
					usort($forms, fn($a, $b) => $a['client'] <=> $b['client']);
				}
			}
			
			return $forms;
		}
		
		public function getForm($formID) {
			$url = "$this->api/form/$formID/submissions?api_key=$this->api_key";
			
			$curl = new CurlRequest();
			$response = $curl->get($url);
			$this->form = $response->json();
			
			return $this->form;
		}
	}
	
?>
