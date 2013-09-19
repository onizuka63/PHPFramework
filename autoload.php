<?php

define('INCLUDES_DIR', __DIR__ . '/lib/');

require_once dirname(__FILE__) . '/lib/functions.php';

/* register ClassLoader as class loader */
spl_autoload_register(array(ClassLoader::getInstance(), 'loadClass'));

class ClassLoader
{

    private static $SAVE_FILE = 'ClassLoader.save.php';

    /* singleton */
    private static $instance;

    /* stores a className -> filePath map */
    private $classList;
    /* tells whether working from saved file */
    private $refreshed;


    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            self::$instance = new ClassLoader();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->initClassList();
    }

    public function loadClass($className)
    {
        //echo $className . '<br/>';
        $tab = explode("\\", $className);
        $className = $tab[count($tab) - 1];

        if ( !array_key_exists($className, $this->classList) && !$this->refreshed )
        {
            $this->refreshClassList();
        }

        require_once($this->classList[$className]);

    }

    private function initClassList()
    {
        if (file_exists(INCLUDES_DIR . self::$SAVE_FILE))
        {
            require_once(INCLUDES_DIR . self::$SAVE_FILE);
            $this->refreshed = FALSE;
        }
        else
        {
            $this->refreshClassList();
        }
    }

    private function refreshClassList()
    {
        $this->classList = $this->scanDirectory(INCLUDES_DIR);
        $this->refreshed = TRUE;

        $this->saveClassList();
    }

    private function saveClassList() {

        $handle = fopen(INCLUDES_DIR . self::$SAVE_FILE, 'w');
        fwrite($handle, "<?php\r\n");

        foreach($this->classList as $class => $path){
            $line = '$this->classList' . "['" . $class . "'] = '" . $path . "';\r\n";
            fwrite($handle, $line);
        }

        fwrite($handle, '?>');
        fclose($handle);
    }

    private function scanDirectory ($directory) {
        
         // strip closing '/'
        if (substr($directory, -1) == '/') {
                $directory = substr($directory, 0, -1);
        }

        if (!file_exists($directory) || !is_dir($directory) || !is_readable($directory)) {
            return array();
        }
        
        $dirH = opendir($directory);
        $scanRes = array();

        while(($file = readdir($dirH)) !== FALSE) {

            // skip pointers
            if ( strcmp($file , '.') == 0 || strcmp($file , '..') == 0) {
                continue;
            }

            $path = $directory . '/' . $file;

            if (!is_readable($path)) {
                continue;
            }

            // recursion
            if (is_dir($path)) {
                $scanRes = array_merge($scanRes, $this->scanDirectory($path));

            } elseif (is_file($path)) {
                $className = explode('.', $file);
                if ( strcmp($className[1], 'class') == 0 && strcmp($className[2], 'php') == 0 ) {
                    $scanRes[$className[0]] = $path;
                }
            }
        }
        
        return $scanRes;
    }

}
