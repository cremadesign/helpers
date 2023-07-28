<?php

	/*/
		Dreamhost API Wrapper
		Stephen Ginn at Crema Design Studio
		Created on July 28, 2023
	/*/

	namespace Crema;
	
	class DreamhostApi extends \stdClass {
		public function __construct($credentials) {
			$this->api = "https://api.dreamhost.com/?key=$credentials->apikey";
			$this->userid = $credentials->userid;
		}
		
		// Set the User ID to filter with on accounts with shared access
		public function setUserID($userid) {
			$this->userid = $userid;
		}
		
		// Get Simple List of Domains
		public function getRecords($domain = null) {
			$url = "$this->api&cmd=dns-list_records&format=json";
			$data["domains"] = [];
			
			$records = json_decode(`curl "$url"`, true)['data'];
			
			if ($domain) {
				$records = array_values(array_filter($records, fn($record) => strpos($record["record"], $domain) !== false));
			}
			
			if ($this->userid) {
				$records = array_values(array_filter($records, fn($record) => $record["account_id"] == $this->userid));
			}
			
			return $records;
		}
		
		// Get Array of DNS Records
		public function getDomains() {
			$records = $this->getRecords();
			$records = array_values(array_filter($records, fn($record) => $record["type"] == "A"));
			$records = array_values(array_filter($records, fn($record) => strpos($record["record"], 'www') !== false));
			
			$records = array_column($records, 'zone');
			$records = array_unique($records);
			sort($records);
			
			return $records;
		}
		
		// Add a DNS Record
		public function addRecord($recordVal, $value) {
			$url = "$this->api&cmd=dns-add_record&record=$recordVal&type=A&value=$value&editable=0";
			$res = explode("\n", trim(`curl "$url"`));
			
			$response = [];
			$response['url'] = $url;
			$response[$res[0]] = $res[1];
			
			return $response;
		}
		
		// Add DNS Records
		public function addRecords($url, $value) {
			$recordValues = [
				"$url",
				"www.$url",
				"ssh.$url",
				"ftp.$url"
			];
			
			$responses = [];
			
			foreach ($recordValues as $recordVal) {
				$responses[] = $this->addRecord($recordVal, $value);
			}
			
			return $responses;
		}
	}
?>
