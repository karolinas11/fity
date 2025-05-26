<?php

namespace App\Services;

use App\Repositories\ScopeRepository;

class ScopeService
{

    protected ScopeRepository $scopeRepository;

    public function __construct() {
        $this->scopeRepository = new ScopeRepository();
    }

    public function addScope($scopeData) {
        return $this->scopeRepository->addScope($scopeData);
    }

    public function getUserScopes($userId) {
        return $this->scopeRepository->getUserScopes($userId);
    }

}
