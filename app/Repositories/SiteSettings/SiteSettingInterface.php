<?php

namespace App\Repositories\SiteSettings;

use App\Models\site_setting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface SiteSettingInterface
{
    public function getAllSettings(string $search = null , string $key = null , int $perPage = 10): LengthAwarePaginator;
    public function getSetting(string $key): ?site_setting;
    public function updateSetting(string $key, Request $req): bool;
    public function createSetting(Request $req): site_setting;
    public function deleteSetting(string $key): bool;
    public function findByKey(string $key): ?array;
    public function getSettings(): array;
}
