<?php

namespace App\UrlCompressor\Interfaces;

use InvalidArgumentException;

interface ICheckUrl
{
    /**
     * @param string $url
     * @throws InvalidArgumentException
     * @return bool
     */
    public function CheckUrl(string $url): bool;
}
