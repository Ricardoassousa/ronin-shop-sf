<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller responsible for handling default pages of the application.
 *
 * This includes the homepage and can be extended to include other
 * general-purpose pages that do not fit into a specific domain.
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