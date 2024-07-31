<?php
namespace ModernPhpExample;

class ApiController {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getUsers(): void {
        $users = $this->db->getUsers();
        $this->jsonResponse($users);
    }

    public function addUser(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        
        if (empty($name) || empty($email)) {
            $this->jsonResponse(['error' => 'Name and email are required'], 400);
            return;
        }

        $id = $this->db->addUser($name, $email);
        $this->jsonResponse(['id' => $id, 'name' => $name, 'email' => $email], 201);
    }

    private function jsonResponse(array $data, int $statusCode = 200): void {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }
}
