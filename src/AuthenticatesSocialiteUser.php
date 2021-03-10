<?php

namespace CreatvStudio\SocialiteAuth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Laravel\Socialite\Facades\Socialite;
use CreatvStudio\SocialiteAuth\SocialiteUser;
use Illuminate\Validation\ValidationException;

trait AuthenticatesSocialiteUser
{
    protected $socialiteUser;

    /**
     * Redirects user to socialite provider
     *
     * @param Request $request
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request, $provider)
    {
        if ($this->isValidProvider($provider)) {
            return Socialite::driver($provider)->redirect();
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Handle a login request to the application.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function callback(Request $request, $provider)
    {
        if (! $this->isValidProvider($provider)) {
            return $this->sendFailedLoginResponse($request);
        }

        if (! $this->verifySocialiteUser($request)) {
            return $this->sendFailedLoginResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Verifies socialite user
     *
     * @param Request $request
     * @return void
     *
     * @throws \Laravel\Socialite\Two\InvalidStateException
     */
    protected function verifySocialiteUser(Request $request)
    {
        try {
            $this->socialiteUser = Socialite::driver($request->provider)->user();
            $this->socialiteUser->provider = $request->provider;

            return $this->socialiteUser ? true : false;
        } catch (InvalidStateException $e) {
            return false;
        }
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        if ($this->socialiteUser) {
            return $this->loginWithSocialite($request);
        }

        return false;
    }

    protected function loginWithSocialite($request)
    {
        $user = $this->retrieveUserFromSocialite();

        if (! $user && $this->socialiteUser->email) {
            $user = $this->guard()->getProvider()->retrieveByCredentials([
                'email'=> $this->socialiteUser->email
            ]);
        }

        // Register a new user if the developer
        // provides a create method.
        if (! $user && method_exists($this, 'create')) {
            event(new Registered($user = $this->create($this->socialiteUser)));
        }

        if ($user) {
            $this->associateSocialiteUser($user);
            $this->guard()->login($user);
        }

        return $this->guard()->check();
    }

    protected function retrieveUserFromSocialite()
    {
        $socialite = SocialiteUser::where('provider', $this->socialiteUser->provider)
            ->where('provider_id', $this->socialiteUser->id)
            ->first();

        return $socialite ? $this->guard()->getProvider()->retrieveById($socialite->user_id) : null;
    }

    protected function associateSocialiteUser($user)
    {
        $user->socialites()->updateOrCreate([
            'provider' => $this->socialiteUser->provider,
            'provider_id' => $this->socialiteUser->id,
        ], [
            'token' => $this->socialiteUser->token,
        ]);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->intended($this->redirectPath());
    }

    /**
     * Check if the provider is valid
     *
     * @param string $provider
     * @return boolean
     */
    protected function isValidProvider($provider)
    {
        return in_array($provider, $this->providers());
    }

    /**
     * Get the list of valid providers
     *
     * @return array
     */
    protected function providers()
    {
        return property_exists($this, 'providers') ? $this->providers : [];
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // throw ValidationException::withMessages([
        //     'email' => [trans('auth.failed')],
        // ]);

        return redirect('login');
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }

    /**
     * The user has been authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @param \Laravel\Socialite\Contracts\User $socialite
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
