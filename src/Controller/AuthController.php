<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthController extends AbstractController
{
    private MailerInterface $mailer;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/api/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $name = $data['name'] ?? null;

        if (!$email || !$password || !$name) {
            return $this->json(['error' => 'Email, nom ou mot de passe manquant.'], 400);
        }

        if ($userRepository->findOneBy(['email' => $email])) {
            return $this->json(['error' => 'Cet email est déjà utilisé.'], 400);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(false);
        $user->setConfirmationToken(bin2hex(random_bytes(32)));
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $em->persist($user);
        $em->flush();

        $confirmationUrl = $this->urlGenerator->generate(
            'app_verify_email',
            ['token' => $user->getConfirmationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $emailMessage = (new Email())
            ->from('noreply@zenday.com')
            ->to($email)
            ->subject('Confirme ton adresse email')
            ->html("
                <p>Bonjour " . htmlspecialchars($name) . ",</p>
                <p>Merci de confirmer ton adresse email en cliquant sur le lien suivant :</p>
                <p><a href=\"$confirmationUrl\">Confirmer mon email</a></p>
            ");

        $this->mailer->send($emailMessage);

        return $this->json([
            'message' => 'Utilisateur créé. Un email de confirmation a été envoyé.',
            'email' => $email,
        ], 201);
    }

    #[Route('/api/verify-email', name: 'app_verify_email', methods: ['GET'])]
    public function verifyEmail(Request $request, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $token = $request->query->get('token');

        if (!$token) {
            return $this->json(['error' => 'Token manquant.'], 400);
        }

        $user = $userRepository->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            return $this->json(['error' => 'Token invalide.'], 400);
        }

        $user->setIsVerified(true);
        $user->setConfirmationToken(null);
        $em->flush();

        return $this->json(['message' => 'Email vérifié avec succès. Vous pouvez maintenant vous connecter.']);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Cette méthode ne sera jamais exécutée car le firewall intercepte la requête
        // Lexik JWT gère automatiquement l'authentification et la génération du token
        throw new \RuntimeException('Cette méthode ne devrait pas être atteinte');
    }

    #[Route('/api/forgot-password', name: 'forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return $this->json(['message' => 'Si cet email existe, un lien a été envoyé.'], 200);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = (new \DateTime())->modify('+1 hour');
            $user->setResetToken($token);
            $user->setResetTokenExpiresAt($expiresAt);
            $em->flush();

            $resetUrl = 'http://localhost:4200/reset-password?token=' . $token;

            $resetEmail = (new Email())
                ->from('noreply@zenday.com')
                ->to($email)
                ->subject('Réinitialisation du mot de passe')
                ->html("<p>Cliquez sur ce lien pour réinitialiser votre mot de passe : <a href=\"$resetUrl\">Réinitialiser</a></p>");

            $this->mailer->send($resetEmail);
        }

        return $this->json(['message' => 'Si cet email existe, un lien a été envoyé.'], 200);
    }

    #[Route('/api/reset-password', name: 'reset_password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (!$token || !$newPassword) {
            return $this->json(['error' => 'Token ou mot de passe manquant.'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user || $user->getResetTokenExpiresAt() < new \DateTime()) {
            return $this->json(['error' => 'Token invalide ou expiré.'], 400);
        }

        $user->setPassword($hasher->hashPassword($user, $newPassword));
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);
        $em->flush();

        return $this->json(['message' => 'Mot de passe mis à jour avec succès.'], 200);
    }

    #[Route('/api/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode ne doit pas être appelée directement.');
    }

    #[Route('/api/token/refresh', name: 'api_token_refresh', methods: ['POST'])]
    public function refreshToken(Request $request): JsonResponse
    {
        // Le token est automatiquement validé par le firewall
        // une logique de vérification supplémentaire
        
        return $this->json([
            'status' => 'success',
            'message' => 'Token rafraîchi avec succès'
        ]);
    }
}
