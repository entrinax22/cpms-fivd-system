<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    public function getEncryptedTeamId($id, $type = 'dev')
    {
        $cacheKey = "{$type}_team_{$id}_encrypted";
        
        return Cache::rememberForever($cacheKey, function () use ($id) {
            return Crypt::encrypt($id);
        });
    }

    public function getEncryptedDevelopmentTeamId($id)
    {
        return $this->getEncryptedTeamId($id, 'dev');
    }

    public function getEncryptedTestingTeamId($id)
    {
        return $this->getEncryptedTeamId($id, 'test');
    }

    public function getEncryptedUserId($id)
    {
        $cacheKey = "user_{$id}_encrypted";
        
        return Cache::rememberForever($cacheKey, function () use ($id) {
            return Crypt::encrypt($id);
        });
    }

    public function getEncryptedManagerId($id)
    {
        $cacheKey = "manager_{$id}_encrypted";
        
        return Cache::rememberForever($cacheKey, function () use ($id) {
            return Crypt::encrypt($id);
        });
    }

    public function decryptManagerId($encryptedId)
    {
        return Crypt::decrypt($encryptedId);
    }
}