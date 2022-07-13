<?php
/**
 * busca-ativa-escolar-api
 * GroupSettings.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 13/02/2017, 18:39
 */

namespace BuscaAtivaEscolar\Settings;

use BuscaAtivaEscolar\Data\SerializableObject;

class UserSettings extends SerializableObject {

	const NOTIFY_ON_ALL = 'all';
	const NOTIFY_ON_SYSTEM = 'system';
	const NOTIFY_NONE = 'none';

	const VALID_NOTIFICATION_CHANNELS = [self::NOTIFY_NONE, self::NOTIFY_ON_SYSTEM, self::NOTIFY_ON_ALL];

	public $notifications = [
		'assigned_to_me' => 'all',
		'assigned_to_group' => 'system',
		'all_cases' => 'none',
		'default' => 'all',
	];

	public function getNotificationChannels($relationship = 'default') {
		switch($this->notifications[$relationship]) {
			case self::NOTIFY_ON_ALL: return ['mail', 'database'];
			case self::NOTIFY_ON_SYSTEM: return ['database'];
			default: case self::NOTIFY_NONE: return [];
		}
	}

	/**
	 * Updates the settings with form data
	 * @param array $data
	 */
	public function update($data) {
		if(!isset($data['notifications'])) return;
		if(!is_array($data['notifications'])) throw new \InvalidArgumentException('invalid_format');

		foreach($data['notifications'] as $type => $channel) {
			if(!isset($this->notifications[$type])) throw new \InvalidArgumentException('invalid_notification_type');
			if(!in_array($channel, self::VALID_NOTIFICATION_CHANNELS)) throw new \InvalidArgumentException('invalid_notification_channel');

			$this->notifications[$type] = $channel;

		}
	}

}