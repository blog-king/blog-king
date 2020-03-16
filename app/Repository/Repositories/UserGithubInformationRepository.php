<?php

namespace App\Repository\Repositories;

use App\Models\UserGithubInformation;
use App\Repository\Interfaces\UserGithubInformationInterface;

class UserGithubInformationRepository implements UserGithubInformationInterface
{
    /**
     * 根据github的name获取是否已经使用过github登录.
     */
    public function getUserIdByName(string $name): int
    {
        $data = UserGithubInformation::query()
            ->select('user_id')
            ->where('name', '=', $name)
            ->first();

        if ($data instanceof UserGithubInformation) {
            return $data->user_id;
        }

        return 0;
    }

    /**
     * 根据github的id获取是否已经使用过github登录.
     */
    public function getUserIdById(int $id): int
    {
        $data = UserGithubInformation::query()
            ->select('user_id')
            ->where('github_id', '=', $id)
            ->first();

        if ($data instanceof UserGithubInformation) {
            return $data->user_id;
        }

        return 0;
    }
}
