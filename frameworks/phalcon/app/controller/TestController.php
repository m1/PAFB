<?php

namespace App\Controllers;

Class TestController extends \Phalcon\Mvc\Controller
{
    public function initialize()
    {
       $this->db = $this->getDi()->getShared('db');
    }   

    private function gs()
    {
        return substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 3)), 0, 3);
    }

    private function pg()
    {
        return getenv('DB_CONNECTION') === 'pgsql';
    }

    private function json($output)
    {
        $this->response->setContentType('application/json', 'utf-8');
        $this->response->setJsonContent($output);
        $this->response->send();
    }

    public function insertAction()
    {
        try {
            $this->db->begin();

            $val = $this->request->getPost("val");
            $this->db->execute("INSERT INTO test (val) VALUES (?)", [$val]);

            $this->db->commit();

            $this->json(['inserted' => $this->db->affectedRows()]);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->response->setStatusCode(404, "Not Found");
        }
    }

    public function deleteAction()
    {
        try {
            $this->db->begin();

            if ($this->pg()) {
                $this->db->execute(
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
                $this->db->execute("DELETE FROM test WHERE val = ? ORDER BY id DESC LIMIT 1", [$val]);
            }
            $this->db->commit();

            $this->json(['deleted' => $this->db->affectedRows()]);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->response->setStatusCode(404, "Not Found");
        }
        $val = $this->request->getPost("val");
    }

    public function selectAction($val)
    {
        $q = $this->db->query("SELECT * FROM test WHERE val = ? LIMIT 1", [$val])->fetch();
        $this->json(['select_id' => $q['id']]);
    }

    public function updateAction($val)
    {
        try {
            $this->db->begin();

            $update_val = $this->request->getPost("val");

            if ($this->pg()) {
                $this->db->execute(
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
                $this->db->execute("UPDATE test SET val=? WHERE val = ? ORDER BY id DESC LIMIT 1", [$update_val, $val]);
            }

            $this->db->commit();
            $this->json(['updated' => $this->db->affectedRows()]);

        } catch (Exception $e) {
            $this->db->rollback();
            $this->response->setStatusCode(404, "Not Found");
        }

    }

    public function indexAction()
    {
        try {
            $this->db->begin();

            $this->db->execute("INSERT INTO test (val) VALUES (?)", [$this->gs()]);

            $json['inserted'] = $this->db->affectedRows();

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            $this->response->setStatusCode(404, "Not Found");
        }

        $q = $this->db->query("SELECT * FROM test ORDER BY id ASC LIMIT 1")->fetch();
        $json['select_id'] = $q['id'];

        try {
            $this->db->begin();

            if ($this->pg()) {
                $this->db->execute(
                    "UPDATE test t
                    SET val = ?
                    FROM (
                       SELECT id
                       FROM test
                       ORDER BY id DESC
                       LIMIT  1
                       ) t2
                    WHERE  t.id = t2.id",
                    [$this->gs()]);
            } else {
                $this->db->execute("UPDATE test SET val=? ORDER BY id DESC LIMIT 1", [$this->gs()]);
            }

            $json['updated'] = $this->db->affectedRows();

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            $this->response->setStatusCode(404, "Not Found");
        }

        $this->json($json);
    }
}