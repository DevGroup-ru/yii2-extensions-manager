<?php

namespace testsHelper;

class CustomClient extends \Packagist\Api\Client
{
    public function get($filename)
    {
        return $this->respond($filename);
    }

    protected function request($filename)
    {
        return file_get_contents($filename);
    }
}