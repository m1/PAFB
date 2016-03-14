<?php

namespace App\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class TestController
{
	private function gs()
	{
		return substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 3)), 0, 3);
	}

	private function pg()
	{
		return getenv('DB_CONNECTION') === 'pgsql';
	}

	public function deleteAction(Request $request, Application $app)
	{
		try {
			$app['db']->beginTransaction();
			if ($this->pg()) {
				$delete = $app['db']->executeUpdate(
					"DELETE FROM test t
					WHERE t.id = (
					   SELECT id
					   FROM test
					   WHERE val = ?
					   ORDER BY id DESC
					   LIMIT  1	
					   )",
				 	[$request->get('val')]
				);
			} else {
				$delete = $app['db']->executeUpdate('DELETE FROM test WHERE val = ? ORDER BY id DESC LIMIT 1',
				    [$request->get('val')]
				);
			}

			$app['db']->commit();
			return $app->json(['deleted' => $delete]);

		} catch (Exception $e) {
			$app['db']->rollback();
			$app->abort(404);
		}
	}

	public function selectAction(Request $request, Application $app)
	{
		$select = $app['db']->executeQuery(
			'SELECT id FROM test WHERE val = ? LIMIT 1',
		    [$request->attributes->get('v')]
    	)->fetch();

    	return $app->json(['select_id' => $select['id']]);
	}

	public function updateAction(Request $request, Application $app)
	{

		try {
			$app['db']->beginTransaction();

			if ($this->pg()) {
				$update = $app['db']->executeUpdate(
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
					[$request->get('val'), $request->attributes->get('v')]
				);
			} else {
				$update = $app['db']->executeUpdate('UPDATE test SET val = ? WHERE val = ? ORDER BY id DESC LIMIT 1',
				    [$request->get('val'), $request->attributes->get('v')]
				);
			}

			$app['db']->commit();

			return $app->json(['updated' => $update]);
		} catch (Exception $e) {
			$app['db']->rollback();
			$app->abort(404);
		}
	}

	public function insertAction(Request $request, Application $app)
	{
		$insert = $app['db']->executeUpdate('INSERT INTO test(val) VALUES(?)',
		    [$request->get('val')]
		);

		return $app->json(['inserted' => $insert]);
	}

	public function indexAction(Request $request, Application $app)
	{
		try {
			$app['db']->beginTransaction();

			$insert = $app['db']->executeUpdate('INSERT INTO test(val) VALUES(?)',
			    [$this->gs()]
			);

			$json['inserted'] = $insert;

			$app['db']->commit();
		} catch (Exception $e) {
			$app['db']->rollback();
			$app->abort(404);
		}

		$select = $app['db']->fetchAssoc('SELECT id FROM test ORDER BY id ASC LIMIT 1');
    	$json['select_id'] = $select['id'];

    	try {
    		$app['db']->beginTransaction();

    		if ($this->pg()) {
    			$update = $app['db']->executeUpdate(
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
    			$update = $app['db']->executeUpdate('UPDATE test SET val = ? ORDER BY id DESC LIMIT 1',
    			    [$this->gs()]
    			);
    		}

    		$json['updated'] = $update;

    		$app['db']->commit();

    	} catch (Exception $e) {
    		$app['db']->rollback();
    		$app->abort(404);
    	}

		return 	$app->json($json);
	}
}