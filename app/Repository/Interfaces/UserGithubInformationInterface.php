<?php

namespace App\Repository\Interfaces;

interface UserGithubInformationInterface
{
    /**
     * 根据用户github名字获取userId.
     * @param string $name
     * @return int
     */
    public function getUserIdByName(string $name): int;

    /**
     * 根据github的id 获取userId.
     * @param int $id
     * @return int
     */
    public function getUserIdById(int $id): int;
}
