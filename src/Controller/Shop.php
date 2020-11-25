<?php

declare(strict_types=1);

namespace App\Controller;

use App\Tracking\IdentifyRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class Shop extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    /**
     * @Route("cart", name="cart")
     */
    public function cart(): Response
    {
        return $this->render('cart.html.twig');
    }

    /**
     * @Route("products", name="products")
     */
    public function product(): Response
    {
        return $this->render('product.html.twig');
    }

    /**
     * @Route("logo", name="logo")
     */
    public function svgLogo(Request $request, IdentifyRequest $identifier): Response
    {
        $id = $identifier->getOrCreateId($request);

        $response = $this->render('_logo.html.twig', [
            'userid' => $id->toString(),
        ]);
        $response->headers->set('Content-Type', 'image/svg+xml');
        $response->setImmutable();

        $response->setEtag($id->toString());

        return $response;
    }
}
