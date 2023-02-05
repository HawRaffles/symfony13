<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(name: 'encoded_urls')]
class EncodedUrls
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(length: 6)]
    private string $code;

    #[ORM\Column(length: 2048)]
    private string $url;

    #[ORM\Column(type: Types::INTEGER)]
    private int $redirects = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $createDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTime $lastRedirectDate;

    /**
     * @param string $code
     * @param string $url
     */
    public function __construct(string $code, string $url)
    {
        $this->code = $code;
        $this->url = $url;
        $this->setCreateDate();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return void
     */
    protected function setCreateDate(): void
    {
        $this->createDate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate(): \DateTime
    {
        return $this->createDate;
    }

    /**
     * @return void
     */
    public function setLastRedirectDate(): void
    {
        $this->lastRedirectDate = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getLastRedirectDate(): \DateTime
    {
        return $this->lastRedirectDate;
    }

    /**
     * @return int
     */
    public function getRedirects(): int
    {
        return $this->redirects;
    }

    /**
     * @return void
     */
    public function fixRedirect(): void
    {
        $this->setLastRedirectDate();
        $this->redirects++;
    }
}