<?php namespace Larabug\Larabug;

use App;
use Event;
use Config;
use BackendAuth;
use System\Classes\PluginBase;
use Larabug\Larabug\Models\Settings;
use Illuminate\Foundation\AliasLoader;
use Larabug\Larabug\Classes\Authenticatable;

class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'larabug.larabug::lang.plugin.name',
            'description' => 'larabug.larabug::lang.plugin.description',
            'author'      => 'Larabug',
            'icon'        => 'icon-bug',
            'homepage'    => 'https://github.com/LaraBug/oc-larabug-plugin',
        ];
    }

    public function register()
    {
        $this->app->bind('Illuminate\Contracts\Auth\Factory', function () {
            return BackendAuth::instance();
        });
    }

    /**
     * Runs right before the request route
     */
    public function boot()
    {
        App::before(function () {
            $lbKey = Settings::get('key');
            $lbProjectKey = Settings::get('project_key');
            $lbEnvironments = Settings::get('environments');

            // Check if logging config file exists
            if (!is_array(Config::get('logging.channels'))) {
                $this->prepareLoggingConfig();
            }

            if ($lbKey && $lbKey !== '') {
                Config::set('larabug.login_key', $lbKey);
            }

            if ($lbProjectKey && $lbProjectKey !== '') {
                Config::set('larabug.project_key', $lbProjectKey);
            }

            if ($lbEnvironments && $lbEnvironments !== '') {
                Config::set('larabug.environments', $lbEnvironments);
            }

            $loggingArray = array_merge(Config::get('logging.channels'), ['larabug' => ['driver' => 'larabug']]);
            Config::set('logging.channels', $loggingArray);

            $stack = Config::get('logging.channels.stack');
            $stack['channels'] = array_merge($stack['channels'], ['larabug']);
            Config::set('logging.channels.stack', $stack);
        });

        // Setup required packages
        $this->bootPackages();

        Event::listen('exception.report', function ($exception) {
            if (app()->bound('larabug')) {
                app('larabug')->handle($exception);
            }
        });
    }

    /**
     * Boots (configures and registers) any packages found within this plugin's packages.load configuration value
     *
     * @see https://luketowers.ca/blog/how-to-use-laravel-packages-in-october-plugins
     * @author Luke Towers <octobercms@luketowers.ca>
     */
    public function bootPackages()
    {
        // Get the namespace of the current plugin to use in accessing the Config of the plugin
        $pluginNamespace = str_replace('\\', '.', strtolower(__NAMESPACE__));

        // Instantiate the AliasLoader for any aliases that will be loaded
        $aliasLoader = AliasLoader::getInstance();

        // Get the packages to boot
        $packages = Config::get($pluginNamespace . '::config.packages');

        // Boot each package
        foreach ($packages as $name => $options) {
            // Setup the configuration for the package, pulling from this plugin's config
            if (!empty($options['config']) && !empty($options['config_namespace'])) {
                Config::set($options['config_namespace'], $options['config']);
            }

            // Register any Service Providers for the package
            if (!empty($options['providers'])) {
                foreach ($options['providers'] as $provider) {
                    App::register($provider);
                }
            }

            // Register any Aliases for the package
            if (!empty($options['aliases'])) {
                foreach ($options['aliases'] as $alias => $path) {
                    $aliasLoader->alias($alias, $path);
                }
            }
        }
    }

    public function prepareLoggingConfig()
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
                    'level' => 'debug',
                ],

                'daily' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/system.log'),
                    'level' => 'debug',
                    'days' => 14,
                ],

                'slack' => [
                    'driver' => 'slack',
                    'url' => env('LOG_SLACK_WEBHOOK_URL'),
                    'username' => 'October CMS Log',
                    'emoji' => ':boom:',
                    'level' => 'critical',
                ],

                'papertrail' => [
                    'driver' => 'monolog',
                    'level' => 'debug',
                    'handler' => \Monolog\Handler\SyslogUdpHandler::class,
                    'handler_with' => [
                        'host' => env('PAPERTRAIL_URL'),
                        'port' => env('PAPERTRAIL_PORT'),
                    ],
                ],

                'stderr' => [
                    'driver' => 'monolog',
                    'handler' => \Monolog\Handler\StreamHandler::class,
                    'formatter' => env('LOG_STDERR_FORMATTER'),
                    'with' => [
                        'stream' => 'php://stderr',
                    ],
                ],

                'syslog' => [
                    'driver' => 'syslog',
                    'level' => 'debug',
                ],

                'errorlog' => [
                    'driver' => 'errorlog',
                    'level' => 'debug',
                ],
            ],
        ];

        Config::set('logging', $loggingConfig);
    }

    public function registerSettings()
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
                'permissions' => []
            ]
        ];
    }
}