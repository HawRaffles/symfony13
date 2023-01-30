<?php

namespace App\UrlCompressor\Helpers;

use InvalidArgumentException;
use App\UrlCompressor\Interfaces\ICheckUrl;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CheckUrl implements ICheckUrl
{
    /**
     * @param HttpClientInterface $httpClient
     * @param int $timeout
     * @param array $statusCodes
     * @param string $userAgent
     */
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected int $timeout,
        protected array $statusCodes,
        protected string $userAgent
    )
    {
    }

    /**
     * @param string $url
     * @return bool
     */
    public function CheckUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL))
            throw new InvalidArgumentException('Невалідний URL: ' . $url);
        return $this->GetRequest($url);
    }

    /**
     * @param string $url
     * @return bool
     */
    protected function GetRequest(string $url): bool
    {
        try {
            $requestResult = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'User-Agent' => $this->userAgent,
                ],
                'timeout' => $this->timeout
            ]);
            $responseCode = $requestResult->getStatusCode();
        } catch (TransportExceptionInterface) {
            $responseCode = 0;
        }
        return $this->ValidateResponse($responseCode);
    }

    /**
     * @param int $code
     * @return bool
     */
    protected function ValidateResponse(int $code): bool
    {
        $validity = false;
        if (in_array($code, $this->statusCodes))
            $validity = true;
        return $validity;
    }
}
