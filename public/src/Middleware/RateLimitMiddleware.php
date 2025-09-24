<?php
namespace PsyNexus\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PsyNexus\Database\Connection;
class RateLimitMiddleware implements MiddlewareInterface
{
    protected $db;
    protected $limit = 5;
    protected $window = 60; // 1 Minute in Sekunden
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
        $endpoint = $request->getUri()->getPath();
        $stmt = $this->db->prepare("
            SELECT * FROM rate_limits 
            WHERE ip_address = ? AND endpoint = ? 
            AND first_request >= DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$ip, $endpoint, $this->window]);
        $requests = $stmt->fetchAll();
        if (count($requests) >= $this->limit) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                "error" => "Anfragelimit überschritten. Bitte versuche es in einer Minute erneut."
            ]));
            return $response->withStatus(429)->withHeader("Content-Type", "application/json");
        }
        $this->updateRateLimit($ip, $endpoint);
        return $handler->handle($request);
    }
    protected function updateRateLimit($ip, $endpoint)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM rate_limits 
            WHERE ip_address = ? AND endpoint = ?
        ");
        $stmt->execute([$ip, $endpoint]);
        $existing = $stmt->fetch();
        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE rate_limits 
                SET request_count = request_count + 1, last_request = NOW()
                WHERE ip_address = ? AND endpoint = ?
            ");
            $stmt->execute([$ip, $endpoint]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO rate_limits (ip_address, endpoint, request_count, first_request)
                VALUES (?, ?, 1, NOW())
            ");
            $stmt->execute([$ip, $endpoint]);
        }
    }
}


