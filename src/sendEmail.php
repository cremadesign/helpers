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
		
		public function __construct() {
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
			if (gettype($recipients) == "array") {
				$recipients = (object) $recipients;
			}
			
			if (gettype($recipients) == "object") {
				$recipients = [$recipients];
			}
			
			foreach ($recipients as $recipient) {
				$this->mail->addAddress($recipient->email, $recipient->name);
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
		
		public function sendEmail($payload) {
			if (gettype($payload) == "array") {
				$payload = (object) $payload;
			}
			
			$this->mail->Subject = $payload->subject;
			$this->mail->Body    = $payload->body;
			
			if ($this->mail->send()) {
				echo $payload->body;
			} else {
				echo 'Message could not be sent.';
				echo 'Mailer Error: ' . $this->mail->ErrorInfo;
			}
		}
	}
?>
