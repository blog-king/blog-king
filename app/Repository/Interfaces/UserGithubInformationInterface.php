<?php

namespace App\Repository\Interfaces;

interface UserGithubInformationInterface
{
    /**
     * 根据用户github名字获取userId.
     */
    public function getUserIdByName(string $name): int;

    /**
     * 根据github的id 获取userId.
     */
    public function getUserIdById(int $id): int;
}
