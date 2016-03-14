<?php  

namespace App\Controller;
use Slim\Container;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class TestController
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    private function gs()
    {
        return substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 3)), 0, 3);
    }

    private function pg()
    {
        return getenv('DB_CONNECTION') === 'pgsql';
    }

    private function json($json, Response $response)
    {
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->write(json_encode($json));
        return $response;
    }

    public function insertAction(Request $request, Response $response, $args)
    {
        $val = $request->getParam("val");

        $insert = $this->container['db']->executeUpdate('INSERT INTO test(val) VALUES(?)',
            [$val]
        );

        return $this->json(['inserted' => $insert], $response);
    }

    public function deleteAction(Request $request, Response $response, $args)
    {
        try {
            $this->container['db']->beginTransaction();
            $val = $request->getParam("val");

            if ($this->pg()) {
                $delete = $this->container['db']->executeUpdate(
                    "DELETE FROM test t
                    WHERE t.id = (
                       SELECT id
                       FROM test
                       WHERE val = ?
                       ORDER BY id DESC
                       LIMIT  1 
                       )",
                    [$val]
                );
            } else {
                $delete = $this->container['db']->executeUpdate('DELETE FROM test WHERE val = ? ORDER BY id DESC LIMIT 1',
                    [$val]
                );
            }
            $this->container['db']->commit();

            return $this->json(['deleted' => $delete], $response);
        } catch (Exception $e) {
            $this->container['db']->rollback();
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'text/html')
                ->write('Page not found');
        }
    }

    public function selectAction(Request $request, Response $response, $args)
    {
        $val = $args['val'];

        $select = $this->container['db']->executeQuery(
            'SELECT id FROM test WHERE val = ? LIMIT 1',
            [$val]
        )->fetch();

        return $this->json(['select_id' => $select['id']], $response);
    }

    public function updateAction(Request $request, Response $response, $args)
    {
        try {
            $this->container['db']->beginTransaction();
            $update_val = $request->getParam("val");
            $val = $args['val'];

            if ($this->pg()) {
                $update = $this->container['db']->executeUpdate(
                    "UPDATE test t
                    SET val = ?
                    FROM (
                       SELECT id
                       FROM test
                       WHERE val = ?
                       ORDER BY id DESC
                       LIMIT  1
                       ) t2
                    WHERE  t.id = t2.id",
                    [$update_val, $val]
                );
            } else {
                $update = $this->container['db']->executeUpdate('UPDATE test SET val = ? WHERE val = ? ORDER BY id DESC LIMIT 1',
                    [$update_val, $val]
                );
            }

            $this->container['db']->commit();

            return $this->json(['updated' => $update], $response);
        } catch (Exception $e) {
            $this->container['db']->rollback();
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'text/html')
                ->write('Page not found');
        }
    }

    public function indexAction(Request $request, Response $response, $args)
    {
        try {
            $this->container['db']->beginTransaction();
            
            $insert = $this->container['db']->executeUpdate('INSERT INTO test(val) VALUES(?)',
                [$this->gs()]
            );

            $json['inserted'] = $insert;
            $this->container['db']->commit();
        } catch (Exception $e) {
            $this->container['db']->rollback();
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'text/html')
                ->write('Page not found');
        }

        $select = $this->container['db']->fetchAssoc('SELECT id FROM test ORDER BY id ASC LIMIT 1');
        $json['select_id'] = $select['id'];

        try {
            $this->container['db']->beginTransaction();

            if ($this->pg()) {
                $update = $this->container['db']->executeUpdate(
                    "UPDATE test t
                    SET val = ?
                    FROM (
                       SELECT id
                       FROM test
                       ORDER BY id DESC
                       LIMIT  1
                       ) t2
                    WHERE  t.id = t2.id",
                    [$this->gs()]
                );
            } else {
                $update = $this->container['db']->executeUpdate('UPDATE test SET val = ? ORDER BY id DESC LIMIT 1',
                    [$this->gs()]
                );
            }

            $json['updated'] = $update;
            $this->container['db']->commit();
        } catch (Exception $e) {
            $this->container['db']->rollback();
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'text/html')
                ->write('Page not found');
        }

        return $this->json($json, $response);
    }
}