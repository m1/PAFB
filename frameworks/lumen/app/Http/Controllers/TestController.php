<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;

class TestController extends Controller
{

    private function gs()
    {
        return substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 3)), 0, 3);
    }

    private function pg()
    {
        return getenv('DB_CONNECTION') === 'pgsql';
    }

    public function selectAction($val)
    {
        $select = DB::table('test')->where('val', $val)->first();
        return response()->json(['select_id' => $select->id]);
    }

    public function updateAction($val, Request $request)
    {
        try {
            DB::beginTransaction();

            $update_val = $request->input('val');

            if ($this->pg()) {
                $updated = DB::update(
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
                    [$update_val, $val]);

            } else {
                $updated = DB::update('UPDATE test SET val = ? WHERE val = ? ORDER BY id DESC LIMIT 1', [$update_val, $val]);
            }

            DB::commit();

            return response()->json(['updated' => $updated]);
        } catch (Exception $e) {
            DB::rollBack();
            abort(404);
        }
    }

    public function deleteAction(Request $request)
    {
        try {
            DB::beginTransaction();

            $val = $request->input('val');

            if ($this->pg()) {
                $deleted = DB::delete(
                    "DELETE FROM test t
                    WHERE t.id = (
                       SELECT id
                       FROM test
                       WHERE val = ?
                       ORDER BY id DESC
                       LIMIT  1 
                       )", [$val]);

            } else {
                $deleted = DB::delete('DELETE FROM test WHERE val = ? ORDER BY id DESC LIMIT 1', [$val]);
            }

            DB::commit();

            return response()->json(['deleted' => $deleted]);
        } catch (Exception $e) {
            DB::rollBack();
            abort(404);
        }
    }

    public function insertAction(Request $request)
    {
        $val = $request->input('val');
        DB::table('test')->insert(['val' => $val]);
        return response()->json(['inserted' => 1]);
    }

    public function indexAction()
    {
        try {
            DB::beginTransaction();

            DB::table('test')->insert(['val' => $this->gs()]);
            $json['inserted'] = 1;
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            abort(404);
        }

        $select = DB::select('SELECT * FROM test ORDER BY id ASC LIMIT 1');
        $json['select_id'] = $select[0]->id;

        try {
            DB::beginTransaction();

            if ($this->pg()) {
                $updated = DB::update(
                    "UPDATE test t
                    SET val = ?
                    FROM (
                       SELECT id
                       FROM test
                       ORDER BY id DESC
                       LIMIT  1
                       ) t2
                    WHERE  t.id = t2.id", [$this->gs()]);

            } else {
                $updated = DB::update('UPDATE test SET val = ? ORDER BY id DESC LIMIT 1', [$this->gs()]);
            }

            $json['updated'] = $updated;
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            abort(404);
        }

        return response()->json($json);
    }
}
