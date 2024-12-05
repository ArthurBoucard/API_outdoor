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

        return new JsonResponse([
            'success' => true,
            'message' => 'POI created successfully!',
        ]);
    }

    #[Route('/pois/{id}', name: 'get_poi', methods: ['GET'])]
    public function getPOI(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $poi = $entityManager->getRepository(POI::class)->find($id);

        if (!$poi) {
            return new JsonResponse(['error' => 'POI not found!'], 404);
        }

        $data = $this->serializer->normalize($poi, null, ['groups' => 'poi']);

        return new JsonResponse([
            'success' => true,
            'message' => 'POI found!',
            'data' => $data,
        ]);
    }

    #[Route('/pois', name: 'get_all_poi', methods: ['GET'])]
    public function getAllPOI(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);
        $type = $request->query->get('type', null);
        $bbox = $request->query->get('bbox', null);
        $radius = $request->query->get('radius', null);
        $limit = 30;
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM poi WHERE 1=1";
        $params = [];

        if ($type) {
            $sql .= " AND type = :type";
            $params['type'] = $type;
        }

        if ($bbox) {
            $coords = explode(',', $bbox);

            if (count($coords) === 4) {
                $minLon = (float) $coords[0];
                $minLat = (float) $coords[1];
                $maxLon = (float) $coords[2];
                $maxLat = (float) $coords[3];

                $sql .= " AND longitude BETWEEN :minLon AND :maxLon AND latitude BETWEEN :minLat AND :maxLat";
                $params['minLon'] = $minLon;
                $params['maxLon'] = $maxLon;
                $params['minLat'] = $minLat;
                $params['maxLat'] = $maxLat;
            } else {
                return new JsonResponse(['error' => 'Invalid bbox format. Use minLon,minLat,maxLon,maxLat.'], 400);
            }
        }

        if ($radius) {
            [$lon, $lat, $distance] = explode(',', $radius);

            $sql .= " AND (6371 * acos(
                        cos(radians(:lat)) * cos(radians(latitude)) *
                        cos(radians(longitude) - radians(:lon)) +
                        sin(radians(:lat)) * sin(radians(latitude))
                    )) <= :distance";
            $params['lat'] = $lat;
            $params['lon'] = $lon;
            $params['distance'] = $distance;
        }

        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        try {
            $conn = $entityManager->getConnection();
            $result = $conn->executeQuery($sql, $params)->fetchAllAssociative();

            return new JsonResponse([
                'success' => true,
                'message' => 'POIs fetched successfully!',
                'page' => $page,
                'total' => count($result),
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while fetching data.', 'details' => $e->getMessage()], 500);
        }
    }
}
