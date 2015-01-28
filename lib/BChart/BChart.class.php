<?php
/**********************************************************************
 *    						BChart.class.php
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/

class BChart{
    protected $id, $data, $xkeys, $ykeys;
    
    const REQUIRED = "id data ykeys width height";
    
    static $scolors = array(
        "xaxis" => "#aaaaaa",
        "yaxis" => "#aaaaaa",
        "xlabels" => "#888888",
        "ylabels" => "#888888",        
    );
	static $dark = array(
        "xaxis" => "#777777",
        "yaxis" => "#777777",
        "xlabels" => "#222222",
        "ylabels" => "#222222",        
    );
      
    static $default = array(
        "xtype" => "time",
        "xformat" => "auto",
        "yscale" => "fixed",
		"colors" => array(
            "#6495ED", 
			"#A52A2A",
			"#9ACD32",
			"#8A2BE2",
			"#F4A460" ,
			"#006400",
			"#00CED1",
			"#FF8C00",
			"#000000",
			"#777777",	
			"#DAA520", // golden rod
			"#7B68EE", // medium slate blue
			"#FF00FF", // fuchsia
			"FF6347" //tomato
        )
    );	
    
    public function __construct($options = array()){
        foreach(self::$default as $key => $value)
            $this->$key = $value;
            
        foreach($options as $key => $value){        
            $this->$key = $value;
        }
        
        /*** Check all required data has been given ***/
        foreach(explode(" ", self::REQUIRED) as $required){
            if(!isset($this->$required))
                throw new Exception("BChart constructor: the field \"$required\" must be defined");
        }
		
        $this->series = array();
		
        foreach($this->ykeys as $key){
            $this->series[$key] = array();
            foreach($this->data as $x => $line){
                if(isset($line[$key]) && is_finite($line[$key])){					
                    $this->series[$key][$x] = $line[$key];
				}
            }
        }
    }
    
    public function display(){
		echo $this->output();
	}
	
    /*** Display the chart. This method is overriden by every child class ***/
	public function output(){}
	public function export($type="png"){}
	
	public function __toString(){
		return $this->output();
	}
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/