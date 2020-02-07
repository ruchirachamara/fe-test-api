<?php
use Firebase\JWT\JWT;
use Tuupola\Base62;
use Aws\S3\S3Client;  
use Aws\Exception\AwsException;

$container = $app->getContainer();

$container['db'] = function ($c) {
    try{
       $db = $c['settings']['db'];
       $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
       $pdo = new PDO("mysql:host=" . $db['servername'] . ";dbname=" . $db['dbname'], $db['username'], $db['password'], $options);
       return $pdo;
    }catch(\Exception $ex){
       return $ex->getMessage();
    }
};

// Register globally to app
$container['session'] = function ($c) {
    return new \SlimSession\Helper;
};

$app->group('/v1', function () use ($app, $container) {

    $app->group('/employees', function () use ($app, $container) {

        $app->post('/add', function ($request, $response, $args) {
            $con = $this->db;
            $data = $request->getParsedBody();
            $sql = "INSERT INTO `employees` (`first_name`, `last_name`, `reporting_person_id`, `created_at`) VALUES (:first_name, :last_name, :reporting_person_id, :created_at)";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':first_name' => $data['first_name'],                            
                ':last_name' => $data['last_name'],
                ':reporting_person_id' => $data['reporting_person_id'],
                ':created_at' => date('Y-m-d H:i:s')
            );
            $pre->execute($values);
            $userId = $con->lastInsertId();
            return $response->withJson(array('status' => 'employee_created'), 200);  
        }); 

        $app->get("/list",  function ($request, $response, $args) {
            $con = $this->db;
            $sql = "SELECT employee_id, first_name, last_name, created_at  FROM employees";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));            
            $pre->execute();
            $result = $pre->fetchAll();                
            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        });

        $app->get("/view",  function ($request, $response, $args) {
            $con = $this->db;
            $emp_id = $request->getParam('emp_id');   
            $sql = "SELECT employee_id, first_name, last_name, reporting_person_id, created_at FROM employees WHERE employee_id = :emp_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));            
            $values = array(':emp_id' => $emp_id);
            $pre->execute($values);
            $result = $pre->fetch();                
            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        });

        $app->get("/delete",  function ($request, $response, $args) {
            $con = $this->db;
            $emp_id = $request->getParam('emp_id');   
            $sql = "DELETE FROM employees WHERE employee_id = :emp_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));            
            $values = array(':emp_id' => $emp_id);
            $pre->execute($values);
            $result = $pre->fetch();                
            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        });

        $app->post("/update",  function ($request, $response, $args) {
            $con = $this->db;
            $data = $request->getParsedBody();
            $emp_id = $request->getParam('emp_id');   
            $sql = "UPDATE `employees` SET `first_name` = :first_name, `last_name` = :last_name, `reporting_person_id` = :reporting_person_id WHERE employee_id = :emp_id";
            $pre = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $values = array(
                ':first_name' => $data['first_name'],                            
                ':last_name' => $data['last_name'],
                ':reporting_person_id' => $data['reporting_person_id'],
                ':emp_id' => $emp_id
            );
            $pre->execute($values);
            $userId = $con->lastInsertId();
            return $response->withJson(array('status' => 'employee_updated'), 200);
        });
    }); 
});
