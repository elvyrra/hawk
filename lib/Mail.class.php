<?php
/**
 * Mail.class.php
 */

/**
 * This class use PHPMail library to send mails from Hawk applications
 */
class Mail{
	private $mailer;

	const DEFAULT_MAILER = 'mail';

	/**
	 * Make a new mail
	 * @param array $param The parameters to pass to PHPMailer
	 */
	public function __construct($param = array()){
		$this->mailer = new PHPMailer;

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

		foreach($param as $key => $value){
			$this->mailer->$key = $value;
		}

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
	 */
	public function fromName($name) {
		$this->mailer->FromName = $name;

		return $this;
	}

	/**
	 * set recipents
	 */
	public function to($email, $name = '') {
		$this->mailer->addAddress($email, $name);

		return $this;
	}

	/**
	 * Set 'Reply-To'
	 */
	public function replyTo($email, $name = '') {
		$this->mailer->addReplyTo($email, $name);

		return $this;
	}

	/**
	 * Add a user in copy
	 */
	public function cc($email, $name = '') {
		$this->mailer->addCC($email, $name);

		return $this;
	}

	/**
	 * Add a user in hidden copy
	 */
	public function bcc($email, $name){
		$this->mailer->addBCC($email, $name);

		return $this;
	}

	/**
	 * Add an attachment
	 */
	public function attach($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment'){
		$this->mailer->addAttachment($path, $name, $encoding, $type, $disposition);

		return $this;
	}

	/**
	 * set HTML content
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
	 */
	public function text($text){
		$this->mailer->AltBody = $text;

		return $this;
	}


	/**
	 * send the mail
	 */
	public function send(){
		if(!$this->mailer->send()){
			throw new MailException($this->mailer->ErrorInfo);
		}
	}

	/**
	 * Set subject
	 */
	public function subject($subject){
		$this->mailer->Subject = $subject;

		return $this;
	}
}

/**
 * MailException
 */
class MailException extends Exception{

}