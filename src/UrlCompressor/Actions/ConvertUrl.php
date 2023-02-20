<?php

namespace App\UrlCompressor\Actions;

use App\Entity\EncodedUrls;
use App\Entity\Users;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use InvalidArgumentException;
use App\UrlCompressor\Interfaces\ICheckUrl;
use App\UrlCompressor\Interfaces\IUrlDecoder;
use App\UrlCompressor\Interfaces\IUrlEncoder;
use Symfony\Bundle\SecurityBundle\Security;
use Throwable;

class ConvertUrl implements IUrlEncoder, IUrlDecoder
{
    /**
     * @param ICheckUrl $urlValidator
     * @param ManagerRegistry $doctrine
     */
    public function __construct(
        protected ICheckUrl $urlValidator,
        protected ManagerRegistry $doctrine,
        protected Security $security
    )
    {
    }

    /**
     * @param string $url
     * @return string
     */
    public function encode(string $url): string
    {
        if (!$this->urlValidator->CheckUrl($url))
            throw new InvalidArgumentException('Не доступний URL: ' . $url);
        $hash = md5($url);
        $code = substr($hash, 0, 6);
        $this->addCode($code, $url);
        return $code;
    }

    /**
     * @param string $code
     * @return string
     */
    public function decode(string $code): string
    {
        return $this->decodeAllData($code)->getUrl();
    }

    /**
     * @param string $code
     * @return string
     */
    public function decodeAndRedirect(string $code): string
    {
        $result = $this->decodeAllData($code);
        $result->fixRedirect();
        $entityManager = $this->doctrine->getManager();
        $entityManager->persist($result);
        $entityManager->flush();
        return $result->getUrl();
    }

    /**
     * @param string $code
     * @return EncodedUrls
     */
    public function decodeAllData(string $code): EncodedUrls
    {
        $result = $this->doctrine->getRepository(EncodedUrls::class)->findBy(['code' => $code], null, 1);
        if (empty($result))
            throw new InvalidArgumentException('Вказаний код ' . $code . ' URL-у відсутній в базі!');
        return $result[0];
    }

    /**
     * @return ObjectRepository
     */
    public function getAllData(): ObjectRepository
    {
        return $this->doctrine->getRepository(EncodedUrls::class);
    }

    public function getDataByUser(): array
    {
        $user = $this->security->getUser();
        return $this->doctrine->getRepository(EncodedUrls::class)->findBy(['user' => $user]);
    }

    /**
     * @param string $url
     * @return string|array
     */
    public function checkExistUrl(string $url): string|array
    {
        $result = $this->doctrine->getRepository(EncodedUrls::class)->findBy(['url' => $url], null, 1);
        if (!empty($result))
            $result = $result[0]->getCode();
        return $result;
    }

    /**
     * @param $code
     * @param $url
     * @return void
     */
    private function addCode($code, $url): void
    {
        $user = $this->security->getUser();
        $entityManager = $this->doctrine->getManager();
        $encodedUrl = new EncodedUrls($code, $url, $user);
        $entityManager->persist($encodedUrl);
        $entityManager->flush();
    }
}
