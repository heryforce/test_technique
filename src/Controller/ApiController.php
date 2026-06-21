<?php

namespace App\Controller;

use App\Service\Message\AlertMessage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    #[Route('/api/alerter', name: 'alerter', methods: ['GET', 'POST'])]
    public function alerter(Request $request, ManagerRegistry $doctrine, MessageBusInterface $bus)
    {
        $connection = $doctrine->getConnection();
        $inseeParameter = $request->query->get('insee');
        if (!$inseeParameter) {
            return new Response("No insee parameter submitted", 422);
        }
        $stmt = $connection->prepare("SELECT * FROM contact WHERE insee = :insee");
        $stmt->bindValue(':insee', $inseeParameter);
        $results = $stmt->executeQuery()->fetchAllAssociative();

        if ($results === []) {
            return new Response("This insee code does not exist in the database.", 404);
        }

        foreach ($results as $result) {
            $bus->dispatch(new AlertMessage("ALERTE MÉTÉO", $result['telephone']));
        }

        return new Response("The alert messages are being sent.", 200);
    }
}
