<?php

namespace App\Controller;

use App\UrlCompressor\Interfaces\IUrlDecoder;
use App\UrlCompressor\Interfaces\IUrlEncoder;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/url')]
class ConverterController extends AbstractController
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
    #[Route('/encode', name: 'url_encode', methods: ['POST'])]
    public function getCode(Request $request): Response
    {
        $url = $request->request->get('url');
        $result = $this->encoder->checkExistUrl($url);
        if (empty($result)) {
            try {
                $code = $this->encoder->encode($url);
                $result = new RedirectResponse($this->generateUrl('url_statistics', ['code' => $code]));
            } catch (InvalidArgumentException) {
                $result = new Response("Невалідний url $url");
            }
        }
        return $result;
    }

    /**
     * @param Request $request
     * @return Response
     */
    #[Route('/decode', name: 'url_decode', methods: ['POST'])]
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

    /**
     * @param string $code
     * @return Response
     */
    #[Route('/code/{code}/stat', name: 'url_statistics', requirements: ['code' => '\w{6}'], methods: ['GET'])]
    public function getEncodedUrlInfo(string $code): Response
    {
        $parameters = [];
        try {
            $result = $this->decoder->decodeAllData($code);
            $template = 'url_decode.html.twig';
            $parameters = [
                'id' => $result->getId(),
                'code' => $code,
                'url' => $result->getUrl(),
                'redirects' => $result->getRedirects(),
                'create_date' => $result->getCreateDate()->format('Y-m-d H:i:s'),
                'redirect_date' => ($result->getRedirects() > 0) ? $result->getLastRedirectDate()
                    ->format('Y-m-d H:i:s') : '-'
            ];
        } catch (\Throwable $e) {
            $template = 'error.html.twig';
            $parameters = [
                'error' => $e
            ];
        }
        return $this->render($template, $parameters);
    }

    /**
     * @return Response
     */
    #[Route('/code/new', name: 'url_encode_form', methods: ['GET'])]
    public function newEncode(): Response
    {
        $template = 'url_encode.html.twig';
        $parameters = [
            'action' => $this->generateUrl('url_encode')
        ];
        return $this->render($template, $parameters);
    }

    /**
     * @param string $code
     * @return Response
     */
    #[Route('/code/{code}', name: 'get_url_and_redirect', requirements: ['code' => '\w{6}'], methods: ['GET'])]
    public function getUrlAndRedirect(string $code): Response
    {
        try {
            $result = $this->decoder->decodeAndRedirect($code);
            $response = new RedirectResponse($result);
        } catch (\Throwable $e) {
            $response = new Response($e->getMessage(), 400);
        }
        return $response;
    }

    /**
     * @return Response
     */
    #[Route('/{path}', requirements: ['path' => '.*'], methods: ['GET'])]
    public function defaultUrlInterface(): Response
    {
        $template = 'url_default.html.twig';
        return $this->render($template, ['message' => 'Сторінку управління url не знайдено'], new Response('', 404));
    }
}