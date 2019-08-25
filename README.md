# php-setup

Setup basico para padronizar projeto

## Exemplos

### DbSetup

Config/DbSetup.php

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

### Model

db/models/Pessoa.php

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

### Settings

Config/Settings.php

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
                'database'               => [
                    'driver'        => getenv('DB_DRIVER'),
                    'host'          => getenv('DB_HOST'),
                    'port'          => getenv('DB_PORT'),
                    'dbname'        => getenv('DB_NAME'),
                    'user'          => getenv('DB_USER'),
                    'password'      => getenv('DB_PASSWORD'),
                    'service'       => true,
                    'charset'       => 'UTF8',
                    'driverOptions' => ['charset' => 'UTF8'],
                ],
            ],
        ];
    }

}

````

### Providers

Config/Providers/AuthProvider.php

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

Config/Providers/Registers.php

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

Config/Routes/Router.php

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

Config/Routes/AuthRoute.php

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


