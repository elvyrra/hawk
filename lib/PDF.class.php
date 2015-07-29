<?php
/**
 * PDF.class.php
 */

/**
 * This class uses dompdf to generate PDF from HTML templates
 */
require_once(LIB_DIR . "ext/dompdf/dompdf_config.inc.php");

class PDF{
    private $engine;

    public function __construct($html){
        $this->engine = new DOMPDF();
        $this->engine->load_html($html);

        $this->setPaper('A4', 'portrait');
        // $this->setStaticBasePath(Conf::get('rooturl'));
    }

    private function render(){
        $this->engine->render();
    }    

    public function setStaticBasePath($path){
        $this->engine->set_base_path($path);
    }

    public function setPaper($size, $orientation = 'portrait'){
        $this->engine->set_paper($size, $orientation);
    }

    public function save($file){
        $this->render();
        file_put_contents($file, $this->engine->output());
    }

    public function display($file = ''){
        Response::setContentType('application/pdf');
        $this->render();
        Response::set($this->engine->output($file));
        Response::end();
    }

    public function __toString(){
        return $this->display();
    }
}