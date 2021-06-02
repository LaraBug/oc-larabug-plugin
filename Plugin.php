<?php namespace Larabug\Larabug;

use App;
use Event;
use BackendAuth;
use LaraBug\Facade;
use Monolog\Logger;
use LaraBug\LaraBug;
use System\Classes\PluginBase;
use LaraBug\Commands\TestCommand;
use System\Classes\PluginManager;
use LaraBug\Logger\LaraBugHandler;
use Larabug\Larabug\Models\Settings;
use RainLab\User\Classes\AuthManager;

class Plugin extends PluginBase
{
    public $elevated = true;

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'larabug.larabug::lang.plugin.name',
            'description' => 'larabug.larabug::lang.plugin.description',
            'author'      => 'Larabug',
            'icon'        => 'icon-bug',
            'homepage'    => 'https://github.com/LaraBug/oc-larabug-plugin',
        ];
    }

    public function register(): void
    {
        $this->handleAppEnvironment();

        $this->handleAuthFactory();

        $this->registerSingleton();

        $this->extendLogManager();
    }

    public function boot(): void
    {
        App::before(function () {
            $this->setInitialConfig();

            $settings = Settings::instance();

            if (!is_array(config('logging.channels'))) {
                $this->prepareLoggingChannels();
            }

            // Set key
            if (!empty($settings->key)) {
                config()->set('larabug.login_key', $settings->key);
            }

            // Set project
            if (!empty($settings->project_key)) {
                config()->set('larabug.project_key', $settings->project_key);
            }

            // Set environments
            if (!empty($settings->environments)) {
                config()->set('larabug.environments', $settings->environments);
            }

            // Set server
            if (!empty($settings->server)) {
                config()->set('larabug.server', $settings->server);
            }

            // Set sleep
            if (!empty($settings->sleep)) {
                config()->set('larabug.sleep', $settings->sleep);
            }

            // Add to logging config
            $loggingArray = array_merge(config('logging.channels'), ['larabug' => ['driver' => 'larabug']]);
            config()->set('logging.channels', $loggingArray);

            //
            $stack = config('logging.channels.stack');
            $stack['channels'] = array_merge($stack['channels'], ['larabug']);
            config()->set('logging.channels.stack', $stack);
        });

        //
        $this->registerFacade();

        //
        $this->registerEvents();

        //
        $this->registerCommands();
    }

    public function registerSettings(): array
    {
        return [
            'larabug' => [
                'label'       => 'LaraBug',
                'description' => 'Manage LaraBug settings.',
                'category'    => 'system::lang.system.categories.logs',
                'icon'        => 'icon-bug',
                'class'       => 'Larabug\Larabug\Models\Settings',
                'order'       => 1000,
                'keywords'    => 'larabug exception',
                'permissions' => ['larabug.larabug.access_settings'],
            ]
        ];
    }

    public function registerPermissions(): array
    {
        return [
            'larabug.larabug.access_settings' => [
                'label' => 'Access LaraBug settings',
                'tab'   => 'LaraBug',
                'order' => 200,
            ],
        ];
    }

    protected function handleAppEnvironment(): void
    {
        if (config('app.env')) {
            return;
        }

        config()->set('app.env', env('APP_ENV') ?? 'local');
    }

    protected function handleAuthFactory(): void
    {
        if (!app()->bound('Illuminate\Contracts\Auth\Factory')) {
            if (PluginManager::instance()->exists('RainLab.User')) {
                $authManager = app()->runningInBackend() ? BackendAuth::class : AuthManager::class;
            } else {
                $authManager = BackendAuth::class;
            }

            app()->bind('Illuminate\Contracts\Auth\Factory', function () use ($authManager) {
                return $authManager();
            });
        }
    }

    protected function registerSingleton(): void
    {
        $this->app->singleton('larabug', function ($app) {
            return new LaraBug(new \LaraBug\Http\Client(
                config('larabug.login_key', 'login_key'),
                config('larabug.project_key', 'project_key')
            ));
        });
    }

    protected function extendLogManager(): void
    {
        if (!$this->app['log'] instanceof \Illuminate\Log\LogManager) {
            return;
        }

        resolve('log')->extend('larabug', function () {
            $handler = new LaraBugHandler(resolve('larabug'));

            return new Logger('larabug', [$handler]);
        });
    }

    protected function prepareLoggingChannels(): void
    {
        $loggingConfig = [
            'default' => env('LOG_CHANNEL', 'single'),
            'channels' => [
                'stack' => [
                    'driver' => 'stack',
                    'channels' => ['daily'],
                    'ignore_exceptions' => false,
                ],
                'single' => [
                    'driver' => 'single',
                    'path' => storage_path('logs/system.log'),
                ],
                'daily' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/system.log'),
                    'level' => 'debug',
                    'days' => 14,
                ],
            ],
        ];

        config()->set('logging', $loggingConfig);
    }

    protected function registerFacade(): void
    {
        if (class_exists(\Illuminate\Foundation\AliasLoader::class)) {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('LaraBug', Facade::class);
        }
    }

    protected function registerEvents(): void
    {
        Event::listen('exception.report', function ($exception) {
            if (app()->bound('larabug')) {
                app('larabug')->handle($exception);
            }
        });
    }

    public function registerCommands(): void
    {
        $this->commands([
            TestCommand::class,
        ]);
    }

    public function setInitialConfig(): void
    {
        config()->set('larabug', config('larabug.larabug::larabug-config'));
    }
}
