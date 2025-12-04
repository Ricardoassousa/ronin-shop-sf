<?php

namespace App\Controller;

use App\Entity\CustomerProfile;
use App\Form\CustomerProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerProfileController extends AbstractController
{
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You need to be logged in to access this page.');
        }

        $profile = $user->getCustomerProfile() ?? null;
        if ($profile == null) {
            $profile = new CustomerProfile();
            $profile->setUser($user);
        }

        $form = $this->createForm(CustomerProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($profile);
            $em->flush();

            $this->addFlash('success', 'Profile saved successfully!');

            return $this->redirectToRoute('app_customer_profile');
        }

        return $this->render('customer_profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}