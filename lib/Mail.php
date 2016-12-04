<?php
/**
 * Mail.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class use PHPMailer library to send mails from Hawk applications
 *
 * @package Network
 */
class Mail{
    use Utils;

    /**
     * The PHPMailer instance
     *
     * @var PHPMailer
     */
    private $mailer,

    /**
     * Use the default template, from the theme
     *
     * @var boolean
     */
    $useDefaultTemplate = true,

    /**
     * The title in the default template
     *
     * @var string
     */
    $title = '',

    /**
     * The content in the default template
     */
    $content = '';

    /**
     * Default mailing engine
     */
    const DEFAULT_MAILER = 'mail';

    /**
     * Make a new mail
     *
     * @param array $param The parameters to pass to PHPMailer
     */
    public function __construct($param = array()){
        $this->mailer = new \PHPMailer;

        $param['Mailer'] = Option::get('main.mailer-type') ? Option::get('main.mailer-type') : self::DEFAULT_MAILER;
        if($param['Mailer'] == 'smtp' || $param['Mailer'] == 'pop3') {
            $param['Host'] = Option::get('main.mailer-host');
            $param['Port'] = Option::get('main.mailer-port');
            $param['Username'] = Option::get('main.mailer-username');
            $param['Password'] = Option::get('main.mailer-password');
        }

        if($param['Mailer'] == 'smtp') {
            $param['Secure'] = Option::get('main.smtp-secured');
        }

        $this->map($param, $this->mailer);

        $this->from(Option::get('main.mailer-from'), Option::get('main.mailer-from-name'));

        $this->mailer->CharSet = 'utf-8';
    }

    /**
     * Static method to make a new mailer
     *
     * @param array $param The parameters to pass to PHPMailer
     */
    public static function getInstance($param){
        return new self($param);
    }

    /**
     * Set 'from'
     *
     * @param string $email The sender email address to set
     * @param string $name  The sender name to set
     *
     * @return Mail The instance itself, to permit chained actions
     */
    public function from($email, $name = null) {
        $this->mailer->From = $email;
        if($name !== null) {
            $this->fromName($name);
        }

        return $this;
    }

    /**
     * Set 'from-name'
     *
     * @param string $name The sender name to set
     *
     * @return Mail The instance itself, to permit chained actions
     */
    public function fromName($name) {
        $this->mailer->FromName = $name;

        return $this;
    }

    /**
     * Add a recipient
     *
     * @param string $email The recipient email address
     * @param string $name  The recipient name
     *
     * @return Mail The instance itself, to permit chained actions
     */
    public function to($email, $name = '') {
        if(is_array($email)) {
            foreach($email as $key => $value) {
                if(is_numeric($key)) {
                    $this->to($value);
                }
                else {
                    $this->to($key, $value);
                }
            }
        }
        else {
            $this->mailer->addAddress($email, $name);
        }

        return $this;
    }

    /**
     * Set 'Reply-To'
     *
     * @param string $email The email address to reply to
     * @param string $name  The name of the person to reply to
     *
     * @return Mail The instance itself, to permit chained actions
     */
    public function replyTo($email, $name = '') {
        if(is_array($email)) {
            foreach($email as $key => $value) {
                if(is_numeric($key)) {
                    $this->replyTo($value);
                }
                else {
                    $this->replyTo($key, $value);
                }
            }
        }
        else {
            $this->mailer->addReplyTo($email, $name);
        }

        return $this;
    }

    /**
     * Add a recipient in copy
     *
     * @param string $email The email address to add in copy
     * @param string $name  The recipient's name to add in copy
     *
     * @return Mail The instance itself, to permit chained actions
     */
    public function cc($email, $name = '') {
        if(is_array($email)) {
            foreach($email as $key => $value) {
                if(is_numeric($key)) {
                    $this->cc($value);
                }
                else {
                    $this->cc($key, $value);
                }
            }
        }
        else {
            $this->mailer->addCC($email, $name);
        }

        return $this;
    }

    /**
     * Add a recipient in hidden copy
     *
     * @param string $email The recipient's email address to add in hidden copy
     * @param string $name  The recipient's name to add in hidden copy
     *
     * @return Mail The instance itself, to permit chained actions
     */
    public function bcc($email, $name){
        if(is_array($email)) {
            foreach($email as $key => $value) {
                if(is_numeric($key)) {
                    $this->bcc($value);
                }
                else {
                    $this->bcc($key, $value);
                }
            }
        }
        else {
            $this->mailer->addBCC($email, $name);
        }

        return $this;
    }

    /**
     * Attach a file
     *
     * @param string $path        The file path to attach
     * @param string $name        The attachment name
     * @param string $encoding    The encoding system to add the attachment
     * @param string $type        The file MIME type
     * @param string $disposition Disposition to use
     *
     * @return Mail The instance itself, to permit chained actions
     */
    public function attach($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment'){
        $this->mailer->addAttachment($path, $name, $encoding, $type, $disposition);

        return $this;
    }

    /**
     * Set email subject
     *
     * @param string $subject The email subject
     *
     * @return Mail The instance itself, to permit chained actions
     */
    public function subject($subject){
        $this->mailer->Subject = $subject;

        return $this;
    }

    /**
     * Set HTML content
     *
     * @param string $html The html content to set
     *
     * @return Mail The instance itself, to permit chained actions
     */
    public function html($html){
        $this->mailer->isHTML(true);

        $this->mailer->Body = $html;

        if(empty($this->mailer->AltBody)) {
            $text = preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $html);
            $text = strip_tags($text);

            $this->text($text);
        }

        return $this;
    }

    /**
     * Set text content
     *
     * @param string $text The text to set
     *
     * @return Mail The instance itself, to permit chained actions
     */
    public function text($text){
        $this->mailer->AltBody = $text;

        return $this;
    }

    /**
     * Defaultly, the theme template email.tpl is used to send emails. Calling this function with $set=false
     * will override the default behavior and you will be able to set your own content HTML
     *
     * @param boolean $set True to keep the default HTML template, else false
     *
     * @return Mail The instance itself, to permit chained actions
     */
    public function setDefaultTemplate($set = true) {
        $this->useDefaultTemplate = $set;

        return $this;
    }


    /**
     * Set the title in the default HTML template
     *
     * @param string $title The title to set
     *
     * @return Mail The instance itself, to permit chained actions
     */
    public function title($title) {
        $this->title = $title;

        return $this;
    }


    /**
     * Set the content in the default HTML template
     *
     * @param string $content The content to set
     *
     * @return Mail The instance itself, to permit chained actions
     */
    public function content($content) {
        $this->content = $content;

        return $this;
    }

    /**
     * Prepare the content to send in the email
     */
    private function prepareContent() {
        if($this->useDefaultTemplate) {
            // Create the email content
            $cssVariables = Theme::getSelected()->getEditableVariables();
            $values = Theme::getSelected()->getVariablesCustomValues();
            $css = array();

            foreach($cssVariables as $var) {
                $css[$var['name']] = isset($values[$var['name']]) ? $values[$var['name']] : $var['default'];
            }

            // Format the email
            $emailHtml = View::make(Theme::getSelected()->getView('email.tpl'), array(
                'css' => $css,
                'title' => $this->title,
                'content' => $this->content,
                'logo' => Option::get('main.logo') ?
                    Plugin::get('main')->getUserfilesUrl(Option::get('main.logo')) :
                    Plugin::get('main')->getStaticUrl('img/hawk-logo.png')
            ));

            $this->html($emailHtml);
        }
    }

    /**
     * Send the mail
     *
     * @throws MailException
     */
    public function send(){
        $this->prepareContent();

        if(!$this->mailer->send()) {
            App::logger()->error('The mail could not be sent because : ' . $this->mailer->ErrorInfo);
            throw new MailException($this->mailer->ErrorInfo);
        }
        App::logger()->info('An email was sent to ' . implode(', ', array_keys($this->mailer->getAllRecipientAddresses())));
    }
}