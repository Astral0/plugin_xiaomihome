<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

if (!jeedom::apiAccess(init('apikey'), 'xiaomihome')) {
	echo __('Vous n\'êtes pas autorisé à effectuer cette action', __FILE__);
	die();
}

if (init('test') != '') {
	echo 'OK';
	die();
}
$result = json_decode(file_get_contents("php://input"), true);
if (!is_array($result)) {
	log::add('xiaomihome', 'debug', 'Format Invalide');
	die();
}

if (isset($result['devices'])) {
	foreach ($result['devices'] as $key => $datas) {
		$explode = explode('_',$key);
		$key = $explode[0];
		if ($key == 'aquara'){
			if (!($datas['cmd'] == 'heartbeat' || $datas['cmd'] == 'report' || $datas['cmd'] == 'read_ack')) {
				continue;
			}
			if (!isset($datas['sid'])) {
				continue;
			}
			$logical_id = $datas['sid'];
			if ($datas['model'] == 'gateway') {
				$logical_id = $datas['source'];
			}
			$xiaomihome=xiaomihome::byLogicalId($logical_id, 'xiaomihome');
			if (!is_object($xiaomihome)) {
				if ($datas['model'] == 'gateway') {
					//test si gateway qui a changé d'ip
					foreach (eqLogic::byType('xiaomihome') as $gateway) {
						if ($gateway->getConfiguration('sid') == $datas['sid']) {
							$gateway->setConfiguration('gateway',$datas['source']);
							$gateway->setLogicalId($datas['source']);
							$gateway->save();
							return;
						}
					}
				}
				$xiaomihome= xiaomihome::createFromDef($datas,$key);
				if (!is_object($xiaomihome)) {
					log::add('xiaomihome', 'debug', __('Aucun équipement trouvé pour : ', __FILE__) . secureXSS($datas['sid']));
					continue;
				}
				sleep(2);
				event::add('jeedom::alert', array(
					'level' => 'warning',
					'page' => 'xiaomihome',
					'message' => '',
				));
				event::add('xiaomihome::includeDevice', $xiaomihome->getId());
			}
			if (!$xiaomihome->getIsEnable()) {
				continue;
			}
			if ($xiaomihome->getConfiguration('gateway') != $datas['source'] && $datas['model'] != 'gateway') {
				$xiaomihome->setConfiguration('gateway',$datas['source']);
				$xiaomihome->save();
			}
			if ($datas['sid'] !== null && $datas['model'] !== null) {
				if (isset($datas['data'])) {
					$data = $datas['data'];
					foreach ($data as $key => $value) {
						if ($datas['cmd'] == 'heartbeat' && $key == 'status') {
							continue;
						}
						if ($datas['model'] == 'gateway'){
							xiaomihome::receiveAquaraData($datas['source'], $datas['model'], $key, $value);
						} else {
							xiaomihome::receiveAquaraData($datas['sid'], $datas['model'], $key, $value);
						}
					}
				}
				$xiaomihome->setStatus('lastCommunication',date('Y-m-d H:i:s'));
				$xiaomihome->save();
			}
		}
		elseif ($key == 'yeelight'){
			if (!isset($datas['capabilities']['id'])) {
				continue;
			}
			$logical_id = $datas['ip'];
			$xiaomihome=xiaomihome::byLogicalId($logical_id, 'xiaomihome');
			if (!is_object($xiaomihome)) {
				foreach (eqLogic::byType('xiaomihome') as $yeelight) {
					if ($yeelight->getConfiguration('gateway') == $datas['ip']) {
						$yeelight->setLogicalId($datas['ip']);
						$yeelight->save();
						return;
					}
				}
				if (!isset($datas['capabilities']['model'])) {
					continue;
				}
				$xiaomihome= xiaomihome::createFromDef($datas,$key);
				if (!is_object($xiaomihome)) {
					log::add('xiaomihome', 'debug', __('Aucun équipement trouvé pour : ', __FILE__) . secureXSS($datas['capabilities']['id']));
					continue;
				}
				sleep(2);
				event::add('jeedom::alert', array(
					'level' => 'warning',
					'page' => 'xiaomihome',
					'message' => '',
				));
				event::add('xiaomihome::includeDevice', $xiaomihome->getId());
			}
			if (!$xiaomihome->getIsEnable()) {
				continue;
			}
			if (isset($datas['capabilities'])) {
				$data = $datas['capabilities'];
				$power = ($data['power'] == 'off')? 0:1;
				$xiaomihome->checkAndUpdateCmd('status', $power);
				$xiaomihome->checkAndUpdateCmd('brightness', $data['bright']);
				if ($xiaomihome->getConfiguration('model') != 'mono' && $xiaomihome->getConfiguration('model') != 'ceiling') {
					$xiaomihome->checkAndUpdateCmd('color_mode', $data['color_mode']);
					$xiaomihome->checkAndUpdateCmd('rgb', '#' . str_pad(dechex($data['rgb']), 6, "0", STR_PAD_LEFT));
					$xiaomihome->checkAndUpdateCmd('hsv', $data['hue']);
					$xiaomihome->checkAndUpdateCmd('saturation', $data['sat']);
				}
				if ($xiaomihome->getConfiguration('model') != 'mono') {
					$xiaomihome->checkAndUpdateCmd('temperature', $data['ct']);
				}
				$xiaomihome->setConfiguration('ipwifi', $datas['ip']);
				$xiaomihome->setConfiguration('gateway', $datas['ip']);
				$xiaomihome->setStatus('lastCommunication',date('Y-m-d H:i:s'));
				$xiaomihome->save();
			}
		}
		elseif ($key == 'wifi'){
			if (isset($datas['notfound'])){
				$logical_id = $datas['ip'];
				$xiaomihome=xiaomihome::byLogicalId($logical_id, 'xiaomihome');
				event::add('xiaomihome::notfound', $xiaomihome->getId());
				continue;
			}
			if (isset($datas['found'])){
				$logical_id = $datas['ip'];
				$xiaomihome=xiaomihome::byLogicalId($logical_id, 'xiaomihome');
				$xiaomihome->setConfiguration('gateway',$datas['ip']);
				$xiaomihome->setConfiguration('sid',$datas['serial']);
				$xiaomihome->setConfiguration('short_id',$datas['devtype']);
				$xiaomihome->setStatus('lastCommunication',date('Y-m-d H:i:s'));
				$xiaomihome->setIsEnable(1);
				$xiaomihome->setIsVisible(1);
				if (!in_array($datas['model'], array('vacuum','philipsceiling'))){
					$xiaomihome->setConfiguration('password',$datas['token']);
				}
				$xiaomihome->save();
				event::add('xiaomihome::found', $xiaomihome->getId());
				$refreshcmd = xiaomihomeCmd::byEqLogicIdAndLogicalId($xiaomihome->getId(),'refresh');
				$refreshcmd->execCmd();
				continue;
			}
			if (!isset($datas['model']) || !isset($datas['ip'])) {
				continue;
			}
			$logical_id = $datas['ip'];
			$xiaomihome=xiaomihome::byLogicalId($logical_id, 'xiaomihome');
			if (!is_object($xiaomihome)) {
				continue;
			}
			if (!$xiaomihome->getIsEnable()) {
				continue;
			}
			log::add('xiaomihome', 'debug', 'Status ' . print_r($datas, true));
			foreach ($xiaomihome->getCmd('info') as $cmd) {
				$logicalId = $cmd->getLogicalId();
				if ($logicalId == '') {
					continue;
				}
				$path = explode('::', $logicalId);
				$value = $datas;
				foreach ($path as $key) {
					if (!isset($value[$key])) {
						continue (2);
					}
					$value = $value[$key];
					if (!is_array($value) && strpos($value, 'toggle') !== false && $cmd->getSubType() == 'binary') {
						$value = $cmd->execCmd();
						$value = ($value != 0) ? 0 : 1;
					}
				}
				if (!is_array($value)) {
					if ($cmd->getSubType() == 'numeric') {
						$value = round($value, 2);
					}
					$cmd->event($value);
				}
				if (strpos($logicalId,'battery') !== false) {
					$xiaomihome->batteryStatus($value);
				}
				$xiaomihome->setStatus('lastCommunication',date('Y-m-d H:i:s'));
				$xiaomihome->save();
			}
		}
	}
}

?>
