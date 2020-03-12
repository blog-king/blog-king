<?php


namespace App\Repository\Repositories;


use App\Models\UserGithubInformation;
use App\Repository\Interfaces\UserInterface;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserRepository implements UserInterface
{

    /**
     * 根据UserId 获取user 类
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        $user = User::query()->where("id", "=", $id)->first();
        if ($user instanceof User) {
            return $user;
        }
        return null;
    }


    /**
     * github登录的用户创建用户信息
     *
     * @param array $githubUserData
     *  eg：['name' => '名字'， "nickname" => "github昵称"， "email" => "github邮箱", "github_id" =>'', 'location'=>""]
     *
     * @return User
     * @throws \Exception
     */
    public function createUserByGithub(array $githubUserData): User
    {

        DB::beginTransaction();
        try {
            //创建新用户

            $salt = Str::random(16);
            $user = new User();
            $user->name = $githubUserData['name'];
            $user->login_type = User::LOGIN_TYPE_GITHUB;
            $user->password = Hash::make($this->generateUserPassword($githubUserData['name'], $salt));
            $user->password_salt = $salt;
            $user->save();

            //保存github登录的信息
            $userGithubInformation = new UserGithubInformation();
            $userGithubInformation->github_id = $githubUserData['github_id'];
            $userGithubInformation->user_id = $user->id;
            $userGithubInformation->name = $githubUserData['name'];
            $userGithubInformation->nickname = $githubUserData['nickname'];
            $userGithubInformation->email = $githubUserData['email'];
            $userGithubInformation->location = $githubUserData['location'];
            $userGithubInformation->save();

            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }

    /**
     * 生成用户密码的规则
     * @param string $name
     * @param string $salt
     * @return string
     */
    private function generateUserPassword(string $name, string $salt): string
    {
        return "kgin" . substr($name, 0, 3) . $salt ;
    }

}
