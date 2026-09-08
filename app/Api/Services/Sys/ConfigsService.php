<?php

namespace App\Api\Services\Sys;

class ConfigsService
{
    /**
     * 对外暴露的配置键名列表（硬编码，后续追加配置项在此添加）
     *
     * @var array<int, string>
     */
    protected array $configKeys = ['project_name'];

    /**
     * 获取对外暴露的系统配置键值对
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        $result = [];
        foreach ($this->configKeys as $key) {
            $result[$key] = sys_config($key, '');
        }
        return $result;
    }
}
