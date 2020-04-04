<?php

namespace App\Repository\Repositories;

use App\Models\UserGithubInformation;
use App\Repository\Interfaces\UserInterface;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserRepository implements UserInterface
{
    /**
     * 根据UserId 获取user 类.
     *
     * @param int $id
     *
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        $user = User::query()->where('id', '=', $id)->first();
        if ($user instanceof User) {
            return $user;
        }

        return null;
    }

    /**
     * github登录的用户创建用户信息.
     *
     * @param array $githubUserData
     *                              eg：['name' => '名字'， "nickname" => "github昵称"， "email" => "github邮箱", "github_id" =>'', 'location'=>""]
     *
     * @return User
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function createUserByGithub(array $githubUserData): User
    {
        return Model::resolveConnection()->transaction(function () use ($githubUserData) {
            $salt = Str::random(16);
            $user = new User();
            $user->name = $githubUserData['name'];
            $user->nickname = $githubUserData['nickname'];
            $user->login_type = User::LOGIN_TYPE_GITHUB;
            $user->password = Hash::make($this->generateUserPassword($githubUserData['name'], $salt));
            $user->password_salt = $salt;
            $user->save();

            //保存github登录的信息
            $userGithubInformation = new UserGithubInformation();
            $userGithubInformation->fill($githubUserData);
            $userGithubInformation->user_id = $user->id;
            $userGithubInformation->save();
            return $user;
        });
    }

    /**
     * 生成用户密码的规则.
     *
     * @param string $name
     * @param string $salt
     *
     * @return string
     */
    private function generateUserPassword(string $name, string $salt): string
    {
        return 'kgin' . substr($name, 0, 3) . $salt;
    }
}
