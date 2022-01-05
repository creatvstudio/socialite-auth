# Socialite Auth

[![Latest Version on Packagist](https://img.shields.io/packagist/v/creatvstudio/socialite-auth.svg?style=flat-square)](https://packagist.org/packages/creatvstudio/socialite-auth)
[![Total Downloads](https://img.shields.io/packagist/dt/creatvstudio/socialite-auth.svg?style=flat-square)](https://packagist.org/packages/creatvstudio/socialite-auth)
<!-- [![Build Status](https://img.shields.io/travis/creatvstudio/socialite-auth/master.svg?style=flat-square)](https://travis-ci.org/creatvstudio/socialite-auth) -->
<!-- [![Quality Score](https://img.shields.io/scrutinizer/g/creatvstudio/socialite-auth.svg?style=flat-square)](https://scrutinizer-ci.com/g/creatvstudio/socialite-auth) -->

This package provides easy laravel socialite authentication. By [CreatvStudio](https://creatvstudio.ph)

## Requirements

To get started use composer to install [Laravel Socialite](https://laravel.com/docs/socialite). See Laravel Socialite official [documentation](https://laravel.com/docs/socialite).

```bash
composer require laravel/socialite
```

## Installation

You can install the package via composer:

```bash
composer require creatvstudio/socialite-auth
```

## Configuration

Before using Socialite, you will also need to add credentials for the OAuth services your application utilizes. These credentials should be placed in your `config/services.php` configuration file, and should use the key `facebook`, `twitter`, `linkedin`, `google`, `github`, `gitlab` or `bitbucket`, depending on the providers your application requires. For example:

```php
'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('APP_URL') . '/login/facebook/callback',
],

'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => env('APP_URL') . '/login/github/callback',
],
```

## User Model

Use the `HasSocialite` trait in your `User` model.

``` php
use CreatvStudio\SocialiteAuth\HasSocialite;

class User extends Authenticatable
{
    use Notifiable, HasRolesAndAbilities, HasSocialite;

    ...
}
```

## Controller

Create a new controller to handle your requests

```bash
php artisan make:controller Auth/SocialiteController
```

Then use the `AuthenticatesSocialiteUser` trait. *(In the future we plan to create a stub)*

```
<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use CreatvStudio\SocialiteAuth\AuthenticatesSocialiteUser;

class SocialiteController extends Controller
{
    use AuthenticatesSocialiteUser;

    protected $redirectTo = '/home';

    protected $providers = [
        'facebook',
    ];

    /**
     * Creates a user if it does not exist.
     *
     * @param mixed $user
     * @return void
     */
    protected function create(SocialiteUser $user)
    {
        return User::create([
            'name' => $user->name,
            'email' => $user->email,
            'password' => Hash::make(Str::random()),
        ]);
    }
}
```

## Authenticated

Just like `Laravel Auth Controllers`, Socialite Auth provide an empty `authenticated(Request $request, $user)` method that may be overwritten if desired:

```bash
/**
 * The user has been authenticated.
 *
 * @param \Illuminate\Http\Request $request
 * @param mixed $user
 * @return mixed
 */
protected function authenticated(Request $request, $user)
{
    // Do anything here
}
```

## Routing

On your `routes/web.php` add the login routes.

``` php
Route::get('/login/{provider}', 'Auth\SocialiteController@login');
Route::get('/login/{provider}/callback', 'Auth\SocialiteController@callback');
```

## Guard Customization

You may also customize the "guard" that is used to authenticate and register users. To get started, define a guard method on your LoginController, RegisterController, and ResetPasswordController. The method should return a guard instance:

```php
use Illuminate\Support\Facades\Auth;

protected function guard()
{
    return Auth::guard('guard-name');
}
```

### Roadmap

- [ ] Add stub for `Auth/SocialiteController`
- [ ] Add Tests

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email jeff@creatvstudio.ph instead of using the issue tracker.

## Credits

- [Jeffrey Naval](https://github.com/creatvstudio)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
