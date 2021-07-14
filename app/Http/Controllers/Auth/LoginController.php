<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\User;

class LoginController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    /**
     * Where to redirect users after login.
     *
     * @var string

     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider = 'facebook')
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback(Request $request, $provider = 'facebook')
    {
        if ($request->query('error_code')) {
            return abort(404);
        }
        $providerUser = Socialite::driver($provider)->user();

        // Check Data From Provider
        // dd($providerUser);

        // GET : users
        $account = User::whereSocial($provider)->whereSocialId($providerUser->getId())->first();
        // dd($account);

        if ($account) {
            auth()->login($account);
            return redirect('/#');
        } else {
            $social_data = Socialite::driver($provider)->userFromToken($providerUser->token);
            $data_session = [
                'userDetail' => $social_data,
                'token' => $social_data->token,
                'social_id' => $social_data->id,
                'email' => $social_data->email,
                'provider' => $provider
            ];
            // return view('auth.register_social', $data_session);
            $request->session()->flash('fromSocial', $data_session);
            return redirect()->route('register');
        }

        //$user = $this->createOrGetUser($provider, $providerUser);
        //auth()->login($user);
        //return redirect('/#');
    }

    // NOT USED
    public function createOrGetUser($provider, $providerUser) {
        $account = User::whereSocial($provider)->whereSocialId($providerUser->getId())->first();

        if ($account) {
            return $account;
        } else {
            $userDetail = Socialite::driver($provider)->userFromToken($providerUser->token);

            /** Get email or not */
            $email = !empty($providerUser->getEmail()) ? $providerUser->getEmail() : $providerUser->getId() . '@' . $provider . '.com';

            /** Get User Auth */
            if (auth()->check()) {
                $user = auth()->user();
            } else {
                $user = User::whereEmail($email)->first();
            }

            if (!$user) {

                /** Create User */
                $name = explode(" ", $providerUser->getName());
                $user = User::create([
                    'first_name' => $name[0],
                    'last_name' => $name[1],
                    'email' => $email,
                    'social' => $provider,
                    'social_id' => $providerUser->getId(),
                    'password' => bcrypt(rand(1000, 9999)),
                ]);
                $user->save();
            }
            return $user;
        }
    }


    //    protected function authenticated(Request $request, $user)
    //     {
    //         // stuff to do after user logs in
    //       Cart::instance('cart')->restore($user->id);
    //       Cart::instance('wishlist')->restore($user->id);
    //     }

}
