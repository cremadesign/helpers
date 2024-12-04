<?php
	/*/ ========================================================================
		PHP Emailer Helper Class
		Stephen Ginn at Crema Design Studio
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
				$this->output = $str;
			};
			
			// Server settings
			$this->mail->SMTPDebug  = SMTP::DEBUG_CONNECTION;
			$this->mail->isSMTP();
			$this->mail->SMTPAuth   = true;
			$this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			
			// Content Defaults
			$this->mail->isHTML(true);
			$this->mail->CharSet = 'UTF-8';
			
			// Set default values if not provided in config
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
		
		public function login($credentials) {
			if (gettype($credentials) == "array") {
				$credentials = (object) $credentials;
			}
			
			$this->mail->Host       = $credentials->host;
			$this->mail->Username   = $credentials->username;
			$this->mail->Password   = $credentials->password;
			$this->mail->Port       = $credentials->port;
		}
		
		public function setFrom($sender) {
			if (gettype($sender) == "array") {
				$sender = (object) $sender;
			}
			
			$this->mail->setFrom($sender->email, $sender->name);
		}
		
		public function addAddress($recipients) {
			if (is_array($recipients)) {
				foreach ($recipients as $recipient) {
					$this->mail->addAddress($recipient->email, $recipient->name);
				}
			} else {
				$this->mail->addAddress($recipients->email, $recipients->name);
			}
		}
		
		public function clearAllRecipients() {
			$this->mail->clearAllRecipients();
		}
		
		public function getAddresses() {
			return $this->mail->getToAddresses();
		}
		
		public function getErrors() {
			return $this->mail->ErrorInfo;
		}
		
		public function sendEmail($payload, callable $onSuccess = null, callable $onError = null) {
			if (gettype($payload) == "array") {
				$payload = (object) $payload;
			}
	
			$this->mail->Subject = $payload->subject;
			$this->mail->Body    = $payload->body;
	
			if ($this->mail->send()) {
				if ($onSuccess) {
					call_user_func($onSuccess, $this->mail);
				}
				return true;
			} else {
				error_log('Mailer Error: ' . $this->mail->ErrorInfo);
				if ($onError) {
					call_user_func($onError, $this->mail->ErrorInfo);
				}
				return false;
			}
		}
	}
?>
