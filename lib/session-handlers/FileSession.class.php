<?php
/**
 * FileSession.class.php
 * @author Elvyrra SAS
 */

/**
 * This class implements SessionHandlerInterface to define a session engine based on file system
 * @package SessionHandlers
 */
class FileSession implements SessionHandlerInterface{
    /**
     * The session file directory
     * @var string
     */
    private $savePath;

    /**
     * Open a new session
     * @param string $savePath Not used
     * @param string $name Not used
     */
    public function open($savePath, $sessionName) {
        $this->savePath = $savePath;
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0755);
        }

        return true;
    }

    /**
     * Close the session
     */
    public function close() {
        return true;
    }

    /**
     * Read data of a session
     * @param string $sessionId The session id, corresponding to the session cookie
     * @return string The session data, serialized
     */
    public function read($sessionId) {
        return (string) @file_get_contents($this->savePath . '/sess_' . $sessionId );
    }


    /**
     * Write data on the session
     * @param string $sessionId The session id, corresponding to the session cookie
     * @param string $data The data session to write, serialized    
     */
    public function write($sessionId, $data) {
        return file_put_contents($this->savePath . '/sess_' . $sessionId, $data) === false ? false : true;
    }

    /**
     * Destroy the session
     * @param string $sessionId The session id, corresponding to the session cookie
     */
    public function destroy($sessionId) {
        $file = $this->savePath . '/sess_' . $sessionId;
        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }


    /**
     * Clean expired sessions
     * @param int $maxlifetime The session lifetime (not used)
     */
    public function gc($maxlifetime) {
        foreach (glob("$this->savePath/sess_*") as $file) {
            if (file_exists($file) && filemtime($file) + Conf::get('session.lifetime') < time()) {
                unlink($file);
            }
        }

        return true;
    }
}