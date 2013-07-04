<?php

/**
 *  This file is part of ubiquitous.

 *  ubiquitous is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  ubiquitous is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class TestRmeTargetGUI {

	public function removeMahnungen() {
		$AC = anyC::get("GRLBM", "isM", "1");
		$AC->addAssocV3("nummer", ">", "1");

		while ($M = $AC->getNextEntry())
			$M->deleteMe();

		mUserdata::setUserdataS("pluginSpecificCanSetPayed", "Auftraege", "pSpec");
	}

	public function fixMenu($TestID) {
		$AC = anyC::get("TestStep", "TestStepTestID", $TestID);
		$AC->addAssocV3("TestSTepType", "=", "openTab");
		$MG = new MenuGUI();
		while ($TS = $AC->getNextEntry()) {
			$MG->showTab($TS->A("TestStepClass"));
			#$MG->toggleTab($TS->A("TestStepClass"), "small");
		}
	}

}

?>