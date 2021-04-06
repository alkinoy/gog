<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PingController extends AbstractController
{
    public function ping(): Response
    {
        return new Response('pong', Response::HTTP_OK);
    }
}