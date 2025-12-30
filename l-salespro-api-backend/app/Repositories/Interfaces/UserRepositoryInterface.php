<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface
{
    public function findByEmail(string $email);
    public function updatePassword($user, string $password): void;
}