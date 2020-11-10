<?php namespace Larabug\Larabug;

use App;
use Event;
use Config;
use BackendAuth;
use System\Classes\PluginBase;
use Illuminate\Auth\AuthManager;
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
}