<?php

namespace App\Repository\Interfaces;

use App\User;

interface UserInterface
{
    /**
     * 根据UserId 获取user.
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User;

    /**
     * github登录的用户创建.
     *
     * @param array $githubUserData
     * @return User
     */
    public function createUserByGithub(array $githubUserData): User;
}
