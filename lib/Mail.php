<?php
/**
 * Mail.php
 */

namespace Hawk;

/**
 * This class use PHPMailer library to send mails from Hawk applications
 * @package Utils
 */
class Mail{
	use Utils;

	/**
	 * The PHPMailer instance
	 */
	private $mailer;

	/**
	 * Default mailing engine
	 */
	const DEFAULT_MAILER = 'mail';

	/**
	 * Make a new mail
	 * @param array $param The parameters to pass to PHPMailer
	 */
	public function __construct($param = array()){
		$this->mailer = new \PHPMailer;

		$param['Mailer'] = Option::get('main.mailer-type') ? Option::get('main.mailer-type') : self::DEFAULT_MAILER;
		if($param['Mailer'] == 'smtp' || $param['Mailer'] == 'pop3'){
			$param['Host'] = Option::get('main.mailer-host');
			$param['Port'] = Option::get('main.mailer-port');
			$param['Username'] = Option::get('main.mailer-username');
			$param['Password'] = Option::get('main.mailer-password');			
		}

		if($param['Mailer'] == 'smtp'){
			$param['Secure'] = Option::get('main.smtp-secured');
		}

		$this->map($param, $this->mailer);
		
		$this->mailer->CharSet = 'utf-8';
	}

	/**
	 * Static method to make a new mailer
	 * @param array $param The parameters to pass to PHPMailer	 
	 */
	public static function getInstance($param){
		return new self($param);
	}

	/**
	 * Set 'from'
	 * @param string $email The sender email address to set
	 * @param string $name The sender name to set
	 * @param Mail The instance itself, to permit chained actions
	 */
	public function from($email, $name = null) {
		$this->mailer->From = $email;
		if($name !== null){
			$this->fromName($name);
		}

		return $this;
	}

	/**
	 * Set 'from-name'
	 * @param string $name The sender name to set
	 * @param Mail The instance itself, to permit chained actions
	 */
	public function fromName($name) {
		$this->mailer->FromName = $name;

		return $this;
	}

	/**
	 * Add a recipient
	 * @param string $email The recipient email address
	 * @param string $name The recipient name
	 * @param Mail The instance itself, to permit chained actions
	 */
	public function to($email, $name = '') {
		$this->mailer->addAddress($email, $name);

		return $this;
	}

	/**
	 * Set 'Reply-To'
	 * @param string $email The email address to reply to
	 * @param string $name The name of the person to reply to
	 * @param Mail The instance itself, to permit chained actions
	 */
	public function replyTo($email, $name = '') {
		$this->mailer->addReplyTo($email, $name);

		return $this;
	}

	/**
	 * Add a recipient in copy
	 * @param string $email The email address to add in copy
	 * @param string $name The recipient's name to add in copy
	 * @param Mail The instance itself, to permit chained actions
	 */
	public function cc($email, $name = '') {
		$this->mailer->addCC($email, $name);

		return $this;
	}

	/**
	 * Add a recipient in hidden copy
	 * @param string $email The recipient's email address to add in hidden copy
	 * @param string $name The recipient's name to add in hidden copy
	 * @param Mail The instance itself, to permit chained actions
	 */
	public function bcc($email, $name){
		$this->mailer->addBCC($email, $name);

		return $this;
	}

	/**
	 * Attach a file
	 * @param string $path The file path to attach
	 * @param string $name The attachment name
	 * @param string $encoding The encoding system to add the attachment
	 * @param string $type The file MIME type
	 * @param string $disposition Disposition to use
	 * @param Mail The instance itself, to permit chained actions
	 */
	public function attach($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment'){
		$this->mailer->addAttachment($path, $name, $encoding, $type, $disposition);

		return $this;
	}

	/**
	 * Set email subject
	 * @param string $subject The email subject
	 * @param Mail The instance itself, to permit chained actions
	 */
	public function subject($subject){
		$this->mailer->Subject = $subject;

		return $this;
	}

	/**
	 * set HTML content
	 * @param string $html The html content to set
	 * @param Mail The instance itself, to permit chained actions
	 */
	public function html($html){
		$this->mailer->isHTML(true);

		$this->mailer->Body = $html;

		if(empty($this->mailer->AltBody)){
			$text = preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $html);
			$text = strip_tags($text);

			$this->text($text);
		}

		return $this;
	}

	/**
	 * Set text content
	 * @param string $text The text to set
	 * @param Mail The instance itself, to permit chained actions
	 */
	public function text($text){
		$this->mailer->AltBody = $text;

		return $this;
	}


	/**
	 * Send the mail	 
	 */
	public function send(){
		if(!$this->mailer->send()){
			Log::error('The mail could not be sent because : ' . $this->mailer->ErrorInfo);
			throw new MailException($this->mailer->ErrorInfo);
		}
		Log::info('An email was sent to ' . implode(', ', $this->mailer->getAllRecipientAddresses()));
	}
}

/**
 * MailException
 */
class MailException extends \Exception{

}