<?php

namespace App\Repository\Interfaces;

use App\User;

interface UserInterface
{
    /**
     * 根据UserId 获取user.
     */
    public function getUserById(int $id): ?User;

    /**
     * github登录的用户创建.
     *
     * @throws \Exception
     */
    public function createUserByGithub(array $githubUserData): User;
}
