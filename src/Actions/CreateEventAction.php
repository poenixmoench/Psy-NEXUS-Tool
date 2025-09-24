<?php
namespace PsyNexus\Actions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PsyNexus\Utilities\Slugger;
use PsyNexus\Database\Connection;
class CreateEventAction
{
    protected $db;
    protected $slugger;
    public function __construct(Connection $db, Slugger $slugger)
    {
        $this->db = $db;
        $this->slugger = $slugger;
    }
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $errors = $this->validateData($data);
        if (!empty($errors)) {
            $response->getBody()->write(json_encode(['errors' => $errors]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $slug = $this->slugger->createSlug($data['event_name']);
        try {
            $stmt = $this->db->prepare("
                INSERT INTO events 
                (event_name, event_date, location, lineup, ticket_link, description, slug, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['event_name'],
                $data['date'],
                $data['location'],
                $data['lineup'],
                $data['ticketLink'] ?? null,
                $data['description'] ?? null,
                $slug,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            ]);
            $eventId = $this->db->lastInsertId();
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Event gespeichert',
                'url' => '/events/' . $slug,
                'id' => $eventId
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $response->getBody()->write(json_encode(['error' => 'Ein Datenbankfehler ist aufgetreten.']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    protected function validateData(array $data): array
    {
        $errors = [];
        if (empty($data['event_name'])) {
            $errors['event_name'] = 'Event-Name ist erforderlich';
        } elseif (strlen($data['event_name']) > 255) {
            $errors['event_name'] = 'Event-Name darf maximal 255 Zeichen lang sein';
        }
        if (empty($data['date'])) {
            $errors['date'] = 'Datum ist erforderlich';
        }
        if (empty($data['location'])) {
            $errors['location'] = 'Ort ist erforderlich';
        } elseif (strlen($data['location']) > 255) {
            $errors['location'] = 'Ort darf maximal 255 Zeichen lang sein';
        }
        if (empty($data['lineup'])) {
            $errors['lineup'] = 'Line-up ist erforderlich';
        }
        if (!empty($data['ticketLink']) && !filter_var($data['ticketLink'], FILTER_VALIDATE_URL)) {
            $errors['ticketLink'] = 'Ticket-Link ist keine gültige URL';
        }
        return $errors;
    }
}
