<?php


class Crontab {
    
    // In this class, array instead of string would be the standard input / output format.
    
    // Legacy way to add a job:
    // $output = shell_exec('(crontab -l; echo "'.$job.'") | crontab -');
    static public function get() {
        $output = shell_exec('crontab -l');
        $array = explode("\n", trim($output));
        foreach ($array as $key => $item) {
            if ($item == '') {
                unset($array[$key]);
            }
        }
        return $array;
    }
    
    static public function save($jobs = array()) {
        $tmp_file = uniqid("tmp");
        file_put_contents($tmp_file, implode("\n", $jobs)."\n");
        $output = shell_exec("crontab $tmp_file");
        unlink($tmp_file);
        return $output;	
    }
    
    static public function jobExist($jobname) {
        $jobs = self::get();
        foreach($jobs as $job){
            if(strpos($job, "# $jobname") !== false){
                return true;
            }
        }
        return false;
    }
    
    static public function add($name, $job) {
        self::remove($name);
        
        $jobs = self::get();
        $jobs[] = "$job # $name";
        return self::save($jobs);
    }
    
    static public function remove($name) {
        if (self::jobExist($name)) {
            $jobs = self::get();
            foreach ($jobs as $i => $item) {
                if (strpos($item, "# $name") !== false) {
                    unset($jobs[$i]);
                }
            }
            return self::save($jobs);
        } 
        else {
            return false;
        }
    }
    
}

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/