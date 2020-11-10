<p align="center">
    <a href="https://www.larabug.com" target="_blank"><img width="130" src="https://www.larabug.com/images/larabug-logo-small.png"></a>
</p>

# LaraBug

October CMS plugin

## Installation 

```
composer require larabug/oc-larabug-plugin
```

## Usage

All that is left to do is to define 3 ENV configuration variables.

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
