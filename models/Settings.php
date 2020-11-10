<?php namespace Larabug\Larabug\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'larabug_larabug_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';
}