<?php

namespace App\Traits;

trait GenerateApiTokenTrait
{
    public function generateApiToken()
    {
        $token = $this->createToken('token');
        return $token->plainTextToken;
    }
}
