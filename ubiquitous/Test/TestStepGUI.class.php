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
class TestStepGUI extends TestStep implements iGUIHTML2 {
	function getHTML($id){
		$gui = new HTMLGUIX($this);
		$gui->name("Schritt");
		$this->loadMeOrEmpty();
		
		$fields = PMReflector::getAttributesArrayAnyObject($this->getA());
		$fields[] = "ID";
		$gui->attributes($fields);
		
		$gui->type("TestStepType", "select", array(
			"openTab" => "open tab", 
			"newEntry" => "create new entry", 
			"setValue" => "set value", 
			"setValues" => "set values", 
			"saveEntry" => "save entry", 
			"clickButton" => "click button", 
			"show" => "show", 
			"rme" => "rme", 
			"overlay" => "Overlay",
			"switchApplication" => "Switch Application",
			"logoff" => "Logoff",
			"login" => "Login"
		));
		$gui->type("TestStepTestID", "hidden");
		$gui->type("TestStepSort", "hidden");
		$gui->type("TestStepPresentationDuration", "hidden");
		$gui->type("TestStepValue", "textarea");
		$gui->type("TestStepDescription", "textarea");
		
		$gui->parser("TestStepAudioDuration", "TestStepGUI::parserAudioDuration");
		
		$gui->inputStyle("TestStepValue", "font-size:10px;height:150px;");
		$gui->inputStyle("TestStepDescription", "font-size:11px;height:150px;");
		
		$B = $gui->addFieldButton("TestStepClass", "Values laden", "./images/i2/insert.png");
		$B->rmePCR("TestStep", $this->getID(), "loadValues", array("$('editTestStepGUI').TestStepClass.value"), "$('editTestStepGUI').TestStepValue.value = transport.responseText;");
		
		$gui->parser("ID", "TestStepGUI::parserID");
		
		$gui->displayMode("popup");
		
		return $gui->getEditHTML();
	}
	
	public static function parserAudioDuration($w, $l, $E){
		$AC = anyC::get("TestStep", "TestStepTestID", $E->A("TestStepTestID"));
		$AC->addOrderV3("TestStepSort");
		$AC->addOrderV3("TestStepID");
		$i = 1;
		
		while($TS = $AC->getNextEntry()){
			if($TS->getID() == $E->getID())
				break;
			$i++;
		}
		
		$T = new Test($E->A("TestStepTestID"));
		
		$ID = new HTMLInput("TestStepAudioDuration", "text", $w);
		$ID->style("width:80%;");
		
		$IA = new HTMLInput("TestStepAudio", "audio", $T->A("TestAudioPath")."/A".str_pad($i, 3, "0", STR_PAD_LEFT).".ogg");
		$IA->style("width:97%;height:30px;");
		
		return "<span style=\"float:right;\">$i</span>".$ID.$IA;
	}
	
	public static function parserID($w, $l, $E){
		return $E->getID();
	}
	
	public function loadValues($class){
		$c = new $class(-1);
		$c->loadMeOrEmpty();
		
		$A = $c->getA();
		
		$r = "";
		foreach($A AS $k => $v)
			$r .= $k.":\n";
		
		echo trim($r);
	}
}
?>