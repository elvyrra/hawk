<?php

namespace Hawk;


class System extends Singleton {
    protected static $instance;


    /**
     * Execute a shell command, and returns the result in a string. If an error occurs, an exception will be thrown
     * with the stderr content as the exception message
     * @param   string $cmd The command to execute
     * @param   string $cwd The path where to execute the command
     * @return  string      The command result
     */
    public function cmd($cmd, $cwd = ROOT_DIR) {
        $descriptorspec = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $pipes = array();

        $resource = proc_open($cmd, $descriptorspec, $pipes, $cwd);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $status = trim(proc_close($resource));
        if ($status) {
            throw new SystemException($stderr);
        }

        return $stdout;
    }
}