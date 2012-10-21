<?php

namespace Api;
use Symfony\Component\HttpFoundation\Request;

class User
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function info()
    {
        switch ($this->request->get('userName')) {
            case 'gonzalo':
                return array('name' => 'Gonzalo', 'surname' => 'Ayuso');
            case 'peter':
                return array('name' => 'Peter', 'surname' => 'Parker');
        }
    }

    public function update()
    {
        return array('infoUpdated');
    }
}