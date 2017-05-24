<?php
namespace Hawk;

/**
 * This class is used to manage the crons of the hawk instance
 */
class Cron {
    /**
     * The cron minutes value
     * @var string
     */
    public $minutes = '*';


    /**
     * The cron hours value
     * @var string
     */
    public $hours = '*';

    /**
     * The cron day of month value
     * @var string
     */
    public $dayOfMonth = '*';

    /**
     * The cron month value
     * @var string
     */
    public $month = '*';

    /**
     * The cron day of week value
     * @var string
     */
    public $dayOfWeek = '*';

    /**
     * The called script
     * @var string
     */
    public $script;

    /**
     * The registered crons
     * @var array
     */
    private static $crons = null;

    /**
     * Constructor
     * @param array $data An associative array describing the cron. The possible properties are the class properties.
     *                    'script' is mandatory
     */
    private function __construct($data) {
        $this->setData($data);
    }


    /**
     * Set cron data
     * @param array $data The cron data
     */
    private function setData($data) {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Get the regex to find a Hawk cron in the crontab
     * @return string
     */
    private static function getHawkCronRegex() {
        return '/^php\s+' . preg_quote(ROOT_DIR . 'start-cron.php', '/') . '\s+(.+?)(#.*)?$/';
    }


    /**
     * Get the command of a given script to insret in the cron tab
     * @return string         The command to inser
     */
    private function getHawkCronCmd() {
        return 'php ' . ROOT_DIR . 'start-cron.php ' . $this->script;
    }


    /**
     * Get all the crons managed by the current instance of Hawk
     * @return array The list of Cron instances
     */
    public static function getAll() {
        if(self::$crons === null) {
            $crontab = trim(shell_exec('crontab -l'));
            $crons = explode(PHP_EOL, $crontab);
            self::$crons = array();

            foreach($crons as $cron) {
                if(!empty($cron)) {
                    list($minutes, $hours, $dayOfMonth, $month, $dayOfWeek, $command) = preg_split('/\s+/', $cron, 6);

                    $regex = self::getHawkCronRegex();

                    if(preg_match($regex, $command, $match)) {
                        // The cron is managed by the current instance of Hawk
                        self::$crons[] = new self(array(
                            'minutes' => $minutes,
                            'hours' => $hours,
                            'dayOfMonth' => $dayOfMonth,
                            'month' => $month,
                            'dayOfWeek' => $dayOfWeek,
                            'script' => $match[1]
                        ));
                    }
                }
            }
        }

        return self::$crons;
    }


    /**
     * Get a cron by the name of the executed script
     * @param  string $script The path of the executed script
     * @return Cron           The found cron, or null if not found
     */
    public static function getByScript($script) {
        $crons = self::getAll();

        foreach($crons as $cron) {
            if($cron->script === $script) {
                return $cron;
            }
        }

        return null;
    }


    /**
     * Add a cron job
     * @param array $data [description]
     */
    public static function add($data = array()) {
        if(empty($data['script'])) {
            throw new \Exception('The property "script" must be filled when creating a cron');
        }

        $existing = self::getByScript($data['script']);

        if($existing) {
            $existing->setData($data);
        }
        else {
            self::$crons[] = new self($data);
        }

        self::writeCrontab();
    }

    /**
     * Write the crontab
     */
    private static function writeCrontab() {
        $crontab = trim(shell_exec('crontab -l'));
        $allCrons = explode(PHP_EOL, trim($crontab));
        $jobs = array();

        // Get the cron that are not managed by Hawk
        foreach($allCrons as $cron) {
            if(!empty($cron)) {
                list($minutes, $hours, $dayOfMonth, $month, $dayOfWeek, $command) = preg_split('/\s+/', $cron, 6);
                $regex = self::getHawkCronRegex();
                if($cron && !preg_match($regex, $command)) {
                    // The cron is not managed by Hawk, keep it intact
                    $jobs[] = $cron;
                }
            }
        }

        // Add the Hawk crons
        foreach(self::$crons as $cron) {
            $jobs[] = sprintf(
                '%s %s %s %s %s %s',
                $cron->minutes,
                $cron->hours,
                $cron->dayOfMonth,
                $cron->month,
                $cron->dayOfWeek,
                $cron->getHawkCronCmd()
            );
        }

        $tmpFile = TMP_DIR . uniqid();
        file_put_contents($tmpFile, implode(PHP_EOL, $jobs) . PHP_EOL);

        App::system()->cmd('crontab ' . $tmpFile);
    }

    /**
     * Save a cron
     */
    public function update() {
        if(empty($this->script)) {
            throw new \Exception('The property "script" must be filled when creating a cron');
        }

        self::writeCrontab();
    }

    /**
     * Delete a cron job
     */
    public function delete() {
        $index = array_search($this, self::$crons);

        array_splice(self::$crons, $index, 1);

        self::writeCrontab();
    }
}