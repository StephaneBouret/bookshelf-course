<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use App\Form\ResetPasswordRequestFormType;
use App\Service\SendMailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'security_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'security_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/forgot-password', name: 'security_forgotPw')]
    public function forgotPw(Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenGeneratorInterface, EntityManagerInterface $em, SendMailService $mail): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy([
                'email' => $form->get('email')->getData()
            ]);

            if ($user) {
                // On génère un token de réinitialisation
                $token = $tokenGeneratorInterface->generateToken();
                $now = new DateTimeImmutable();
                $user->setResetToken($token)
                    ->setCreatedTokenAt($now);
                $em->flush();

                $url = $this->generateUrl('security_resetPw', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                // On crée les données du mail
                $context = [
                    'url' => $url,
                    'user' => $user
                ];

                // Envoi du mail
                $mail->sendEmail(
                    'no-reply@mon-site.fr',
                    'Infos de la bibliothèque',
                    $user->getEmail(),
                    'Réinitilisation du mot de passe',
                    'password_reset',
                    $context
                );

                $this->addFlash('success', 'Email envoyé avec succès');
                return $this->redirectToRoute('security_login');
            }

            // $user est null
            $this->addFlash('danger', 'Un problème est sourvenu');
            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'formView' => $form
        ]);
    }

    #[Route('/forgot-password/{token}', name: 'security_resetPw')]
    public function resetPw($token, Request $request, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        // On vérifie si on a ce token dans la base
        $user = $userRepository->findOneBy([
            'resetToken' => $token
        ]);

        // On vérifie si le createdTokenAt = now - 3h
        $now = new DateTimeImmutable();
        if ($now > $user->getCreatedTokenAt()->modify('+ 3 hour')) {
            $this->addFlash('warning', 'Votre demande de mot de passe a expiré. Merci de la renouveller.');
            return $this->redirectToRoute('security_forgotPw');
        }

        // On vérifie si l'utilisateur existe
        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // On efface le token et sa date de création
            $user->setResetToken(null);
            $user->setCreatedTokenAt(null);

            // On enregistre le nouveau mot de passe en le hashant
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );
            $em->flush();

            $this->addFlash('success', 'Mot de passe changé avec succès');
            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'passForm' => $form->createView()
        ]);

        // Si le token est invalide on redirige vers le login
        $this->addFlash('danger', 'Jeton invalide');
        return $this->redirectToRoute('security_login');
    }
}
