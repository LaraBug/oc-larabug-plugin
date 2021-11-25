<p align="center">
    <a href="https://www.larabug.com" target="_blank"><img width="130" src="https://www.larabug.com/images/larabug-logo-small.png"></a>
</p>

# LaraBug

October CMS plugin

## Installation 
This plugin can be installed by either installing it from the October CMS Market Place (soon) or installing it with composer.

### October CMS Market Place
Add the plugin to your project by the October CMS website or download & install it directly from the backend of your project. 

### Composer
Your October CMS project should have a `composer.json` file. If not, use the Market Place version.
```
composer require larabug/larabug-plugin
```

## Usage
The plugin comes with an easy settings page you can access from the backend. However, if you prefer to use the `.env` variables, that's an option too.  

### Settings page
Navigate to the settings page. You should see 'LaraBug' right at the bottom of the side menu.

### Using .env variables
```
LB_KEY=
LB_PROJECT_KEY=
LB_ENVIRONMENTS=production,development
```

`LB_KEY` is your profile key which authorises your account to the API.
`LB_PROJECT_KEY` is your project API key which you receive when creating a project.
`LB_ENVIRONMENTS` contains the environments you want to catch exceptions for.

Get these variables at [larabug.com](https://www.larabug.com)

## License
The larabug package is open source software licensed under the [license MIT](http://opensource.org/licenses/MIT)
