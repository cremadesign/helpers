<?php
	/*/ ========================================================================
		PHP Emailer Helper Class
		Stephen Ginn at Crema Design Studio
		Updated on 2025-11-13
	======================================================================== /*/
	
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

	class Emailer {
		public $mail;
		public $output;
		public $credentials;
		public $recipient;
		public $payload;
		
		public function __construct($credentials = null, $sender = null, $recipient = null) {
			$this->mail = new PHPMailer(true);
			
			$this->mail->Debugoutput = function($str, $level) {
				$this->output .= $str . "\n"; // Append instead of overwrite
			};
			
			// Server settings - Only enable debug in development
			$this->mail->SMTPDebug = $this->isDevelopment() ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
			$this->mail->isSMTP();
			$this->mail->SMTPAuth = true;
			
			// Content Defaults
			$this->mail->isHTML(true);
			$this->mail->CharSet = 'UTF-8';
			
			// Set default values if provided
			if ($credentials) {
				$this->login($credentials);
			}
			
			if ($sender) {
				$this->setFrom($sender);
			}
			
			if ($recipient) {
				$this->addAddress($recipient);
			}
			
			return $this;
		}
		
		private function isDevelopment() {
			return str_ends_with($_SERVER['HTTP_HOST'], '.test');
		}
		
		public function login($credentials) {
			$credentials = $this->toObject($credentials);
			
			// Validate required credentials
			if (!isset($credentials->host, $credentials->username, $credentials->password, $credentials->port)) {
				throw new Exception('Missing required credentials: host, username, password, port');
			}
			
			$this->mail->Host = $credentials->host;
			$this->mail->Username = $credentials->username;
			$this->mail->Password = $credentials->password;
			$this->mail->Port = (int) $credentials->port;
			
			// Set encryption type based on port
			if ($credentials->port == 465) {
				$this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			} elseif ($credentials->port == 587) {
				$this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			}
			
			// Only disable SSL verification in development
			if ($this->isDevelopment()) {
				$this->mail->SMTPOptions = [
					'ssl' => [
						'verify_peer' => false,
						'verify_peer_name' => false,
						'allow_self_signed' => true
					]
				];
			}
			
			return $this;
		}
		
		public function setFrom($sender) {
			$sender = $this->toObject($sender);
			
			if (!isset($sender->email)) {
				throw new Exception('Sender email is required');
			}
			
			$name = $sender->name ?? '';
			$this->mail->setFrom($sender->email, $name);
			
			return $this;
		}
		
		public function addAddress($recipients) {
			// Normalize input to array format
			$recipients = $this->normalizeRecipients($recipients);
			
			foreach ($recipients as $recipient) {
				$recipient = $this->toObject($recipient);
				
				if (!isset($recipient->email) || !filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
					throw new Exception('Invalid recipient email: ' . ($recipient->email ?? 'null'));
				}
				
				$name = $recipient->name ?? '';
				$this->mail->addAddress($recipient->email, $name);
			}
			
			return $this;
		}
		
		// Helper method to normalize recipient input to consistent format
		private function normalizeRecipients($recipients) {
			// If it's a string (single email), convert to array with email
			if (is_string($recipients)) {
				return [['email' => $recipients]];
			}
			
			// If it's not an array, convert to array
			if (!is_array($recipients)) {
				return [['email' => (string) $recipients]];
			}
			
			// Check if it's an associative array with 'email' key (single recipient object)
			if (isset($recipients['email'])) {
				return [$recipients];
			}
			
			// Check if it's a numeric array of strings (array of email addresses)
			if (isset($recipients[0]) && is_string($recipients[0])) {
				return array_map(function($email) {
					return ['email' => $email];
				}, $recipients);
			}
			
			// Already an array of recipient objects/arrays
			return $recipients;
		}
		
		public function clearAllRecipients() {
			$this->mail->clearAllRecipients();
			return $this;
		}
		
		public function getAddresses() {
			return $this->mail->getToAddresses();
		}
		
		public function getErrors() {
			return $this->mail->ErrorInfo;
		}
		
		public function getDebugOutput() {
			return $this->output;
		}
		
		public function sendEmail($payload, callable $onSuccess = null, callable $onError = null) {
			$payload = $this->toObject($payload);
			
			if (!isset($payload->subject) || !isset($payload->body)) {
				throw new Exception('Email payload must include subject and body');
			}
	
			$this->mail->Subject = $payload->subject;
			$this->mail->Body = $payload->body;
			
			// Set plain text alternative if provided
			if (isset($payload->altBody)) {
				$this->mail->AltBody = $payload->altBody;
			}
	
			try {
				if ($this->mail->send()) {
					if ($onSuccess) {
						call_user_func($onSuccess, $this->mail);
					}
					return true;
				}
			} catch (Exception $e) {
				error_log('Mailer Error: ' . $this->mail->ErrorInfo);
				if ($onError) {
					call_user_func($onError, $this->mail->ErrorInfo);
				}
				return false;
			}
			
			return false;
		}
		
		// Helper method to convert arrays to objects
		private function toObject($data) {
			return is_array($data) ? (object) $data : $data;
		}
	}
?>
