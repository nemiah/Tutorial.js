<?php
/**
 * 	
 * 	
 * 	@author		Marcel Block <mb@suchlotsen.de>
 * 	@version	
 * 	@copyright	2012 Marcel Block <mb@suchlotsen.de>
 * 	
 * 	2012-04-07
 */
class mTestSetResultGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page) {
		$this->addOrderV3("TestSetResultID", "ASC");
		$this->setFieldsV3(array("TestSetResultStart", "TestSetResultEnd - TestSetResultStart", "TestSetResultSteps", "TestSetResultLoops"));
		
		$gui = new HTMLGUIX($this);
		$gui->name("Test-Set");
		$gui->options(true, true, false);
		$gui->attributes(array(
			"TestSetResultStart",
			"TestSetResultEnd - TestSetResultStart",
			"TestSetResultSteps",
			"TestSetResultLoops"
		));
		$gui->parser("TestSetResultStart", "mTestSetResultGUI::parseTimestamp");
		$gui->parser("TestSetResultEnd - TestSetResultStart", "mTestSetResultGUI::parseDuration");
		
		return $gui->getBrowserHTML();
	}
	
	public static function parseTimestamp($w, $l, $E) {
		$date = new DateTime();
		$date->setTimestamp($w);
		return $date->format("d.m.Y H:m:s");
	}
	
	public static function parseDuration($w, $l, $E) {
		$value = (int)$w;
		$minutes = floor($value / 60);
		$seconds = $value % 60;
		$secondsStr = ((strlen($seconds) == 1)? "0":"") . $seconds;
		return $minutes . ":" . $secondsStr;
	}
	
}

?>
