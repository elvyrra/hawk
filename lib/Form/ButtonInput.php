<?php
/**
 * ButtonInput.php
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */

namespace Hawk;

/**
 * This class describes the button inputs in form (button, delete, dans submit)
 *
 * @package Form\Input
 */
class ButtonInput extends FormInput{
    // the type of the input
    const TYPE = "button";
    // this type of input is independant, so not inserted in database
    const INDEPENDANT = true;

    /**
     * Defines the icons for most common buttons
     *
     * @static array $defaultIcons
     */
    private static $defaultIcons = array(
        'valid' => 'save',
        'save' => 'save',
        'cancel' => 'ban',
        'close' => 'times',
        'delete' => 'times',
        'back' => 'reply',
        'next' => 'step-forward',
        'previous' => 'step-backward',
        'send' => 'mail-closed'
    );

    /**
     * Defines if the input has to be displayed on a new line. For button inputs, this property is defaulty set to false
     *
     * @var boolean
     */
    public $nl = false;

    /**
     * Display the input
     *
     * @return string The dislayed HTML
     */
    public function display(){
        if(!empty($this->notDisplayed)) {
            return '';
        }

        $param = get_object_vars($this);
        $param["class"] .= " form-button";
        if(empty($param['icon']) && isset(self::$defaultIcons[$this->name]))
        $param['icon'] = self::$defaultIcons[$this->name];

        $param = array_filter($param, function ($v) {
            return !empty($v);
        });

        if(!isset($param['label'])) {
            $param['label'] = $this->value;
        }
        $param['type'] = static::TYPE;

        if(!empty($param['href'])) {
            $param['data-href'] = $param['href'];
            unset($param['href']);
        }

        if(!empty($param['target'])) {
            $param['data-target'] = $param['target'];
            unset($param['target']);
        }


        $param = array_intersect_key(
            $param,
            array_flip(array(
                'id',
                'class',
                'icon',
                'label',
                'type',
                'name',
                'onclick',
                'style',
                'data-href',
                'data-target',
                'title'
            ))
        );
        $param = array_merge($param, $this->attributes);

        /*** Set the attribute and text to the span inside the button ***/
        $button = View::make(Theme::getSelected()->getView('button.tpl'), array(
            'class' => isset($param['class']) ? $param['class'] : '',
            'param' => $param,
            'icon' => isset($param['icon']) ? $param['icon'] : '',
            'label' => isset($param['label']) ? $param['label'] : '',
            'textStyle' => isset($param['textStyle']) ? $param['textStyle'] : '',
        ));

        if($this->nl) {
            return View::make(Theme::getSelected()->getView(Form::VIEWS_DIR . 'form-input-block.tpl'), array(
                'input' => $this,                
                'inputLabel' => '',
                'inputDisplay' => $button
            ));    
        }

        return $button;

    }


    /**
     * Check the submitted value
     *
     * @param Form $form The form the input is associated with
     *
     * @return bool This function always return true, because no value is expected from a button
     */
    public function check(&$form = null){
        return true;
    }
}
