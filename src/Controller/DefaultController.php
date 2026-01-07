<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * DefaultController is used to manage basic routes and render views.
 * It handles the homepage and potentially other default pages of the application.
 */
class DefaultController extends AbstractController
{
    /**
     * Displays the homepage.
     *
     * This action renders the homepage template and passes a message to the view.
     * The message is displayed on the homepage and can be customized.
     *
     * @return Response
     */
    public function homepageAction(): Response
    {
        return $this->render('homepage.html.twig', []);
    }

}