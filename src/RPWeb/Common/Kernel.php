<?php
namespace RPWeb\Common;

use DI\Definition\Resolver\ResolverDispatcher;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Pecee\SimpleRouter\SimpleRouter;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/../Helpers/helpers.php';

class Kernel
{
    /**
     * Kernel instance.
     *
     * @var Kernel
     */
    private static $instance = null;

    /**
     * Database capsule.
     *
     * @var Capsule
     */
    private $capsule;

    /**
     * Twig environment.
     *
     * @var Environment
     */
    private $twig;

    /**
     * Gets the kernel instance.
     *
     * @return Kernel|null
     */
    public static function getInstance() : ?Kernel
    {
        return self::$instance;
    }

    /**
     * Load the system core.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (!is_null(self::$instance)) {
            throw new \Exception('Kernel already initialised.');
        }
        self::$instance = $this;

        $this->capsule = new Capsule();

        $this->capsule->addConnection(array(
            'driver'   => $config['db']['driver'],
            'host'     => $config['db']['host'],
            'database' => $config['db']['database'],
            'username' => $config['db']['username'],
            'password' => $config['db']['password'],
            'charset'  => $config['db']['charset'],
            'collation'=> $config['db']['collation'],
            'prefix'   => '',
        ));

        $this->capsule->setEventDispatcher(new Dispatcher(new Container()));

        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        $loader = new FilesystemLoader(__DIR__ . '/../Templates');

        $cache = false;
        if($config['enable_cache']) {
            $cache = __DIR__ . '/../../../cache/templates';
        }

        $this->twig = new \Twig\Environment($loader, array(
            'cache' => $cache
        ));
    }

    /**
     * Starts the application.
     *
     * @return void
     */
    public function start()
    {
        SimpleRouter::setDefaultNamespace('\\RPWeb\\Controllers');

        Routes::initRoutes();

        SimpleRouter::start();
    }
    
    /**
     * Get twig environment.
     *
     * @return  Environment
     */ 
    public function getTwig() : Environment
    {
        return $this->twig;
    }
}
