<?php 

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;



class AuthController extends AbstractController
{
    private MailerInterface $mailer;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/register', name: 'app_register', methods: ['GET','POST'])]
    public function register(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $name = $data['name'] ?? null;

        if (!$email || !$password || !$name) {
            return $this->json(['error' => 'Email, nom ou mot de passe est vide! '], 400);
        }

        // Vérifie si l'utilisateur existe déjà
        $existingUser = $userRepository->findOneBy(['email' => $email]);
        if ($existingUser) {
            return $this->json(['error' => 'Cet email est déjà utilisé'], 400);
        }

        // Crée un nouvel utilisateur
        $user = new User();
        $user->setEmail($email);

        // Hash le mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Définit le nom de l'utilisateur
        $user->setName($name);

        // Par défaut, on peut donner le rôle USER
        $user->setRoles(['ROLE_USER']);

        $user->setIsVerified(false); // Par défaut, l'utilisateur n'est pas vérifié

        // Génération du token de confirmation
        $token = bin2hex(random_bytes(32));
        $user->setConfirmationToken($token);

        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);

        // Enregistre l'utilisateur en base de données
        $em->persist($user);
        $em->flush();
        // Création de l'URL de confirmation
        $confirmationUrl = $urlGenerator->generate('app_verify_email', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        // Création et envoi de l'email
        $emailMessage = (new Email())
            ->from('noreply@tondomaine.com')
            ->to($user->getEmail())
            ->subject('Confirme ton adresse email')
            ->html("<p>Bonjour " . htmlspecialchars($user->getName()) . ",</p>
                    <p>Merci de confirmer ton adresse email en cliquant sur le lien suivant :</p>
                    <p><a href='$confirmationUrl'>Confirmer mon email</a></p>");

        $mailer->send($emailMessage);

        return $this->json([
            'message' => 'Utilisateur créé avec succès. Un email de confirmation a été envoyé.',
            'email' => $user->getEmail(),
        ], 201);
    }

    #[Route('/verify-email', name: 'app_verify_email', methods: ['GET'])]
    public function verifyEmail(Request $request, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $token = $request->query->get('token');

        if (!$token) {
            return $this->json(['error' => 'Token manquant'], 400);
        }

        $user = $userRepository->findOneBy(['confirmationToken' => $token]);

        if (!$user) {
            return $this->json(['error' => 'Token invalide'], 400);
        }

        $user->setIsVerified(true);
        $user->setConfirmationToken(null);

        $em->flush();

        return $this->json(['message' => 'Email vérifié avec succès. Tu peux maintenant te connecter.']);
    }

    
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return $this->json(['error' => 'Email et mot de passe requis'], 400);
        }

        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['error' => 'Identifiants invalides'], 401);
        }

        // Génère un nouveau token
        $token = bin2hex(random_bytes(32));
        $user->setApiToken($token);
        $em->flush();

        return $this->json([
            'token' => $token,
            'message' => 'Connexion réussie !'
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode peut rester vide
        throw new \Exception('Ne devrait jamais être appelée directement.');
    }

    #[Route('/forgot-password', name: 'forgot_password', methods: ['POST'])]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse(['message' => 'Si cet email existe, un lien a été envoyé.'], 200);
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = (new \DateTime())->modify('+1 hour');

        $user->setResetToken($token);
        $user->setResetTokenExpiresAt($expiresAt);
        $em->flush();

        $resetUrl = 'http://localhost:4200/reset-password?token=' . $token;

        $resetEmail  = (new Email())
        ->from('noreply@zenday.com')
        ->to($user->getEmail())
        ->subject('Réinitialisation de mot de passe')
        ->html('<p>Cliquez sur ce lien pour réinitialiser votre mot de passe : <a href="http://localhost:8000/reset-password/'.$user->getResetToken().'">Réinitialiser</a></p>');

        $this->mailer->send($resetEmail );

        return new JsonResponse(['message' => 'Si cet email existe, un lien a été envoyé.'], 200);
    }

    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (!$token || !$newPassword) {
            return new JsonResponse(['error' => 'Token ou mot de passe manquant.'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user || $user->getResetTokenExpiresAt() < new \DateTime()) {
            return new JsonResponse(['error' => 'Token invalide ou expiré.'], 400);
        }

        $user->setPassword($hasher->hashPassword($user, $newPassword));
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);
        $em->flush();

        return new JsonResponse(['message' => 'Mot de passe mis à jour.'], 200);
    }
}
