<?php
/**
 * Crontab.class.php
 * @author Elvyrra SAS
 */

namespace Hawk;

/**
 * This class is used to add, and remove cron jobs. To use this class, you must have rights to execute shell commands on the server
 * @package Utils
 */
class Crontab {
    
    /**
     * Get all the registered cron jobs
     * @return array The list of the cron jobs
     */
    private static function get() {
        $output = shell_exec('crontab -l');
        $array = explode(PHP_EOL, trim($output));

        foreach ($array as $key => $item) {
            if ($item == '') {
                unset($array[$key]);
            }
        }
        return $array;
    }
    

    /**
     * Save crontab 
     * @param array $jobs The list of jobs to register in the crontab
     * @param boolean True if the crontab has been regsitered, false if an error occured
     */
    private static function save($jobs = array()) {
        // Register the crons in a tmp file
        $tmp_file = uniqid("tmp");        
        file_put_contents( $tmp_file, implode(PHP_EOL, $jobs) . PHP_EOL );

        // Register the crontab
        $output = shell_exec("crontab $tmp_file");

        // Remove the tmpfile 
        unlink($tmp_file);
        return (bool) $output;	
    }
    

    /**
     * Check if a job exists in the crontab. All crons created by this class are commented with a name, and the existence check is computed on that name
     * @param string $jobname The name of the cron to search
     * @return boolean true if the job exists, false else.
     */
    public static function jobExist($jobname) {
        $jobs = self::get();
        foreach($jobs as $job){
            if(strpos($job, "# $jobname") !== false){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Add a new cron job
     * @param string $name The name of the cron job, that will be put as command comment, to be retreived later by this class
     * @param string $job The job to add, with the format of crontab
     * @return boolean True if the job has been added, false if an error occurs
     */
    public static function add($name, $job) {
        self::remove($name);
        
        $jobs = self::get();
        $jobs[] = "$job # $name";
        return self::save($jobs);
    }
    

    /**
     * Remove a cron job
     * @param string $name The job name, present in the comment of the job command
     * @return boolean true if the job has been removed, false if an error occurs or if the job does not exist
     */
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
