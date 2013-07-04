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

//class mTestStepGUI extends propertyC implements iGUIHTMLMP2 {
class mTestStepGUI extends VideoGUI implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->setSingleOwner("Test", $id);
		$this->addOrderV3("TestStepSort");
		$this->addOrderV3("TestStepID");
		$B = new Button("Schritt\nanlegen", "./ubiquitous/Test/Test.png");
		$B->rmePCR("Test", $this->ownerClassID, "createStep", "", "$('contentScreen').update(transport.responseText);");
		
		$this->setButton($B);
		
		$this->displayMode("BrowserLeft");
		$this->allowEdit = true;
		$this->setName("Schritt");
		$this->showAttributes = array("TestStepType", "TestStepID", "TestStepClass", "TestStepEntry", "TestStepDescription");
		
		$this->addParser("TestStepType", "mTestStepGUI::parserType");
		$this->addParser("TestStepID", "mTestStepGUI::parserID");
//		$this->addParser("TestStepClass", "mTestStepGUI::parserClass");
		$this->addParser("TestStepEntry", "mTestStepGUI::parserEntry");
		$this->addParser("TestStepDescription", "mTestStepGUI::parserDescription");
		
		echo '<div>'.parent::getHTML($id, $page)."</div>";
	}
	
//	public static function parserDG($w, $E){
//		if($w != "openTab")
//			return false;
//		
//		return $E->A("TestStepClass");
//	}
	
	public static function parserType($w, $E){
		$B = new Button($w, "./ubiquitous/Test/images/$w.png", "icon");
		
		return $B;
	}
	
	public static function parserDescription($w, $E){
		if(empty($w))
			return "";
		
		$B = new Button($w, "./ubiquitous/Test/images/text_document_wrap.png", "icon");
		$B->style("margin-right: 5px;");
		return $B;
	}
	
	public static function parserEntry($w, $E) {
		if (empty($w))
			return "";
		
		$B = new Button($w, "./ubiquitous/Test/images/arrow_branch.png", "icon");
		$B->style("margin-right: 5px;");
		
		return $B;
//		return $B.$E->A("TestStepClass")." ".$w."".($E->A("TestStepType") != "overlay" ? "<br /><small style=\"color:grey;\">".$E->A("TestStepDescription")."</small>" : "<span style=\"color:grey;\">".$E->A("TestStepDescription")."</span>");
	}
	
	public static function parserID() {
		$B = new Button("Sortierung", "./images/i2/topdown.png", "icon");
		$B->style("margin-right: 5px;");
		$B->className("TestStepHandler");
		
		return $B;
	}
	
	public static function parserClass($w, $E) {
		if (empty($w))
			return "Browser Event";
		return $w;
	}

	public function saveOrder($newOrder){
		$newOrder = explode(";", $newOrder);
		
		foreach($newOrder AS $o => $id){
			$TS = new TestStep($id);
			$TS->changeA("TestStepSort", $o);
			$TS->saveMe();
		}
		
		Red::messageSaved();
	}
}
?>