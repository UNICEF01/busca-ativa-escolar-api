<?php
/**
 * busca-ativa-escolar-api
 * RebuildGroupCausesMap.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 09/03/2017, 18:37
 */

namespace BuscaAtivaEscolar\Console\Commands;


use BuscaAtivaEscolar\Group;

class RebuildGroupCausesMap extends Command {

	protected $signature = 'maintenance:rebuild_group_causes_map';
	protected $description = 'Rebuilds the group causes map for all groups';

	public function handle() {
		$groups = Group::all();

		foreach($groups as $group) {
			$this->comment("Group {$group->id}... ");
			Group::updateCausesMap($group);
			echo "DONE!";
		}

		$this->info("All groups' maps rebuilt!");
	}

}