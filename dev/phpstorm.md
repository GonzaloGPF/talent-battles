# PhpStorm
Configure PhpStorm integration with Laravel Sail

- PHP CLI:
    - Remote interpreter using Docker by specifying the `./docker-compose.yml` configuration file
    - The Service must be talentbattles.test
    - Also, add user and group to Environment Variables `WWWGROUP=sail;WWWUSER=sail`
    - Mark 'connect to existing container' option
- Test Framework
    - Use the same remote interpreter of Php CLI
    - Set the autoloader configuration path to `vendor/autoload.php`
    - In Test Runner section, add PhpUnit configuration file `phpunit.xml`
    - Add bootstrap configuration path `bootstrap/app.php` (it's not working)
- Run configuration
    - Add new template run configuration for PhpUnit
    - In Test Runner section, select 'Defined in configuration file'
    - In 'Use alternative configuration file': \\wsl$\Ubuntu-22.04\home\<user>\code\TalentBattles\phpunit.xml
    - In 'Use alternative bootstrap file': \\wsl$\Ubuntu-22.04\home\<user>\code\TalentBattles\bootstrap\app.php
    - Set the service as talentbattles.test
    - In Environment Variables, add `WWWGROUP=sail;WWWUSER=sail`
- NodeJS
    - Installing Node in WSL instead of using a Remote Interpreter. (go to WSL terminal
      and [install it](https://learn.microsoft.com/en-us/windows/dev-environment/javascript/nodejs-on-wsl) with **nvm**)
    - then, add Node in Settings > Languages and Frameworks > NodeJs

## IDE helper
To generate all PHP helper files just run:

`sail composer generate`

It will generate **_ide_helper.php**, **_ide_helper_models.php** and **.phpstorm.meta.php** files for you.

## Excluding folders

Avoid PhpStorm index unnecessary folders like:

- `storage/framework` and `storage/debugbar`
- `public/vendor`
