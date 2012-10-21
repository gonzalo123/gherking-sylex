<?php

namespace Api;
use Symfony\Component\HttpFoundation\Request;

class Users
{
    public function listUsers()
    {
        return array('gonzalo', 'peter');
    }
}