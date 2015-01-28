<?php
/**********************************************************************
 *    						BLineChart.class.php
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
class BLineChart extends BChart{
    
    const STEPS_Y_AXIS = 5;
    const STEPS_X_AXIS = 10;
    const MARGIN_TOP = 25;
    const MARGIN_BOTTOM = 25;
    const MARGIN_LEFT = 50;
    const MARGIN_RIGHT = 30;
    const CURVE_COEFF = 1.1428;
    static $roundScales = array(0.1,0.2,0.4,0.5,1,2,2.5,4,5);
    public function __construct($options = array()){
        self::$default = array_merge(parent::$default,array(
            "lineWidth" => 1,
            "grid" => "y",
            "smooth" => true,
        ));

        
        parent::__construct($options);
		
    }
    
    /*** Display a line chart ***/
    public function output(){
		if($this->dark)
			self::$scolors = self::$dark;
        $height = $this->height  - self::MARGIN_TOP - self::MARGIN_BOTTOM;
        $width = $this->width  - self::MARGIN_LEFT - self::MARGIN_RIGHT;
        
        $result = "<svg height='$this->height' version='1.1' width='$this->width' xmlns='http://www.w3.org/2000/svg' id='$this->id' class='b-chart ".($this->smooth ? "smooth-chart" : "raw-chart")."'>";
        
        /*** Display the axis ***/
        // X Axis
        $result .= "<path d='M".self::MARGIN_LEFT.",".($this->height - self::MARGIN_BOTTOM)."H".($width + self::MARGIN_LEFT)."' stroke='".self::$scolors['xaxis']."' stroke-width='1' fill='none' />";
        // Y axis
        $result .= "<path d='M".self::MARGIN_LEFT.",".($this->height - self::MARGIN_BOTTOM)."V".self::MARGIN_TOP."' stroke='".self::$scolors['yaxis']."' stroke-width='1' fill='none' />";
        
        /*** Display the Y grid ***/
        foreach(array_reverse($this->getYScale()) as $i => $step){
            $result .= "<text x='".(self::MARGIN_LEFT - 10)."' y='".(self::MARGIN_TOP + ($height / self::STEPS_Y_AXIS * $i))."' text-anchor='end' stroke='none' fill='".self::$scolors['ylabels']."' font-size='12px' >$step</text>";
             
            if(strpos($this->grid, "y") !== false){
                $result .= "<path d='M".self::MARGIN_LEFT.",".(self::MARGIN_TOP + ($height / self::STEPS_Y_AXIS * $i))."H".($width + self::MARGIN_LEFT)."' stroke='".self::$scolors['xaxis']."' stroke-width='0.5' fill='none' />";
            }
        }
		
        
        /*** Display the x grid ***/
        $xscale = $this->getXScale();
        // foreach(range(0, count($xscale) - 1) as $i){
        $xsteps = count($xscale) - 1;
		if(!$xsteps)
			$xsteps = 1;
        foreach($xscale as $i => $step){
// 			$step = $xscale[$i];
            $result .= "<text  x='".(self::MARGIN_LEFT + ($width / $xsteps * $i))."' y='$this->height' text-anchor='middle' stroke='none' fill='".self::$scolors['xlabels']."' font-size='12px' >$step</text>";
             
            if(strpos($this->grid, "x") !== false){
                $result .= "<path d='M".(self::MARGIN_LEFT + ($width / $xsteps * $i)).",".($this->height - self::MARGIN_BOTTOM)."V".self::MARGIN_TOP."' stroke='".self::$scolors['yaxis']."' stroke-width='0.5' fill='none' />";
            }
        }
        
        /*** Display the data ***/
        $c = 0;
		foreach($this->series as $name => $serie){
            if(count($serie) > 2){
				// A chart must be at least two records to be able to draw a line
				$path = "";
				$coords = array();
				
				foreach($serie as $index => $value){
					if($this->xtype == "time" && !is_int($index))
						$index = strtotime($index);                    
						
					$x = ($index - $this->xmin) * $width / ($this->xmax - $this->xmin) + self::MARGIN_LEFT;
					$a = $height / ($this->ymax - $this->ymin);
					$b = $height;
					$y = -$a * ($value - $this->ymin) + self::MARGIN_TOP + $b ;    
					
					$coords[] = array('x' => $x, 'y' => $y);
				}
				
				if($this->smooth)
					$grads = $this->gradients($coords);
				
				$prev = null;			
				
				foreach($coords as $i => $coord){
					$x = $coord['x'];
					$y = $coord['y'];
					if($prev !== null){
						if($this->smooth){
							$g = $grads[$i];
							$lg = $grads[$i - 1];
							$ix = ($x - $prevx) / 4;
							$x1 = $prevx + $ix;
							$y1 = min($height, $prev + $ix * $lg);
							$x2 = $x - $ix;
							$y2 = min($height, $y - $ix * $g);
							$path .= "C$x1,$y1,$x2,$y2,$x,$y";
						}  
						else{
							$path .= "L$x,$y";
						}
					}
					elseif(!$this->smooth || $grads[$i] != null){
						$path .= "M$x,$y";
					}
					$prev = $y;
					$prevx = $x;
					$i ++;
				}
				$result .= "<path name='$name' d='$path' stroke='".$this->colors[$c]."' stroke-width='$this->lineWidth' fill='none' />";
			}
			$c++;
        }
        
        
        $result .= "</svg>";
        return  $result;
    }
	
        
    private function getYScale(){
        $maxs = array();
        $mins = array();
        foreach($this->ykeys as $key){
            if(!empty($this->series[$key])){
				$maxs[] = max($this->series[$key]);            			
				$mins[] = min($this->series[$key]);		
			}
        }
        $this->ymax = empty($maxs) ? 1 : max($maxs);
        $this->ymin = empty($mins) ? 0 : min($mins);
		 
        switch(true){
            case $this->ymax >=0 && $this->ymin >= 0:
                if($this->yscale == "fixed")   
                    $this->ymin = 0;
            break;
            
            case $this->ymax <=0 && $this->ymin <= 0:
                if($this->yscale == "fixed")   
                    $this->ymax = 0;
            break;
        }
		
		if($this->ymax - $this->ymin == 0)
			$test = abs($this->ymax);
		else
			$test = abs($this->ymax - $this->ymin);
		foreach(range(-15, 15) as $o){
			if($test < pow(10, $o)){
				$order = $o - 1;
				break;
			}
		}	
		
		foreach(self::$roundScales as $s){
			$s = $s * pow(10, $order);
			if($this->ymax - $this->ymin <= self::STEPS_Y_AXIS * $s){
				$scale = array();		
				// We have the right scale order, find the offset
				$offset = 0;
				if($this->ymin < 0){
					while($this->ymin < $offset)
						$offset -= $s;
				}
				else{									
					while($this->ymin > $offset + $s)
						$offset += $s;					
				}
				$this->ymin = $offset;	
				$this->ymax = self::STEPS_Y_AXIS * $s + $offset;
				foreach(range(0, self::STEPS_Y_AXIS) as $i){
					$scale[] = $i * $s + $offset;					
				}
				return $scale;
			}	
		}			
    }
    
    private function getXScale(){
        $scale = array();
		if(!$this->xsteps)
			$this->xsteps = self::STEPS_X_AXIS;
			
        switch($this->xtype){
            case "time" :        
				if(empty($this->data)){
					$times = array(new DateTime("-1 day"),new DateTime());					
				}
				else{
					$times = array_map(function($time){
						return new DateTime(is_int($time) ? "@".$time : $time);
					}, array_keys($this->data));
                }
				$max = end($times);
				$min = $times[0];			
				
                $interval = date_diff($min, $max)->days;
                $intervals = array(
                    "hour" => array(0.5,1,2,3,6,12,24),
                    "day" => array(1,2,5,10),
                    "month" => array(1,2,3,6,12),
                    "year" => array()
                );
                switch(true){			
                    case $interval <= 7:
                        $format = "D H:i";
                        $unit = "hour";
                        $ranks = array(0.5,1,2,3,6,12,24);
                    break;
                    
                    case $interval <= 90 :
                        $format = "d M";
                        $unit = "day";
                        $ranks = array(1,2,5,10);
                    break;
                    
                    case $interval <= 2000 :
                        $format = "Y-m-d";
                        $unit = "month";
                        $ranks = array(1,2,3,6,12);
                    break;
                    
                    default :
                        $format = "Y";
                        $ranks;
                    break;
                }
                $this->xmax = $max->getTimestamp();
                $this->xmin = $min->getTimestamp();
				
                foreach(range(0, $steps = min($this->xsteps, count($times))) as $i){
                    $point = date($format, floor(($this->xmax - $this->xmin) / $steps * $i + $this->xmin));
					if($scale[count($scale) - 1] != $point)
						$scale[] = $point;
                }
                
            break;
            
            case "numeric" :
                $abs = array_keys($this->data);
                $this->xmax = end($abs);
                $this->xmin = $abs[0];
                if(count($abs) < $this->xsteps){
                    $scale = $abs;
                }
                else{
            		foreach(range(-15, 15) as $o){
            			if(abs($this->xmax - $this->xmin) < pow(10, $o)){
            				$order = $o - 1;
            				break;
            			}
            		}	
            		
            		foreach(self::$roundScales as $s){
            			$s = $s * pow(10, $order);
            			if($this->xmax - $this->xmin < $this->xsteps * $s){
            				$scale = array();		
            				// We have the right scale order, find the offset
            				$offset = 0;
            				if($this->xmin < 0){
            					while($this->xmin < $offset)
            						$offset -= $s;
            				}
            				else{									
            					while($this->xmin > $offset + $s)
            						$offset += $s;					
            				}
            				$this->xmin = $offset;	
            				$this->xmax = $this->xsteps * $s + $offset;
            				foreach(range(0, $this->xsteps) as $i){
            					$scale[] = $i * $s + $offset;					
            				}
            				return $scale;
            			}	
            		} 
                }
            break;
        }
        return $scale;
    }
   
    
    private function gradients($coords) {
        $result = array();
        for($i = $_i = 0, $len = count($coords); $_i < $_len; $i = ++$_i){
			$coord = $coords[$i];
			if($coord['y'] != null){
				$next = isset($coords[$i + 1]) ? $coords[$i + 1] : array('y' => null);
				$prev = isset($coords[$i - 1]) ? $coords[$i - 1] : array('y' => null);
				
				if($prev['y'] != null && $next['y'] != null){
					$result[] = ($next['y'] - $prev['y']) / ($next['x'] - $prev['x']);					
				}
				elseif($prev['y'] != null){
					$result[] = ($coord['y'] - $prev['y']) / ($coord['x'] - $prev['x']);
				}
				elseif($next['y'] != null) {
					$result[] = ($next['y'] - $coord['y']) / ($next['x'] - $coord['x']);
				} 
				else {
					$result[] = null;
				}
			}
			else{
				$result[] = null;
			}
			return $result;
		}
		
        $indexes = array_keys($coords);
        foreach($indexes as $i => $coord){
            $next = isset($coords[$i + 1]) ? $coords[$i + 1] : null;
            $prev = isset($coords[$i - 1]) ? $coords[$i - 1] : null;
            
            
            if ($prev != null && $next != null) {
                $result[] = ($next['y'] - $prev['y']) / ($next['x'] - $prev['x']);
            } 
            elseif ($prev != null) {
                $result[] = ($coord['y'] - $prev['y']) / ($coord['x'] - $prev['x']);
            }
            elseif ($next != null) {
                $result[] = ($next['y'] - $coord['y']) / ($next['x'] - $coord['x']);
            } 
            else {
                $result[] = null;
            }
        } 
        return $result;
    }
    
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/