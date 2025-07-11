<?php

namespace App\Controller;

use App\Entity\Moment;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\HumeurRepository;
use App\Repository\MomentRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class MomentController extends AbstractController
{
    #[Route('api/moments/user', name: 'moments_by_user', methods: ['GET'])]
    public function getMomentsByUser(
        #[CurrentUser] ?User $user,
        MomentRepository $momentRepository
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $moments = $momentRepository->findBy(['user' => $user], ['createAt' => 'DESC']);

        return $this->json($moments, 200, [], ['groups' => 'moment:read']);
    }

    #[Route('api/moments/user/count', name: 'moments_count_by_user', methods: ['GET'])]
    public function getMomentsCountByUser(
        #[CurrentUser] ?User $user,
        MomentRepository $momentRepository
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $count = $momentRepository->count(['user' => $user]);

        return $this->json(['count' => $count], Response::HTTP_OK);
    }

    #[Route('api/moments/user/latest', name: 'latest_moment_by_user', methods: ['GET'])]
    public function getLatestMomentByUser(
        #[CurrentUser] ?User $user,
        MomentRepository $momentRepository
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $latestMoment = $momentRepository->findOneBy(['user' => $user], ['createAt' => 'DESC']);

        if (!$latestMoment) {
            return $this->json(['message' => 'No moments found for this user'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($latestMoment, 200, [], ['groups' => 'moment:read']);
    }

    #[Route('api/moments/user/humeur', name: 'moments_humeur_by_user', methods: ['GET'])]
    public function getMomentsHumeurByUser(
        #[CurrentUser] ?User $user,
        MomentRepository $momentRepository
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $moments = $momentRepository->findBy(['user' => $user], ['createAt' => 'DESC']);

        if (!$moments) {
            return $this->json(['message' => 'No moments found for this user'], Response::HTTP_NOT_FOUND);
        }

        $humeurs = [];
        foreach ($moments as $moment) {
            $humeurs[] = [
                'id' => $moment->getHumeur()->getId(),
                'name' => $moment->getHumeur()->getName(),
            ];
        }

        return $this->json($humeurs, 200, [], ['groups' => 'moment:read']);
    }

    #[Route('api/moments/user/tags', name: 'moments_tags_by_user', methods: ['GET'])]
    public function getMomentsTagsByUser(
        #[CurrentUser] ?User $user,
        MomentRepository $momentRepository
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $moments = $momentRepository->findBy(['user' => $user], ['createAt' => 'DESC']);
        
        if (!$moments) {
            return $this->json(['message' => 'No moments found for this user'], Response::HTTP_NOT_FOUND);
        }

        $tags = [];
        foreach ($moments as $moment) {
            foreach ($moment->getTags() as $tag) {
                $tags[] = [
                    'id' => $tag->getId(),
                    'name' => $tag->getname(),
                ];
            }
        }

        return $this->json(array_unique($tags, SORT_REGULAR), 200, [], ['groups' => 'moment:read']);
    }

    #[Route('api/moments/user/localisation', name: 'moments_localisation_by_user', methods: ['GET'])]
    public function getMomentsLocalisationByUser(
        #[CurrentUser] ?User $user,
        MomentRepository $momentRepository
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $moments = $momentRepository->findBy(['user' => $user], ['createAt' => 'DESC']);

        if (!$moments) {
            return $this->json(['message' => 'No moments found for this user'], Response::HTTP_NOT_FOUND);
        }

        $localisations = [];
        foreach ($moments as $moment) {
            if ($moment->getLocalisation()) {
                $localisations[] = $moment->getLocalisation();
            }
        }

        return $this->json(array_unique($localisations), 200, [], ['groups' => 'moment:read']);
    }

    #[Route('api/moments/user/latest/tags', name: 'latest_moment_tags_by_user', methods: ['GET'])]
    public function getLatestMomentTagsByUser(
        #[CurrentUser] ?User $user,
        MomentRepository $momentRepository
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $latestMoment = $momentRepository->findOneBy(['user' => $user], ['createAt' => 'DESC']);

        if (!$latestMoment) {
            return $this->json(['message' => 'No moments found for this user'], Response::HTTP_NOT_FOUND);
        }

        $tags = [];
        foreach ($latestMoment->getTags() as $tag) {
            $tags[] = [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
            ];
        }

        return $this->json($tags, 200, [], ['groups' => 'moment:read']);
    }

    #[Route('api/moments/user/latest/humeur', name: 'latest_moment_humeur_by_user', methods: ['GET'])]
    public function getLatestMomentHumeurByUser(
        #[CurrentUser] ?User $user,
        MomentRepository $momentRepository
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $latestMoment = $momentRepository->findOneBy(['user' => $user], ['createAt' => 'DESC']);

        if (!$latestMoment) {
            return $this->json(['message' => 'No moments found for this user'], Response::HTTP_NOT_FOUND);
        }

        $humeur = [
            'id' => $latestMoment->getHumeur()->getId(),
            'name' => $latestMoment->getHumeur()->getName(),
        ];

        return $this->json($humeur, 200, [], ['groups' => 'moment:read']);
    }

    #[Route('api/moments/user/latest/localisation', name: 'latest_moment_localisation_by_user', methods: ['GET'])]
    public function getLatestMomentLocalisationByUser(
        #[CurrentUser] ?User $user,
        MomentRepository $momentRepository
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $latestMoment = $momentRepository->findOneBy(['user' => $user], ['createAt' => 'DESC']);

        if (!$latestMoment) {
            return $this->json(['message' => 'No moments found for this user'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($latestMoment->getLocalisation(), 200, [], ['groups' => 'moment:read']);
    }

    #[Route('api/moments/user/latest/titre', name: 'latest_moment_titre_by_user', methods: ['GET'])]
    public function getLatestMomentTitreByUser(
        #[CurrentUser] ?User $user,
        MomentRepository $momentRepository
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $latestMoment = $momentRepository->findOneBy(['user' => $user], ['createAt' => 'DESC']);

        if (!$latestMoment) {
            return $this->json(['message' => 'No moments found for this user'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($latestMoment->getTitre(), 200, [], ['groups' => 'moment:read']);
    }

    #[Route('api/moments', name: 'post_moment', methods: ['POST'])]
    public function postMoment(
        #[CurrentUser] ?User $user,
        Request $request,
        TagRepository $tagRepository,
        HumeurRepository $humeurRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        // Validation des données requises
        if (!isset($data['humeur'])) {
            return $this->json(['message' => 'Humeur is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Gestion des tags
            $tags = [];
            if (!empty($data['tags']) && is_array($data['tags'])) {
                foreach ($data['tags'] as $tagName) {
                    $tagName = trim($tagName);
                    if (empty($tagName)) continue;

                    $tag = $tagRepository->findOneBy(['name' => $tagName]);
                    if (!$tag) {
                        $tag = new Tag();
                        $tag->setName($tagName);
                        $entityManager->persist($tag);
                        // Flush pour avoir l'ID pour les nouveaux tags
                        $entityManager->flush();
                    }
                    $tags[] = $tag;
                }
            }

            // Récupération de l'humeur
            $humeur = $humeurRepository->findOneBy(['name' => $data['humeur']]);
            if (!$humeur) {
                return $this->json(['message' => 'Humeur not found'], Response::HTTP_NOT_FOUND);
            }

            // Création du moment
            $moment = new Moment();
            $moment->setTitre($data['titre'] ?? 'No Title');
            $moment->setDescription($data['description'] ?? null);
            $moment->setCreateAt(new \DateTimeImmutable());
            $moment->setLocalisation($data['localisation'] ?? null);
            $moment->setUser($user);
            $moment->setHumeur($humeur);

            // Ajout des tags au moment
            foreach ($tags as $tag) {
                $moment->addTag($tag);
            }

            $entityManager->persist($moment);
            $entityManager->flush();

            return $this->json($moment, Response::HTTP_CREATED, [], ['groups' => 'moment:read']);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Error creating moment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }    
    #[Route('api/moments/{id}', name: 'get_moment', methods: ['GET'])]
    public function getMoment(
        int $id,
        #[CurrentUser] ?User $user,
        MomentRepository $momentRepository
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $moment = $momentRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$moment) {
            return $this->json(['message' => 'Moment not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($moment, Response::HTTP_OK, [], ['groups' => 'moment:read']);
    }

    #[Route('api/moments/{id}', name: 'delete_moment', methods: ['DELETE'])]
    public function deleteMoment(
        int $id,
        #[CurrentUser] ?User $user,
        MomentRepository $momentRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $moment = $momentRepository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$moment) {
            return $this->json(['message' => 'Moment not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($moment);
        $entityManager->flush();

        return $this->json(['message' => 'Moment deleted successfully'], Response::HTTP_NO_CONTENT);
    }

    #[Route('api/moments/{id}', name: 'update_moment', methods: ['PUT'])]
    public function updateMoment(
        int $id,
        Request $request,
        #[CurrentUser] ?User $user,
        MomentRepository $momentRepository,
        EntityManagerInterface $entityManager,
        TagRepository $tagRepository,
        HumeurRepository $humeurRepository
    ): JsonResponse {
        if (!$user) {
            return $this->json(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $moment = $momentRepository->findOneBy(['id' => $id, 'user' => $user]);
        if (!$moment) {
            return $this->json(['message' => 'Moment not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['message' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        $moment->setTitre($data['titre'] ?? $moment->getTitre());
        $moment->setDescription($data['description'] ?? $moment->getDescription());
        $moment->setLocalisation($data['localisation'] ?? $moment->getLocalisation());

        if (!empty($data['createAt'])) {
            $createAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['createAt']);
            if ($createAt) {
                $moment->setCreateAt($createAt);
            } else {
                return $this->json(['message' => 'Invalid date format (expected: Y-m-d H:i:s)'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (!empty($data['humeur'])) {
            $humeur = $humeurRepository->findOneBy(['name' => $data['humeur']]);
            if (!$humeur) {
                return $this->json(['message' => 'Humeur not found'], Response::HTTP_NOT_FOUND);
            }
            $moment->setHumeur($humeur);
        }

        if (isset($data['tags']) && is_array($data['tags'])) {
            $moment->getTags()->clear();

            foreach ($data['tags'] as $tagName) {
                $tag = $tagRepository->findOneBy(['name' => $tagName]);
                if (!$tag) {
                    $tag = new Tag();
                    $tag->setName($tagName);
                    $entityManager->persist($tag);
                }
                $moment->addTag($tag);
            }
        }

        $entityManager->persist($moment);
        $entityManager->flush();

        return $this->json($moment, Response::HTTP_OK, [], ['groups' => 'moment:read']);
    }
}