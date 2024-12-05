<?php

namespace App\Controller;

use App\Entity\POI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class POIController extends AbstractController
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }


    #[Route('/pois', name: 'create_poi', methods: ['POST'])]
    public function createPOI(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

        $poi = new POI();
        $poi->setName($body['name']);
        $poi->setType($body['type']);
        $poi->setDescription($body['description'] ?? null);
        $poi->setLatitude($body['latitude']);
        $poi->setLongitude($body['longitude']);
        $poi->setAltitude($body['altitude']);
        $poi->setLocation($body['location'] ?? null);
        $poi->setStatus($body['status'] ?? null);

        $entityManager->persist($poi);
        $entityManager->flush();

        return new JsonResponse(['status' => 'POI created!']);
    }

    #[Route('/pois/{id}', name: 'get_poi', methods: ['GET'])]
    public function getPOI(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $poi = $entityManager->getRepository(POI::class)->find($id);

        if (!$poi) {
            return new JsonResponse(['error' => 'POI not found!'], 404);
        }

        $data = $this->serializer->normalize($poi, null, ['groups' => 'poi']);

        return new JsonResponse($data);
    }

    #[Route('/pois', name: 'get_all_poi', methods: ['GET'])]
    public function getAllPOI(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);
        $limit = 30;
        $offset = ($page - 1) * $limit;

        $poi = $entityManager->getRepository(POI::class)
            ->createQueryBuilder('p')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $data = $this->serializer->normalize($poi, null, ['groups' => 'poi']);

        return new JsonResponse([
            'page' => $page,
            'limit' => $limit,
            'data' => $data,
        ]);
    }
}
