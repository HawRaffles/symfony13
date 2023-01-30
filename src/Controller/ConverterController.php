<?php

namespace App\Controller;

use App\Entity\EncodedUrls;
use App\UrlCompressor\Interfaces\IUrlDecoder;
use App\UrlCompressor\Interfaces\IUrlEncoder;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/url')]
class ConverterController
{
    /**
     * @param IUrlEncoder $encoder
     * @param IUrlDecoder $decoder
     */
    public function __construct(
        protected IUrlEncoder $encoder,
        protected IUrlDecoder $decoder
    )
    {
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/encode', methods: ['POST'])]
    public function getCode(Request $request): Response
    {
        $url = $request->request->get('url');
        $result = $this->encoder->checkExistUrl($url);
        if (empty($result)) {
            try {
                $result = $this->encoder->encode($url);
                $result = "Код закодованого url - $result";
            } catch (InvalidArgumentException) {
                $result = "Невалідний url $url";
            }
        }
        return new Response($result);
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/decode', methods: ['POST'])]
    public function getUrl(Request $request): Response
    {
        $code = $request->request->get('code');
        try {
            $result = $this->decoder->decode($code);
            $result = "Розкодований по коду $code url - $result";
        } catch (InvalidArgumentException) {
            $result = "Код $code не знайдено в базі данних ресурсу!";
        }
        return new Response($result);
    }
}