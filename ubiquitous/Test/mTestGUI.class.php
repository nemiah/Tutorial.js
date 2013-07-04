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

class mTestGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mTest");
		$gui->name("Test");
		$gui->attributes(array());
		
		$setButton = $gui->addSideButton("Gespeicherte\nTest Sets", "./ubiquitous/Test/runTest.png");
		$setButton->popup("", "Gespeicherte Test Sets", "Test", -1, "getSavedTestsetsPopup");
//		$setButton->popup("", "Gespeicherte Test Sets", "TestGUI;testSet:2");
		
		$setButton = $gui->addSideButton("Test Set", "./ubiquitous/Test/runTest.png");
		// popup-Methode verwenden
		$setButton->popup("Test", "Test Set ausführen", "Test", -1, "getTestsetPopup");
//		$setButton->editInPopup("Test", -1, "Test Set ausführen", "TestGUI;testSet:1");
		
		$logButton = $gui->addSideButton("Set-Logs\nanzeigen", "./ubiquitous/Test/images/source_code.png");
		$logButton->loadFrame("contentRight", "mTestSetResult");
//		$logButton->addOnclick("");
		
		$mozButton = $gui->addSideButton("Fenster\neinstellen", "empty");
		$mozButton->popup("", "Fenster einstellen", "mTest", "-1", "positionWindow");
		
		$mozButton = $gui->addSideButton("Fenster\nwiederherstellen", "empty");
		$mozButton->popup("", "Fenster wiederherstellen", "mTest", "-1", "resetWindow");
		
		$mozButton = $gui->addSideButton("Aufnahme\nstarten", "empty");
		$mozButton->rmePCR("mTest", "-1", "startRecording");
		
		$mozButton = $gui->addSideButton("Aufnahme\nbeenden", "empty");
		$mozButton->rmePCR("mTest", "-1", "stopRecording");
		
		$gui->attributes(array("TestID", "TestIsActive", "TestIsPresentation", "TestName", "TestApplication"));
		$gui->colWidth("TestID", 20);
		$gui->colWidth("TestIsPresentation", 20);
		$gui->parser("TestIsPresentation", "Util::catchParser");
		$gui->parser("TestIsActive", "Util::catchParser");
		
		return $gui->getBrowserHTML($id);
	}
	
	public function startRecording(){
		#proc_close(proc_open("dconf write /org/gnome/desktop/background/show-desktop-icons false", array(), $foo));
		proc_close(proc_open("recordmydesktop -x 200 -y 150 --width 1310 --height 870  --no-cursor --s_quality 10  --channels 1  --freq 44100 --buffer-size ".(4096*8)." --device pulse --display=:0 -o /home/nemiah/foo.ogv &", array(), $foo));
	}

	public function stopRecording(){
		proc_close(proc_open("killall recordmydesktop", array(), $foo));
	}

	public function positionWindow(){
		#echo "<pre style=\"font-size:10px;\">";
		#print_r($_SERVER);
		#echo "</pre>";
		echo "<p>Verbinde zu ".$_SERVER["REMOTE_ADDR"].":4242...</p>";
		try {
			$commands = array(
				"window.outerWidth = 1210; window.outerHeight = 770; window.moveTo(250, 200); window.personalbar.vsible = false; window.menubar.visible = false; window.toolbar.visible = false; gBrowser.setStripVisibilityTo(false); repl.quit();");

			$firefox_socket = new MozReplSocketHelper($_SERVER["REMOTE_ADDR"]);
			if(!$firefox_socket->connect())
				throw new NoServerConnectionException;

			foreach($commands as $command)
				$firefox_socket->send_command($command);
			
			
			echo "<p>Beende Verbindung!</p>";
		} catch (NoServerConnectionException $e){
			echo "<p style=\"color:red;\">Es konnte keine Verbindung zum Server aufgebaut werden!</p>";
		}
	}
	
	public function resetWindow(){
		echo "<p>Verbinde zu ".$_SERVER["REMOTE_ADDR"].":4242...</p>";
		try {
			$commands = array(
				"window.personalbar.visible = true; window.toolbar.visible = true; window.menubar.visible = true; gBrowser.setStripVisibilityTo(true); repl.quit();");

			$firefox_socket = new MozReplSocketHelper($_SERVER["REMOTE_ADDR"]);
			if(!$firefox_socket->connect())
				throw new NoServerConnectionException;

			foreach($commands as $command)
				$firefox_socket->send_command($command);
			
			
			echo "<p>Beende Verbindung!</p>";
		} catch (NoServerConnectionException $e){
			echo "<p style=\"color:red;\">Es konnte keine Verbindung zum Server aufgebaut werden!</p>";
		}
	}
	
	public static function registerJSAspect(){
//		DynamicJSGUI::Class_registerNew("Test", "{c: null};");
		$AC = anyC::get("Test");
		$AC->addAssocV3("TestIsActive", "=", "1");
		
		while($T = $AC->getNextEntry())
			DynamicJSGUI::Class_registerNew("Test" . $T->getID(), $T->compile() . ";");
		
		
		return true;
	}

}
?>