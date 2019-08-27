# php-setup

Setup basico para padronizar projeto

## Install

````bash
composer require paliari/php-setup

````

## Estrutura de diretorio

```
project
│   README.md
│   cli-config.php
│   ...
└───app
│   │   ...
│   └───Controllers
│       │   Auth.php
│       │   Home.php
│       │   ...
│       Middlewares
│       │   AuthMiddleware.php
│       │   ...
│       Repositories
│       │   PessoaRepository.php
│       │   UsuarioRepository.php
│       │   ...
│       Services
│       │   AuthService.php
│       │   ...
│
└───config
│   │   AppSetup.php
│   │   DbSetup.php
│   │   Envs.php
│   │   Settings.php
│   │   ...
│   │
│   └───Providers
│       │   AuthProvider.php
│       │   Registers.php
│       │   ...
│       Routes
│       │   AuthRoute.php
│       │   Router.php
│       │   ...
└───db
│   │   ...
│   └───models
│       │   Pessoa.php
│       │   Usuario.php
│       │   ...
│       Types
│       │   DbCustomType.php
│       │   ...
└───public
    │   favicon.ico
    │   index.php
    │   ...
    └───assets
        └───css
        └───images

```

## Exemplos

### Settings

config/Settings.php

````php
<?php

namespace Config;

use Paliari\PhpSetup\Config\SettingsInterface;

/**
 * Class Settings
 *
 * @package Config
 */
class Settings implements SettingsInterface
{

    public static function get(): array
    {
        return [
            'settings' => [
                'displayErrorDetails'    => getenv('APP_ENV') === 'dev',
                'addContentLengthHeader' => false,
            ],
        ];
    }

}

````

### DbSetup

config/DbSetup.php

````php
<?php

namespace Config;

use Paliari\I18n;
use Paliari\PhpSetup\Db\AbstractSetup;

class DbSetup extends AbstractSetup
{

    protected static function addCustomTypes(): void
    {
        // TODO: Implement addCustomTypes() method.
    }

    protected static function addPathsI18n(): void
    {
        I18n::instance()->addLocalesPath(ROOT_APP . '/db/locales');
    }

    public static function getProxyDir(): string
    {
        return ROOT_APP . '/db/Proxies';
    }

    public static function getModelsDir(): string
    {
        return ROOT_APP . '/db/models';
    }

}

````


boot.php

````php
<?php

require __DIR__ . '/vendor/autoload.php';

$db_params = [
    'driver'        => getenv('DB_DRIVER'),
    'host'          => getenv('DB_HOST'),
    'port'          => getenv('DB_PORT'),
    'dbname'        => getenv('DB_NAME'),
    'user'          => getenv('DB_USER'),
    'password'      => getenv('DB_PASSWORD'),
    'service'       => true,
    'charset'       => 'UTF8',
    'driverOptions' => ['charset' => 'UTF8'],
];
$config = \Config\DbSetup::configure($db_params);\

````


### Model

models/Pessoa.php

````php

<?php

use Paliari\PhpSetup\Db\AbstractModel;

/**
 * Anexo
 *
 * --------- properties ----------
 *
 * @property int    $id
 * @property string $name
 * @property string $cpf_cnpj
 * @property \Paliari\Brasil\DateTime\DateTimeBr $created_at
 * @property \Paliari\Brasil\DateTime\DateTimeBr $updated_at
 *                                                             
 * --------- properties ----------
 *
 * @ORM\Table(
 *   name="pessoas",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="UK_PESSOA_CPF_CNPJ", columns={"cpf_cnpj"})
 *   }
 * )
 * @ORM\Entity
 */
class Pessoa extends AbstractModel
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="cpf_cnpj", type="string", length=14, nullable=false)
     */
    protected $cpf_cnpj = '';

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=255, nullable=false)
     */
    protected $nome = '';

    /**
     * @var \Paliari\Brasil\DateTime\DateTimeBr
     *
     * @ORM\Column(name="created_at", type="db_datetime", nullable=false)
     */
    protected $created_at;

    /**
     * @var \Paliari\Brasil\DateTime\DateTimeBr
     *
     * @ORM\Column(name="updated_at", type="db_datetime", nullable=false)
     */
    protected $updated_at;

}

````

### Repository

Repository/PessoaRepository.php

````php

<?php

namespace Repository;

use Paliari\PhpSetup\Db\AbstractRepository;
use Pessoa;

class PessoaRepository extends AbstractRepository
{

    protected static function modelName(): string
    {
        return Pessoa::className();
    }

}

````

### AppSetup

config/AppSetup.php

````php
<?php

namespace Config;

use Paliari\PhpSetup\Config\AppSetupInterface;
use Config\Providers\Registers;
use Db\Session\SessionHandler;
use Config\Routes\Router;
use Slim\App;

class AppSetup implements AppSetupInterface
{

    /**
     * @var App
     */
    protected static $_app;

    /**
     * @return App
     */
    public static function app(): App
    {
        if (!static::$_app) {
            static::$_app = new App(Settings::get());
            static::setup(static::$_app);
        }

        return static::$_app;
    }

    protected static function setup(App $app): void
    {
        define('ROOT_APP', dirname(__DIR__));
        Database::register($app->getContainer());
        Registers::register($app->getContainer());
        Router::register($app);
    }

}

````

### Providers

config/Providers/AuthProvider.php

````php
<?php

namespace Config\Providers;

use Auth\Auth;
use Psr\Container\ContainerInterface;
use Paliari\PhpSetup\Config\Providers\ProvidableInterface;

abstract class AuthProvider implements ProvidableInterface
{

    const NAME = 'auth';

    public static function register(ContainerInterface $container, string $name): void
    {
        $container[$name] = function () {
            return Auth::i();
        };
    }

}

````

config/Providers/Registers.php

````php
<?php

namespace Config\Providers;

use Paliari\PhpSetup\Config\Providers\RegistersInterface;
use Psr\Container\ContainerInterface;

abstract class Registers implements RegistersInterface
{

    public static function register(ContainerInterface $container): void
    {
        AuthProvider::register($container, AuthProvider::NAME);
        ViewProvider::register($container, ViewProvider::NAME);
        CsrfProvider::register($container, CsrfProvider::NAME);
        NotFoundProvider::register($container, NotFoundProvider::NAME);
        ErrorProvider::register($container, ErrorProvider::NAME);
        PhpErrorProvider::register($container, PhpErrorProvider::NAME);
    }

}

````

### Router

config/Routes/Router.php

````php
<?php

namespace Config\Routes;

use Paliari\PhpSetup\Config\Routes\RouterInterface;
use Middleware\AuthMiddleware;
use Middleware\CsrfMiddleware;
use Controllers\Home;
use Slim\App;

class Router implements RouterInterface
{

    public static function register(App $app): void
    {
        AuthRoute::routes($app);
        static::registerAuthMiddleware($app);
        static::registerGlobalMiddlewares($app);
    }

    private static function registerAuthMiddleware(App $app): void
    {
        $app->group('', function (App $app) {
            LogsRoute::routes($app);
            $app->get('/', Home::class . ':index');
        })
            ->add(new OwnerMiddleware($app->getContainer()))
            ->add(new AuthMiddleware($app->getContainer()))
        ;
    }

    private static function registerGlobalMiddlewares(App $app): void
    {
        $app->add(new CsrfMiddleware($app->getContainer()));
        $app->add($app->getContainer()->get('csrf'));
    }

}

````

config/Routes/AuthRoute.php

````php
<?php

namespace Config\Routes;

use Controllers\Auth\Auth;
use Middleware\GuestMiddleware;
use Paliari\PhpSetup\Config\Routes\RoutableInterface;
use Slim\App;

class AuthRoute implements RoutableInterface
{

    public static function routes(App $app): void
    {
        $app->group('/auth', function (App $app) {
            $app->group('/', function (App $app) {
                $app->get('login', Auth::class . ':login');
            })->add(new GuestMiddleware($app->getContainer()))
            ;
            $app->get('/home', Auth::class . ':home');
            $app->get('/logout', Auth::class . ':logout');
        });
    }

}

````

### Envs

config/Envs.php

````php
<?php
namespace Config;

use Paliari\Utils\Url,
    Paliari\Utils\A;

/**
 * Class Envs
 *
 * @property-read string APP_ENV
 * @property-read string BASE_URL
 * @property-read string DB_DRIVER
 * @property-read string DB_HOST
 * @property-read string DB_PORT
 * @property-read string DB_NAME
 * @property-read string DB_USER
 * @property-read string DB_PWD
 */
class Envs
{

    protected static $_instance;

    /**
     * @return static
     */
    public static function i()
    {
        return static::$_instance = static::$_instance ?: new static();
    }

    public function __get($key)
    {
        return A::get($_ENV, $key);
    }

    public function host()
    {
        $u = Url::parse($this->BASE_URL);

        return "$u->scheme://$u->host";
    }

}

````
