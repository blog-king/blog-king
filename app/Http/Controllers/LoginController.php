<?php


namespace App\Http\Controllers;


use App\Models\Oauth;
use App\Repository\Repositories\UserGithubInformationRepository;
use App\Repository\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{

    /**
     * 第三方平台登录
     * @param Request $request
     * @return View | mixed
     */
    public function oauthRedirectToOtherPlatformProvider(Request $request)
    {
        $platform = $request->input("platform");

        if (Auth::check()) {
            return redirect(route('home'));
        }

        switch ($platform) {
            case Oauth::PLATFORM_GITHUB:
                return Socialite::driver('github')->redirect();
            default:
                return view("pages.login");
        }
    }


    /**
     *
     * github登录，第一次登陆则创建新用户
     * @param UserRepository $userRepository
     * @param UserGithubInformationRepository $githubInformationRepository
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function githubRedirectCallback(
        UserRepository $userRepository, UserGithubInformationRepository $githubInformationRepository
    )
    {
        $githubUser = Socialite::driver('github')->user();
        $githubId = $githubUser->getId();
        $userId = $githubInformationRepository->getUserIdById($githubId);
        if (0 === $userId) {
            $githubUserData = [
                'github_id' => $githubUser->getId(),
                'name' => $githubUser->getName(),
                'nickname' => $githubUser->getNickname(),
                'email' => $githubUser->getEmail(),
                'location' => ($githubUser->user)['location'] ?? null,
            ];
            try {
                $user = $userRepository->createUserByGithub($githubUserData);
                Auth::loginUsingId($user->id, true);
            } catch (\Exception $e) {
                abort(500, __("login.create_user_error"));
            }
        } else {
            Auth::loginUsingId($userId, true);
        }
        return redirect(route("home"));
    }
}
