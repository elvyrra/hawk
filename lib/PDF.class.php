<?php
/**
 * PDF.class.php
 */


/**
 * This class uses dompdf to generate PDF from HTML templates
 */
class PDF{
    /**
     * The PDF engine (DOMPDF instance)
     */
    private $engine;

    /**
     * Constructor
     * @param string $html The html to parse into a PDF file
     */
    public function __construct($html){
        require_once(LIB_DIR . "ext/dompdf/dompdf_config.inc.php");
        
        $this->engine = new DOMPDF();
        $this->engine->load_html($html);

        $this->setPaper('A4', 'portrait');
    }

    /**
     * Compute HTML to PDF
     */
    private function render(){
        $this->engine->render();
    }    

    /**
     * Set the base path for static files in source HTML
     * @param string $path The source path to apply as base path for the static files
     */
    public function setStaticBasePath($path){
        $this->engine->set_base_path($path);
    }

    /**
     * Set the paper properties
     * @param mixed $size The size of the paper : 'A4', 'letter', ...
     * @param string $orientation The orientation of the pages : 'portrait', 'landscape'
     */
    public function setPaper($size, $orientation = 'portrait'){
        $this->engine->set_paper($size, $orientation);
    }

    /**
     * Save the generated PDF in a file
     * @param string $file The file to save
     */
    public function save($file){
        $this->render();
        file_put_contents($file, $this->engine->output());
    }

    /**
     * Output the PDF to the Web client
     * @param string $file The file to output if the client wants to save the PDF on his device
     */
    public function display($file = ''){
        Response::setContentType('application/pdf');
        $this->render();
        Response::set($this->engine->output($file));
        Response::end();
    }
}