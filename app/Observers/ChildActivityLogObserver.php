<?php
/**
 * busca-ativa-escolar-api
 * ChildActivityLogObserver.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 26/01/2017, 14:02
 */

namespace BuscaAtivaEscolar\Observers;


use BuscaAtivaEscolar\ActivityLog;
use BuscaAtivaEscolar\Child;

class ChildActivityLogObserver {

	public function created(Child $child) {
		ActivityLog::writeEntry($child, 'created', ['child_name' => $child->name, 'child' => $child, 'request' => request()->all()], ['source' => get_class()]);
	}
	public function updated(Child $child) {
        $case = $child->cases()->get()[0];
		$group = $case->group;
        $ids = $group->getArrayOfParentsId();
        array_push($ids, $group->id);
        $case->tree_id = implode(", ", $ids);
        $case->save();
	}

	public function deleted(Child $child) {
		ActivityLog::writeEntry($child, 'deleted', ['child_name' => $child->name, 'child' => $child, 'request' => request()->all()], ['source' => get_class()]);
	}

}