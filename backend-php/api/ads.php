<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // Get all ads or single ad
    if (isset($_GET['id'])) {
        // Get single ad
        $query = "SELECT a.*, u.name as seller_name, u.phone as seller_phone 
                  FROM ads a 
                  LEFT JOIN users u ON a.user_id = u.id 
                  WHERE a.id = :id AND a.status = 'active'";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $_GET['id']);
        $stmt->execute();

        $ad = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ad) {
            // Increment views
            $update_query = "UPDATE ads SET views = views + 1 WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(":id", $_GET['id']);
            $update_stmt->execute();

            echo json_encode($ad);
        } else {
            http_response_code(404);
            echo json_encode(array("error" => "Anunț negăsit"));
        }
    } else {
        // Get all ads with filters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $offset = ($page - 1) * $limit;

        $query = "SELECT a.*, u.name as seller_name 
                  FROM ads a 
                  LEFT JOIN users u ON a.user_id = u.id 
                  WHERE a.status = 'active'";

        // Add filters
        if (isset($_GET['category'])) {
            $query .= " AND a.category = :category";
        }
        if (isset($_GET['priceMin'])) {
            $query .= " AND a.price >= :priceMin";
        }
        if (isset($_GET['priceMax'])) {
            $query .= " AND a.price <= :priceMax";
        }
        if (isset($_GET['search'])) {
            $query .= " AND (a.title LIKE :search OR a.description LIKE :search)";
        }

        $query .= " ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($query);

        if (isset($_GET['category'])) {
            $stmt->bindParam(":category", $_GET['category']);
        }
        if (isset($_GET['priceMin'])) {
            $stmt->bindParam(":priceMin", $_GET['priceMin']);
        }
        if (isset($_GET['priceMax'])) {
            $stmt->bindParam(":priceMax", $_GET['priceMax']);
        }
        if (isset($_GET['search'])) {
            $search_term = "%" . $_GET['search'] . "%";
            $stmt->bindParam(":search", $search_term);
        }

        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);

        $stmt->execute();

        $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array(
            "ads" => $ads,
            "page" => $page,
            "limit" => $limit
        ));
    }
}

elseif ($method == 'POST') {
    // Create new ad (requires authentication)
    // Add JWT verification here

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->title) && !empty($data->description) && !empty($data->price)) {
        $query = "INSERT INTO ads (user_id, title, description, category, price, currency, location_city, location_county, status, created_at) 
                  VALUES (:user_id, :title, :description, :category, :price, :currency, :location_city, :location_county, 'active', NOW())";

        $stmt = $db->prepare($query);

        $stmt->bindParam(":user_id", $data->user_id);
        $stmt->bindParam(":title", $data->title);
        $stmt->bindParam(":description", $data->description);
        $stmt->bindParam(":category", $data->category);
        $stmt->bindParam(":price", $data->price);
        $stmt->bindParam(":currency", $data->currency);
        $stmt->bindParam(":location_city", $data->location->city);
        $stmt->bindParam(":location_county", $data->location->county);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("message" => "Anunț creat cu succes", "id" => $db->lastInsertId()));
        } else {
            http_response_code(503);
            echo json_encode(array("error" => "Eroare la crearea anunțului"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("error" => "Date incomplete"));
    }
}

elseif ($method == 'DELETE') {
    // Delete ad
    if (isset($_GET['id'])) {
        $query = "UPDATE ads SET status = 'deleted' WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $_GET['id']);

        if ($stmt->execute()) {
            echo json_encode(array("message" => "Anunț șters cu succes"));
        } else {
            http_response_code(503);
            echo json_encode(array("error" => "Eroare la ștergerea anunțului"));
        }
    }
}
?>


