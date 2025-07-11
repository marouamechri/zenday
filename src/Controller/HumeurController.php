<?php

namespace App\Controller;

use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\Humeur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;


#[Route('/api/humeur')]
final class HumeurController extends AbstractController
{
    
    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        $humeur = new Humeur();
        $humeur->setName($data['name'] ?? '');

        $errors = $validator->validate($humeur) ?? [];
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $em->persist($humeur);
        $em->flush();

        return $this->json($humeur, 201, [], ['groups' => 'humeur:read']);
    }
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        Humeur $humeur
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['name'])) {
            $humeur->setName($data['name']);
        }

        $errors = $validator->validate($humeur) ?? [];
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $em->flush();

        return $this->json($humeur, 200, [], ['groups' => 'humeur:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        EntityManagerInterface $em,
        Humeur $humeur
    ): JsonResponse {
        $em->remove($humeur);
        $em->flush();

        return $this->json(null, 204);
    }
    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(EntityManagerInterface $em): JsonResponse
    {
        $humeurs = $em->getRepository(Humeur::class)->findAll();

        return $this->json($humeurs, 200, [], ['groups' => 'humeur:read']);
    }
    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(Humeur $humeur): JsonResponse
    {
        return $this->json($humeur, 200, [], ['groups' => 'humeur:read']);
    }           
    #[Route('/humeur', name: 'get_collection', methods: ['GET'])]
    public function getCollection(EntityManagerInterface $em): JsonResponse
    {
        $humeurs = $em->getRepository(Humeur::class)->findAll();

        return $this->json($humeurs, 200, [], ['groups' => 'humeur:read']);
    }   
}
