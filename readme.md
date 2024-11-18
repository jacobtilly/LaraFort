# LaraFort

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

This package provides a simple way to connect Laravel applications to Fortnox accounting software. It handles OAuth authentication, API requests, and environment management. Please refer to contributing.md for a detailed to-do list.

## Buy me a coffee?

If you use this package, please consider [buying me a coffee](https://buymeacoffee.com/jacobtilly) :)

## Installation

Via Composer

```bash
composer require jacobtilly/larafort
```

Then, run the install command to configure the connection to Fortnox. Please note that you need to create and set up a Fortnox integration in developer portal beforehand.

```bash
php artisan larafort:install
```

## Usage

After installation, use the following artisan commands to manage your Fortnox connection:

```bash
php artisan larafort:install
```

```bash
php artisan larafort:migrate
```

```bash
php artisan larafort:refresh-tokens
```

```bash
php artisan larafort:testconnection
```

```bash
php artisan larafort:env
```

```bash
php artisan larafort:stoptunnel
```

```bash
php artisan larafort:uninstall
```

For API interactions, use the LaraFort facade:

```php
use JacobTilly\LaraFort\Facades\LaraFort;

LaraFort::get('endpoint');
LaraFort::post('endpoint', $data);
LaraFort::put('endpoint', $data);
LaraFort::delete('endpoint');
```

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome! Submit an issue or a pull request.

## Responsibility note

I am not in any way affiliated with Fortnox, just a happy customer and Laravel programmer :)
This project was originally only intended for me to use in my projects, but I decided to publish it if it would be of good use for someone else. Please note, however, that I take no responsibility for basically anything related to the use of this package. Always look through the code of packages you decide to install and use.

## Security

If you discover any security related issues, please email dev@jacobtilly.com instead of using the issue tracker.

## License

MIT. Please see the [license file](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/jacobtilly/larafort.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/jacobtilly/larafort.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/jacobtilly/larafort
[link-downloads]: https://packagist.org/packages/jacobtilly/larafort
[link-author]: https://github.com/jacobtilly
