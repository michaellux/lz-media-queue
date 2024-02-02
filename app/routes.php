<?php

declare(strict_types=1);

use App\Models\DB;
use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Views\PhpRenderer;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function ($request, $response, $args) {
        $renderer = new PhpRenderer(__DIR__ . '/../src/Views');
        return $renderer->render($response, "addperson.php", ["title" => "Добавление человека в очередь"]);
    });

    $app->post('/addperson', function ($request, $response, array $args) {
        $sql = "INSERT INTO persons (name, surname) VALUES (:value1, :value2)";
        $params = (array)$request->getParsedBody();
        $name = $params['name'];
        $surname = $params['surname'];
        try {
            $db = new DB();
            $conn = $db->connect();
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':value1', $name);
            $stmt->bindParam(':value2', $surname);

            // Execute the statement
            $stmt->execute();

            $db = null;
            return $response
                ->withHeader('Location', '/list')
                ->withStatus(302);
        } catch (PDOException $e) {
            $error = array(
                "message" => $e->getMessage()
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    });

    $app->get('/list', function (Request $request, Response $response) {
        $sql = "SELECT * FROM persons";

        try {
            $db = new DB();
            $conn = $db->connect();
            $stmt = $conn->query($sql);
            $persons = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            $response->getBody()->write(json_encode($persons));
            // Create a new mPDF instance
            $mpdf = new \Mpdf\Mpdf();

            // Convert the associative array to an HTML table
            $html = '<table>';
            foreach ($persons as $person) {
                $html .= '<tr>';
                foreach ($person as $field) {
                    $html .= '<td>' . htmlspecialchars((string)$field) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</table>';

            // Write the HTML to the PDF
            $mpdf->WriteHTML($html);

            // Output the PDF
            $mpdf->Output();
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (PDOException $e) {
            $error = array(
                "message" => $e->getMessage()
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    });
};
