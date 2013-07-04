<?php
/**
 * 	
 * 	
 * 	@author		Marcel Block <mb@suchlotsen.de>
 * 	@version	
 * 	@copyright	2012 Marcel Block <mb@suchlotsen.de>
 * 	
 * 	2012-04-04
 */
class TestSetResultGUI extends TestSetResult implements iGUIHTML2 {
	
	public function getHTML($id) {
		$collection = new anyC();
		$collection->setCollectionOf("TestResult");
		$collection->addOrderV3("TestResultID", "ASC");
		$collection->addAssocV3("TestResultTestSetID", "=", $this->getID());
		$collection->addJoinV3("Test", "TestResultTestID", "=" , "TestID");
		$collection->addJoinV3("TestStep", "TestResultTestStepID", "=", "TestStepID");
		
		$table = new HTMLTable(4);
		$table->addHeaderRow(array(
			"Test",
			"TestStepType",
			"Zeitstempel",
			"Fehler"
		));
		$error = new Button("", "./images/i2/stop.png", "icon");
		$noError = new Button("", "./images/i2/okCatch.png", "icon");
		$timestamps = array();
		$testNames = array();
		$stepTypes = array();
		while ($row = $collection->getNextEntry()) {
			$timestamps[] = $row->A("TestResultTimeStamp");
			$testNames[] = $row->A("TestName");
			$stepTypes[] = $row->A("TestStepType");
			
			$rowError = &$noError;
			if ($row->A("TestResultError"))
				$rowError = &$error;
			$table->addRow(array(
				$row->A("TestName"),
				$row->A("TestStepType"),
				Util::CLDateTimeParser($row->A("TestResultTimeStamp")) . ":" . ($row->A("TestResultTimeStamp") - Util::CLDateTimeParser(Util::CLDateTimeParser($row->A("TestResultTimeStamp")), "store")),
				$rowError
			));
		}
		
		$filter = "";
		// Filter: Uhrzeit
		$timestamps = array_unique($timestamps);
		sort($timestamps);
		$filter .= '<select name="time" onchange="TestManager.FilterResults.filter(' . $this->getID() . ', this);"><option value="">Auswahl: Uhrzeit</option>';
		foreach ($timestamps as $uniqueTimestamp)
			$filter .= '<option value="' . $uniqueTimestamp . '">' . Util::CLDateTimeParser($uniqueTimestamp) . ":" . ($uniqueTimestamp - Util::CLDateTimeParser(Util::CLDateTimeParser($uniqueTimestamp), "store")) . '</option>';
		$filter .= '</select>';
		// Filter: Fehler
		$filter .= '<select name="error" onchange="TestManager.FilterResults.filter(' . $this->getID() . ', this);"><option value="">Auswahl: Fehler</option><option value="noerror">Keine Fehler</option><option value="error">Fehler</option></select>';
		// Filter: Test
		$testNames = array_unique($testNames);
		sort($testNames);
		$filter .= '<select name="testname" onchange="TestManager.FilterResults.filter(' . $this->getID() . ', this);"><option value="">Auswahl: Test</option>';
		foreach ($testNames as $testName)
			$filter .= '<option value="' . $testName . '">' . $testName . '</option>';
		$filter .= '</select>';
		// Filter: StepType
		$stepTypes = array_unique($stepTypes);
		sort($stepTypes);
		$filter .= '<select name="testtype" onchange="TestManager.FilterResults.filter(' . $this->getID() . ', this);"><option value="">Auswahl: StepType</option>';
		foreach ($stepTypes as $stepType)
			$filter .= '<option value="' . $stepType . '">' . $stepType . '</option>';
		$filter .= '</select>';
		
		return $filter . '<br/><br/><div id="filterResult">' . $table->getHTML() . '</div>
			<script type="text/javascript">TestManager.FilterResults.reset();</script>';
	}
	
	public function filter($filters) {
		$filters = explode(";", $filters);
		foreach ($filters as &$filter)
			$filter = explode(":", $filter);
		
		$collection = new anyC();
		$collection->setCollectionOf("TestResult");
		$collection->addAssocV3("TestResultTestSetID", "=", $this->getID());
		$collection->addJoinV3("Test", "TestResultTestID", "=" , "TestID");
		$collection->addJoinV3("TestStep", "TestResultTestStepID", "=", "TestStepID");
		foreach ($filters as $filter)
			// FilterType
			switch ($filter[0]) {
				case "time":
					$collection->addAssocV3("TestResultTimeStamp", "=", $filter[1]);
					break;
				case "error":
					if ($filter[1] == "noerror")
						$collection->addAssocV3("TestResultError", "=", "0");
					else
						$collection->addAssocV3("TestResultError", "=", "1");
					break;
				case "testname":
					$collection->addAssocV3("TestName", "=", $filter[1]);
					break;
				case "testtype":
					$collection->addAssocV3("TestStepType", "=", $filter[1]);
					break;
			}
		
		$table = new HTMLTable(4);
		$error = new Button("", "./images/i2/stop.png", "icon");
		$noError = new Button("", "./images/i2/okCatch.png", "icon");
		$count = 0;
		while ($row = $collection->getNextEntry()) {
			$rowError = &$noError;
			if ($row->A("TestResultError"))
				$rowError = &$error;
			$table->addRow(array(
				$row->A("TestName"),
				$row->A("TestStepType"),
				Util::CLDateTimeParser($row->A("TestResultTimeStamp")) . ":" . ($row->A("TestResultTimeStamp") - Util::CLDateTimeParser(Util::CLDateTimeParser($row->A("TestResultTimeStamp")), "store")),
				$rowError
			));
			$count++;
		}
		if ($count)
			echo $table->getHTML();
		else
			echo "Dieser Filter enthält keine Ergebnisse.";
	}
	
	public function startTestSet($loops, $testIds, $steps) {
		$testIds = explode(";", $testIds);
		$steps = explode(";", $steps);
		
		$tests = "";
		$stepCount = 0;
		for ($i = 0; $i < count($testIds); $i++) {
			$tests .= $testIds[$i] . ":" . $steps[$i] . ";";
			$stepCount += $steps[$i];
		}
		$factory = new Factory("TestSetResult");
		$factory->sA("TestSetResultSteps", $stepCount);
		$factory->sA("TestSetResultStart", time());
		$factory->sA("TestSetResultLoops", $loops);
		$factory->sA("TestSetResultTests", $tests);
		$id = $factory->store();
		echo $id;
	}
	
	public function saveResults() {
		$this->changeA("TestSetResultEnd", time());
		$this->saveMe();
	}
	
	public function endTestSet() {
		$setResultCollection = anyC::get("TestResult");
		$setResultCollection->addAssocV3("TestResultTestSetID", "=", $this->getID());
		$setResultCollection->addOrderV3("TestResultID");
		$flag = false;
		while ($entry = $setResultCollection->getNextEntry()) {
			if ($entry->A("TestResultError")) {
				$flag = true;
				break;
			}
		}
		
		$message = "Während des Testlaufen sind keine Fehler aufgetreten.";
		if ($flag)
			$message = "Während des Testlaufes sind Fehler aufgetreten.";
		mail("rainer@furtmeier.it", "Report des automatisiertes Testsystems", $message);
	}

}

?>
