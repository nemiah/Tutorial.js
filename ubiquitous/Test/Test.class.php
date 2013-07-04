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
class Test extends PersistentObject {
	function __construct($ID) {
		parent::__construct($ID);
		
		$this->customize();
	}
	public function compile(){
		$AC = anyC::get("TestStep", "TestStepTestID", $this->getID());
		$AC->addOrderV3("TestStepSort");
		$AC->addOrderV3("TestStepID");
		$AC->lCV3();

		$js = "{
	id: ".$this->getID().",
	audioPath: '".$this->A("TestAudioPath")."',
	requests: 0";
		$i = 0;
		while($S = $AC->getNextEntry()){
			$M = $S->generateTestMethod();
			if($M != ""){
				$js .= ",
				".$M;
		
				$i++;
			}
		}
			$js .= ",
	count: ".TestStep::$counter[$this->getID()].",
	audio: ".json_encode(TestStep::$step2Audio[$this->getID()])."
			}";
		
		#print_r(TestStep::$step2Audio[$this->getID()]);
		return $js;
	}
	
	function newAttributes() {
		$A = parent::newAttributes();
		
		$A->TestApplication = Applications::activeApplication();
		
		return $A;
	}
	
	public function deleteMe() {
		$AC = anyC::get("TestStep", "TestStepTestID", $this->getID());
		while($TS = $AC->getNextEntry())
			$TS->deleteMe();
		
		parent::deleteMe();
	}
	
	public function newMe($checkUserData = true, $output = false) {
		$id = parent::newMe($checkUserData, false);
		
		$AC = anyC::get("TestStep", "TestStepTestID", "-1");
		while($TS = $AC->getNextEntry()){
			$TS->changeA("TestStepTestID", $id);
			$TS->saveMe();
		}
	}
	
	public function createStep(){
		$AC = anyC::get("TestStep", "TestStepTestID", $this->getID());
		$AC->addOrderV3("TestStepSort");
		
		$count = 0;
		while ($step = $AC->getNextEntry())
			$count++;
		
		$F = new Factory("TestStep");
		$F->sA("TestStepTestID", $this->getID());
		$F->sA("TestStepSort", $count++);
		$F->store();
		
		$Steps = new mTestStepGUI();
		$Steps->setSingleOwner("Test", $this->getID());
		
		echo $Steps->getHTML($this->getID(), 0);
	}
	
	public function getLastID($plugin){
		$AC = anyC::get($plugin);
		$AC->addOrderV3($plugin."ID", "DESC");
		$AC->setLimitV3("1");
		
		$E = $AC->getNextEntry();
		if($E != null)
			echo $E->getID();
		else
			echo -1;
	}
}
?>