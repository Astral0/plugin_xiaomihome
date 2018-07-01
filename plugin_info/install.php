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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
function xiaomihome_update() {
	foreach (eqLogic::byType('xiaomihome') as $xiaomihome) {
        if ($xiaomihome->getConfiguration('type') == 'aquara') {
            $xiaomihomeCmd = xiaomihomeCmd::byEqLogicIdAndLogicalId($xiaomihome->getId(),'refresh');
            if (!is_object($xiaomihomeCmd)) {
                log::add('xiaomihome', 'debug', 'Création de la commande Rafraichir Aqara');
                $xiaomihomeCmd = new xiaomihomeCmd();
                $xiaomihomeCmd->setName(__('Rafraichir', __FILE__));
                $xiaomihomeCmd->setEqLogic_id($xiaomihome->getId());
                $xiaomihomeCmd->setEqType('xiaomihome');
                $xiaomihomeCmd->setLogicalId('refresh');
                $xiaomihomeCmd->setType('action');
                $xiaomihomeCmd->setSubType('other');
                $xiaomihomeCmd->setConfiguration('switch', 'read');
                $xiaomihomeCmd->setIsVisible('0');
                $xiaomihomeCmd->setDisplay('generic_type', 'DONT');
                $xiaomihomeCmd->save();
            }
        }
        $xiaomihome->setConfiguration('applyDevice',$xiaomihome->getConfiguration('model'));
        $xiaomihome->save();
	}
}
?>
