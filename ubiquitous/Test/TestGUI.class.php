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
class TestGUI extends Test implements iGUIHTML2 {
	function getHTML($id) {
//		$status = (int)BPS::popProperty("TestGUI", "testSet", 0);
//		if ($status != 0)
//			return $this->getTestsetPopup($status);
		
		$gui = new HTMLGUIX($this);
		$gui->name("Test");
	
		$gui->type("TestDescription", "textarea");
		$gui->type("TestIsPresentation", "checkbox");
		$gui->type("TestIsActive", "checkbox");
		
		$gui->space("TestIsPresentation", "Präsentation");
		
		$gui->attributes(array(
			"TestName",
			"TestIsActive",
			"TestApplication",
			"TestIsPresentation",
			"TestIcon",
			"TestAudioPath",
			"TestDescription"
			));
		
		$gui->inputStyle("TestDescription", "font-size:10px;height:150px;");
		
		$B = $gui->addSideButton("Test\nausführen", "./ubiquitous/Test/runTest.png");
		$B->onclick("TestManager.startTest(".$this->getID().");");
		
		$BP = $gui->addSideButton("Präsentation\nstarten", "./ubiquitous/Test/images/slideshow.png");
		$BP->onclick("TestManager.startPresentation(".$this->getID().");");
		
		$BP = $gui->addSideButton("Audio\naktualisieren", "./ubiquitous/Test/images/audio.png");
		$BP->popup("", "Audio aktualisieren", "Test", $this->getID(), "updateAudio", "");
		
		$BP = $gui->addSideButton("Vertonen", "./ubiquitous/Test/images/theater.png");
		$BP->popup("", "Vertonen", "Test", $this->getID(), "makeAudio", "", "", "{width: 500, hPosition: 'center'}");
		
		$BP = $gui->addSideButton("Editieren", "./ubiquitous/Test/images/slideshow.png");
		$BP->rmePCR("mTestStep", $this->getID(), "getHtml", $this->getID(), "$('contentScreen').update(transport.responseText); TestManager.initVideoEdit();");
//		$BP->loadFrame("contentScreen", "mTestStepGUI");
//		$BP->popup("", "Vertonen", "Test", $this->getID(), "makeAudio", "", "", "{width: 500, hPosition: \'center\'}");
		
		
//		$Steps = new mTestStepGUI();
//		$Steps->setSingleOwner("Test", $this->getID());
		
//		return $gui->getEditHTML()."<div id=\"testSubframe\" style=\"margin-top:30px;\">".$Steps->getHTML(-1, 0)."</div>";
		return $gui->getEditHTML();
	}
	
	public function updateAudio(){
		$AC = anyC::get("TestStep", "TestStepTestID", $this->getID());
		$AC->addOrderV3("TestStepSort");
		$AC->addOrderV3("TestStepID");
		$i = 1;
		echo "<pre style=\"font-size:10px;max-height:400px;overflow:auto;\">";
		while($TS = $AC->getNextEntry()){
			
			$ogg = new Ogg("/home/nemiah/NetBeansProjects/test/Presentation/".basename($this->A("TestAudioPath"))."/A".str_pad($TS->getID(), 4, "0", STR_PAD_LEFT).".ogg");
			#if ($ogg->LastError) { echo $ogg->LastError."\n"; }
			#print_r($ogg->Streams["vorbis"]["duration"]);
			echo "Step $i: ".$ogg->Streams["vorbis"]["duration"]."\n";
			$duration = 0;
			if(isset($ogg->Streams["vorbis"]) AND isset($ogg->Streams["vorbis"]["duration"]))
				$duration = $ogg->Streams["vorbis"]["duration"];
			
			$TS->changeA("TestStepAudioDuration", $duration);
			$TS->saveMe();
			$i++;
		}
		echo "</pre>";
	}
	
	public function makeAudio(){
		$AC = anyC::get("TestStep", "TestStepTestID", $this->getID());
		$AC->addOrderV3("TestStepSort");
		$AC->addOrderV3("TestStepID");
		
		$BN = new Button("Weiter", "navigation");
		$BN->style("float:right;margin:5px;");
		$BN->onclick("if(\$j('#makeAudio div.currentAudio').next().length == 0) return; \$j('#makeAudio div.currentAudio').next().css('display', 'block').addClass('currentAudio');\$j('#makeAudio div.currentAudio:first').css('display', 'none').removeClass('currentAudio');");
		echo $BN;
		
		$BP = new Button("Zurück", "back");
		$BP->style("margin:5px;");
		$BP->onclick("if(\$j('#makeAudio div.currentAudio').prev().length == 0) return; \$j('#makeAudio div.currentAudio').prev().css('display', 'block').addClass('currentAudio');\$j('#makeAudio div.currentAudio:last').css('display', 'none').removeClass('currentAudio');");
		
		echo $BP;
		
		echo "<div id=\"makeAudio\">";
		$i = 0;
		while($TS = $AC->getNextEntry()){
			echo "<div id=\"makeAudioStep$i\" style=\"".($i > 0 ? "display:none;" : "")."\" class=\"".($i > 0 ? "" : "currentAudio")."\">";
			echo "<p style=\"font-size:30px;text-align:center;color:grey;\">".$TS->getID()."</p><p style=\"font-size:20px;\">".$TS->A("TestStepDescription")."</p>";
			echo "</div>";
			
			$i++;
		}
		echo "</div>";
	}
	
	public function getSavedTestsetsPopup() {
		$testsetCollection = anyC::get("TestSet");
		$testsetCollection->addOrderV3("TestSetID");
		$table = new HTMLTable(4);
		$table->addHeaderRow(array("Tests", "Schleifen", "", ""));
		while ($row = $testsetCollection->getNextEntry()) {
			$runButton = new Button("Set starten", "./ubiquitous/Test/images/arrow_right.png", "icon");
			$runButton->onclick("TestManager.startTestSet(null, null, null, null, true, '" . $row->A("TestIDs") . "', " . $row->A("TestLoops") . ");");
			$runButton->className("savedTestSetRun");
			$deleteButton = new Button("Set löschen", "./images/i2/delete.gif", "icon");
			$deleteButton->onclick("deleteClass('TestSet','" . $row->A("TestSetID") . "', function() { " . OnEvent::reloadPopup("Test") . " },'Eintrag wirklich löschen?');");
			$table->addRow(array($row->A("TestIDs"), $row->A("TestLoops"), $runButton, $deleteButton));
		}
		
		echo $table->getHTML();
	}
	
	public function getTestsetPopup() {
		$T = new HTMLTable(3);
		$T->setColWidth(1, 30);
		
		$T->addHeaderRow(array(
			"",
			"Test Name",
			"Anwendung"
		));
		
		
		$AC = anyC::get("Test");
		$AC->addOrderV3("TestID");
		$AC->lCV3();
		while ($test = $AC->getNextEntry()) {
			$T->addRow(array(
				"<input type=\"checkbox\" name=\"testSetTest\" value=\"".$test->A("TestID")."\"\\>",
				$test->A("TestName"),
				$test->A("TestApplication")
			));
		}
		
		$T->addRow(array(
			"<input type=\"text\" name=\"testSetLoops\" \\>",
			"Anzahl Durchläufe des Sets"
		));
		$T->addRowColspan(2, 2);
		
		
		$buttonStart = new Button("Test Set starten", null, "save");
		$buttonStart->onclick("TestManager.startTestSet();");
		$buttonStart->image(null);
		
		$buttonSave = new Button("Test Set speichern", null, "save");
		$buttonSave->onclick("TestManager.saveTestset(); Popup.close();");
		$buttonSave->image(null);
		
		$T->addRow(array($buttonStart));
		$T->addRowColspan(1, 3);
		$T->addRow(array($buttonSave));
		$T->addRowColspan(1, 3);
		
		echo $T->getHTML();
	}
	
	public function saveTestset($tests, $loops) {
		$testSet = new TestSet(-1);
		$testSet->changeA("TestIDs", $tests);
		$loops = (int)$loops;
		if (is_null($loops) || preg_match("/^\s*$/", $loops))
			$testSet->changeA("TestLoops", null);
		else
			$testSet->changeA("TestLoops", $loops);
		$testSet->newMe();
		return;
	}
	
	public function displayResults($completed, $errors){
		$completed = explode(";", $completed);
		$errors = explode(";", $errors);
		
		$T = new HTMLTable(3);
		$T->setColWidth(3, 20);
		$T->maxHeight(400);
		$AC = anyC::get("TestStep", "TestStepTestID", $this->getID());
		$AC->addOrderV3("TestStepSort");
		while($S = $AC->getNextEntry()){
			
			$B = new Button("", "./images/i2/hilfe.png", "icon");
			if(in_array($S->getID(), $completed))
				$B = new Button("", "./images/i2/okCatch.png", "icon");
			
			if(in_array($S->getID(), $errors))
				$B = new Button("", "./images/i2/stop.png", "icon");
			
			$T->addRow(array($S->A("TestStepType"), $S->A("TestStepClass"), $B));
		}
		
		echo $T;
	}
	
	public function saveResults($setId, $completed, $errors) {
		$completed = explode(";", $completed);
		$errors = explode(";", $errors);
		$timestamp = time();
		
		$stepCollection = anyC::get("TestStep", "TestStepTestID", $this->getID());
		$stepCollection->addOrderV3("TestStepSort");
		while ($step = $stepCollection->getNextEntry()) {
			$resultFactory = new Factory("TestResult");
			$resultFactory->sA("TestResultTestID", $this->getID());
			$resultFactory->sA("TestResultTestSetID", $setId);
			$resultFactory->sA("TestResultTestStepID", $step->A("TestStepID"));
			$resultFactory->sA("TestResultTimeStamp", $timestamp);
			
			if (in_array($step->getID(), $errors))
				$resultFactory->sA("TestResultError", 1);
			$resultFactory->store();
			
			unset($resultFactory);
		}
		return null;
	}
	
}
?>