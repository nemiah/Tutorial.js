var Test = {
	c: null
};

function continueTest() {
	var jsonString = localStorage.getItem('punitTest');
	localStorage.removeItem('punitTest');
	if (typeof jsonString == "undefined" || jsonString == "" || jsonString == null)
		return;
	var jsonObject = eval(jsonString);
	$j.extend(Test, jsonObject);
	Test.c.next();
}

var TestManager = {
	testSetLoops: null, // Schleifen des Test-Sets
	testSetLoopCounter: null,
	testSetIds: null, // Feld: IDs der durchzuführenden Tests
	testSetId: null, // ID des TestSets. Wird beim Start des Sets generiert.
	presentationStop: false,
	presentationEnd: false,
	
	presentationStarted: false,
	presentationFinished: false,
	presentationStepped: false,
	
	soundFormat: "ogg",
	currentAudio: new Audio(),
	currentAudioVolume: 1,
	interval: null,
	nextIn: 0,
	nextDone: false,
	makeVideo: false,
	
	regExpStringGeneratorUrl: "http://localhost:8080/FHS_RegExpStringGenerator/RegExpStringGen?regexp=",
	
	getGeneratedString: function(regExp, callback) {
		$j.ajax({
			dataType: 'jsonp',
			jsonp: 'jsonp_callback',
			url: TestManager.regExpStringGeneratorUrl + encodeURIComponent(regExp),
			error: function(jqXHR, textStatus, errorThrown) {
				callback(regExp);
			},
			success: function(data, textStatus, jqXHR) {
				callback(data);
			}
		});
	},
	
	initVideoEdit: function() {
		var width = 0;
		$j(".punit_draggable").each(function(){
			width += $j(this).width();
		});
		var stepsContainer = $j("#testStepsContainer");
		stepsContainer.width($j(document).width() - 2)
		.height($j(document).height() / 2)
		.sortable({
			update: function(){
				var newOrder = $j(this).sortable('serialize').replace(/\[\]=/g, '').replace(/&/g, ';').replace(/[a-zA-Z]*/g, '');
				contentManager.rmePCR('mTestStep', '-1', 'saveOrder', newOrder);
			}
		}).disableSelection();
	},
	
	checkInsertedValues: function(testStepClass, testStepId) {
		var lastId = Test.c.lastID;
		var lastStepId = Test.c.lastStepID;
		var values = Test.c.lastInsertedValues;
		contentManager.loadFrame('contentLeft', testStepClass, lastId, 0, null, function() {
			var ok = true;
			$j.each(values, function(key, value) {
				var regExp = new RegExp(value, "i");
				var item = $j('#' + key);
				var itemValue = item.val();
				if (item.prop('tagName') == 'SELECT') {
					item.find('option').each(function() {
						var $this = $j(this);
						if ($this.val() == itemValue)
							itemValue = $this.html();
					});
				}
				if (!regExp.test(itemValue))
					ok = false;
			});
			if(ok)
				Test.c.completed.push(lastStepId);
			else
				Test.c.errors.push(lastStepId);
			Test.c.next();
		});
	},
	
	setVolume: function(volume){
		TestManager.currentAudio.volume = volume;
	},
	
	getVolume: function(){
		return TestManager.currentAudio.volume;
	},
	
	setMute: function(muted){
		TestManager.currentAudio.muted = muted;
	},
	
	getMute: function(){
		return TestManager.currentAudio.muted;
	},
	
	compileTests: function(callback) {
		if(!$j('script[src^="./javascript/DynamicJS.php"]').length){
			callback();
			return;
		}
		
		$j('script[src^="./javascript/DynamicJS.php"]').remove();
		if (callback != "undefined" && callback != null && callback != "") {
			$j(document).ajaxComplete(function() {
				callback();
				$j(this).off('ajaxComplete');
			});
		}
		$j('head').append('<script type="text/javascript" src="./javascript/DynamicJS.php?r=' + this.rand(1000, 99999) + '">');
		return;
	},
	
	Mouse: {
		mouse: null,
		
		init: function() {
			this.mouse = $j('#pUnitPointer');
		},
		
		uninit: function() {
			var $this = this;
			
			if(this.mouse)
				this.mouse.fadeOut(500, function(){
					$this.mouse.remove();
				});
		},
		
		click: function(element) {
			this.mouse.attr("src", "./ubiquitous/Test/images/mouse_click.png");
			
			element.first().effect("highlight", {}, 600);
			
			var $this = this;
			$j("html").oneTime(700, "pUnitPresentation" + TestManager.rand(1000, 999999), function() {
				$this.mouse.attr("src", "./ubiquitous/Test/images/mouse.png");
			});
		},
		
		moveTo: function(element, click, message, onFinished) {
			if(typeof click == "undefined")
				click = true;
			
			$j('.qtip.ui-tooltip').qtip('hide');
			var scrollContainer = "#container";
			if(!$j(scrollContainer).length)
				scrollContainer = "body";
			//if(TestManager.makeVideo)
			//	scrollContainer = window;
			
			var $this = this;
			var difference = ($j(window).height() / 2) - element.offset().top;
			var to = $j(scrollContainer).scrollTop();
			if (Math.abs(difference) > ($j(window).height() / 6)) {
				if (difference > 0)
					to = $j(scrollContainer).scrollTop() - Math.abs(difference);
				else
					to = $j(scrollContainer).scrollTop() + Math.abs(difference);
			}
			var popup = element.closest($j('#windows'));
			if (popup.length)
				to = $j(scrollContainer).scrollTop();
			console.log($j(scrollContainer).scrollTo(to < 0 ? 0 : to));
			
			$j(scrollContainer).scrollTo(to < 0 ? 0 : to, 1000, {
				onAfter: function(){
					if(TestManager.presentationEnd)
						return;
					
					var x = element.offset().left + element.width() / 2 - 15;
					var y = element.offset().top + element.height() / 2 - 15;
					
					var dist = Math.sqrt(Math.pow($this.mouse.offset().left - x, 2) + Math.pow($this.mouse.offset().top - y, 2));
					$this.mouse.animate({
						left: x,
						top: y
					}, Math.round(dist) * 1.6, function() {
						if(click)
							$this.click(element);
					});
					var step = Test.c.audio[Test.c.i+1];
					var audioPath = Test.c.audioPath+"/A"+(step < 1000 ? "0" : "")+(step < 100 ? "0" : "")+(step < 10 ? "0" : "")+(step)+"."+TestManager.soundFormat+"?rand="+TestManager.rand(1, 999999);
					
					if(Test.c.audioPath != ""){
						TestManager.currentAudio.src = audioPath;
						TestManager.currentAudio.play();
					}

					if(typeof message != "undefined" && message != "")
						$j(element).first().qtip({
							content: {
								text: message
							},
							position: {
								my: 'bottom center',
								at: 'top center',
								viewport: true,
								adjust: {
									method: 'flip'
								}
							},
							show: {
								event: false,
								ready: true,
								solo: true
							},
							hide: false,
							style: {
								classes: 'ui-tooltip-shadow ui-tooltip-green ui-tooltip-rounded ui-tooltip-text'
							}
						});
						
					
					if(typeof onFinished == "function")
						onFinished();
				}
			});
		}
	},
	
	FilterResults: {
		filters: new Array(),
		filter: function(id, obj) {
			var $this = $j(obj); // Select
			var type = $this.attr("name");
			var value = $this.val();
			if (value == "" || value == null || value == "undefined")
				delete this.filters[type];
			else
				this.filters[type] = value;
			var filterString = "";
			for (var key in this.filters)
				filterString += key + ":" + this.filters[key] + ";";
			filterString = filterString.substr(0, filterString.length - 1);
			contentManager.rmePCR("TestSetResult", id, "filter", [filterString], function(transport) {
				$j("#filterResult").html(transport.responseText);
			});
			return true;
		},
		reset: function() {
			this.filters = new Array();
		}
	},
	
	pausePresentation: function(){
		TestManager.presentationStop = true;
		
		TestManager.currentAudio.pause();
	},
	
	continuePresentation: function(){
		TestManager.presentationStop = false;
		//Test.c.next();
		
		TestManager.currentAudio.play();
	},
	
	endPresentation: function(){
		TestManager.Mouse.uninit();
		
		TestManager.currentAudio.pause();
		TestManager.presentationStop = true;
		TestManager.presentationEnd = true;
		
		$j('.qtip').qtip('hide');
		if(typeof phynxContextMenu != "undefined")
			phynxContextMenu.stop();
		
		if(TestManager.interval != null){
			window.clearInterval(TestManager.interval);
			TestManager.interval = null;
		}
	},
	
	continueIn: function(milliseconds){
		if(TestManager.interval == null)
			TestManager.interval = window.setInterval(function(){
				if(TestManager.presentationStop || TestManager.nextDone)
					return;
				
				if(TestManager.nextIn <= 0){
					TestManager.nextDone = true;
					Test.c.next();
				}
				
				//if(TestManager.nextIn <= 0)
				//	return;
				
				TestManager.nextIn -= 100;
			}, 100);
			
		TestManager.nextIn = milliseconds;
		TestManager.nextDone = false;
	},
	
	startPresentation: function(test, flag, makeVideo, callbackStarted, callbackStepped, callbackFinished) {
		if(typeof makeVideo == "undefined")
			makeVideo = false;
		
		TestManager.makeVideo = makeVideo;
		
		if (typeof test == 'number' && flag) {
			alert("Fehler beim Starten der Präsentation");
			return;
		}
		
		if (!flag) {
			this.compileTests(function() {
				if (typeof test == 'number')
					var object = window["Test"+test];//eval('Test' + test);
				
				var testObject = $j.extend(object, TestManager.Test);
				testObject.presentation = true;
				TestManager.startPresentation(testObject, true, makeVideo, callbackStarted, callbackStepped, callbackFinished);
			});
			return;
		}
		/*
		var popupId = this.rand(1, 999999);
		var popupContent = '<div style="min-height: 50px; text-align: center">\n\
		<span id="testPopupCount">0</span> von ' + test.count + '<br/>\n\
		<img id="testPresentationControll" style="cursor: pointer; margin-top: 9px;" src="./ubiquitous/Test/images/control_pause_blue.png"></div>';
		Popup.create(popupId, "rand", "pUnit Test-Set", {persistent: true});
		$('rand' + 'DetailsContent' + popupId).update(popupContent);
		Popup.show(popupId, "rand");*/
		//Popup.windowsOpen++;
		
		TestManager.presentationStop = false;
		TestManager.presentationEnd = false;
		
		if(typeof callbackStarted == "function")
			callbackStarted(test.count);
		if(!$j("#pUnitPointer").length)
			$j("body").append('<img id="pUnitPointer" style="position: absolute;top:10px;left:10px;display:none;z-index:10000" src="./ubiquitous/Test/images/mouse.png">');
		
		$j("#pUnitPointer").fadeIn();/*.animate({
			left: 10,
			top: 10,
			opacity:1
		}, 1000);*/
		
		/*$j("#testPresentationControll").click(function() {
			var $this = $j(this);
			if (TestManager.presentationStop) {
				$this.attr("src", "./ubiquitous/Test/images/control_pause_blue.png");
				TestManager.presentationStop = false;
			} else {
				$this.attr("src", "./ubiquitous/Test/images/control_play_blue.png");
				TestManager.presentationStop = true;
			}
		});*/
		
		TestManager.presentationFinished = callbackFinished;
		TestManager.presentationStepped = callbackStepped;
		TestManager.presentationStarted = callbackStarted;
		//console.log(TestManager.presentationFinished);
		this.Mouse.init();
		test.run();
	},
	
	/**
	 * Params:
	 * test	Object|Number	Nummer des Testes oder Objekt zur Ausführung des Callbacks
	 * flag	boolean			Flag zur Kompilierung aller Tests
	 */
	startTest: function(test, flag) {
		if (typeof test == 'number' && flag) {
			alert("Fehler beim Starten des Testes");
			return;
		}
		
		if (!flag) {
			this.compileTests(function() {
				if (typeof test == 'number')
					var object = eval('Test' + test);
				var testObject = $j.extend(object, TestManager.Test);
				TestManager.startTest(testObject, true);
			});
			return;
		}
		test.run();
	},
	
	/**
	 * compileFlag: Flag zum Kompilieren der Tests
	 * inputFlag: Flag zum Einlesen der Set-Werte (TestIDs, Schleifen)
	 */
	startTestSet: function(compileFlag, inputFlag, loopCounter, idCounter, savedSet, savedSetIds, savedSetLoops) {
		if (!compileFlag) {
			this.compileTests(function() {
				TestManager.startTestSet(true, null, null, null, savedSet, savedSetIds, savedSetLoops);
			});
			return;
		}
		
		if (!inputFlag) {
			var testIds = new Array();
			if (savedSet) {
				testIds = savedSetIds.split(";");
				for (var i = 0; i < testIds.length; i++)
					testIds[i] = parseInt(testIds[i]);
				this.testSetLoops = savedSetLoops;
			} else {
				this.testSetLoops = parseInt($j("input[name=testSetLoops]").val());
				var checkedTests = $j("input[name=testSetTest]:checked");
				checkedTests.each(function() {
					testIds.push(parseInt($j(this).attr("value")));
				});
			}
			
			// Überschreiben da die IDs an keiner Stelle resetet werden
			this.testSetIds = testIds;
			// Set in der Datenbank einrichten
			var steps = new Array();
			for (var i = 0; i < this.testSetIds.length; i++) {
				var obj = eval("Test" + TestManager.testSetIds[i]);
				steps.push(obj.count);
			}
			
			contentManager.rmePCR("TestSetResult", "-1", "startTestSet", [this.testSetLoops, this.testSetIds.join(";"), steps.join(";")], function(transport) {
				TestManager.testSetId = transport.responseText;
			});
			
			var loopCounter = 0;
			var idCounter = 0;
		}
		
		var testObject = eval("Test" + this.testSetIds[idCounter]);
		testObject = $j.extend(testObject, TestManager.Test);
		testObject.testSet = true;
		testObject.run();
		if (++idCounter == this.testSetIds.length) {
			idCounter = 0;
			loopCounter++;
		}
		
		this.testSetLoopCounter = loopCounter;
		if (this.testSetLoops != loopCounter) {
			$j("html").oneTime("10s", "testSet" + this.rand(1000, 99999), function() {
				TestManager.startTestSet(true, true, loopCounter, idCounter);
			});
		} else {
			var popupId = this.rand(1, 999999);
			var popupContent = '<div style="min-height: 50px; text-align: center">\n\
			Test-Set abgeschlossen</div>';
			Popup.create(popupId, "rand", "pUnit Run - Funktionen");
			$('rand' + 'DetailsContent' + popupId).update(popupContent);
			Popup.show(popupId, "rand");
		}
	},
	
	saveTestset: function() {
		var testSetLoops = parseInt($j("input[name=testSetLoops]").val());
		var testIds = new Array();
		var checkedTests = $j("input[name=testSetTest]:checked");
		checkedTests.each(function() {
			testIds.push(parseInt($j(this).attr("value")));
		});
		
		contentManager.rmePCR("Test", "-1", "saveTestset", [testIds.join(";"), testSetLoops]);
	},
	
	rand: function(min, max) {
		if (min > max)
			return false;
		if (min == max)
			return min;
		
		var random = Math.random();
		while (random == 1.0)
			random = Math.random();
		random = min + parseInt(random * (max - min + 1));
		return random;
	},
	
	Test: {
		presentation: false,
		presentationNext: true,
		
		testSet: false,
		completed: new Array(),
		errors: new Array(),
		i: 0,
		lastID: null,
		
		next: function() {
			var $this = this;
			if (TestManager.presentationStop)
				return;
			
			if (this.presentation && this.presentationNext) {
				this.presentationNext = false;
				
				if(typeof TestManager.presentationStepped == "function")
					TestManager.presentationStepped($this.i+1);
				
				
				var mouseAnimation = this["stepMouseAnimation" + (this.i + 1)];
				if(typeof mouseAnimation == 'function')
					mouseAnimation(function(){
						var inputAnimation = $this["stepInputAnimation" + ($this.i + 1)];
						if(typeof inputAnimation == 'function')
							inputAnimation();
					});
				else
					Test.c.next();
				
				return;
			}
			this.i++;
			if(this.count == this.i)
				return this.submit();
			
			if (this.presentation)
				this.presentationNext = true;
			
			var step = this["step" + this.i];//eval("this.step" + this.i);
			
			if(typeof step == 'function')
				step();
		},
		
		run: function() {
			this.completed = new Array();
			this.errors = new Array();
			this.i = -1;
			
			Test.c = this;
			Test.c.next();
		},
		
		submit: function() {
			if (this.testSet) {
				contentManager.rmePCR("TestSetResult", TestManager.testSetId, "saveResults", "");
				contentManager.rmePCR("Test", this.id, "saveResults", [TestManager.testSetId, this.completed.join(";"), this.errors.join(";")]);
				if (TestManager.testSetLoopCounter == TestManager.testSetLoops)
					contentManager.rmePCR("TestSetResult", TestManager.testSetId, "endTestSet", "");
				return true;
			}
			
			if (this.presentation) {
				if(typeof TestManager.presentationFinished == "function")
					TestManager.presentationFinished();
				TestManager.endPresentation();
				return true;
			}
			
			Popup.load('Testergebnisse', 'Test', this.id, 'displayResults', [this.completed.join(';'), this.errors.join(';')]);
			return true;
		}
	}
};

if((new Audio("")).canPlayType("audio/ogg").match(/maybe|probably/i))
	TestManager.soundFormat = 'ogg';
else
	TestManager.soundFormat = 'mp3';


// jTypeWriter, JQuery plugin
// v 1.1 
// Licensed under GPL licenses.
// Copyright (C) 2008 Nikos "DuMmWiaM" Kontis, info@dummwiam.com
// http://www.DuMmWiaM.com/jTypeWriter
// ----------------------------------------------------------------------------
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
// 
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
// ----------------------------------------------------------------------------

(function($) {
	$.fn.jTypeWriter = function (b) {
		var c, nIntervalCounter, nSequentialCounter, nSequentialCounterInternal, nInterval, nLoopInterval;
		var d = $.extend({}, $.fn.jTypeWriter.defaults, b);
		var e = d.duration * 1000;
		var f = d.type.toLowerCase();
		var g = d.sequential;
		var h = d.onComplete;
		var j = d.text;
		var k = d.loop;
		var l = d.loopDelay;
		var m = (f == "word") ? " " : ".";
		var n = new Array();
		var o = 0;
		for (i = 0; i < this.length; i++) {
			if (j) {
				$(this[i]).text(j)
			}
			if (f == "letter") n.push({
				obj: $(this[i]),
				initialText: $(this[i]).text()
			});
			else n.push({
				obj: $(this[i]),
				initialText: $(this[i]).text().split(m)
			});
			if (!g) o = n[i].initialText.length > o ? n[i].initialText.length : o;
			else o += n[i].initialText.length;
			$(this[i]).text("")
		}
		init();

		function init() {
			c = e / o;
			nIntervalCounter = 0;
			nSequentialCounter = nSequentialCounterInternal = 0;
			nInterval = (!g) ? setInterval(typerSimultaneous, c) : setInterval(typerSequential, c)
		};

		function typerSimultaneous() {
			nIntervalCounter++;
			for (i = 0; i < n.length; i++) {
				var a = n[i];
				if (a.initialText.length >= nIntervalCounter) {
					if (f == "letter") {
						a.obj.val(a.initialText.substr(0, nIntervalCounter))
					} else {
						a.obj.append(a.initialText[nIntervalCounter - 1]);
						if (nIntervalCounter < o) {
							a.obj.append(m)
						}
					}
				}
			}
			if (nIntervalCounter >= o) {
				circleEnd()
			}
		};

		function typerSequential() {
			$obj = n[nSequentialCounter];
			if (f == "letter") {
				$obj.obj.val($obj.initialText.substr(0, ++nSequentialCounterInternal))
			} else {
				$obj.obj.append($obj.initialText[nSequentialCounterInternal++]);
				if (nSequentialCounterInternal < $obj.initialText.length) $obj.obj.append(m)
			}
			if (nSequentialCounterInternal >= $obj.initialText.length) {
				nSequentialCounter++;
				nSequentialCounterInternal = 0
			}
			nIntervalCounter++;
			if (nIntervalCounter >= o) {
				circleEnd()
			}
		};

		function circleEnd() {
			clearInterval(nInterval);
			if (f != "letter") {}
			if (k) {
				if (l) nLoopInterval = setInterval(loopInterval, l * 1000);
				else newLoop()
			}
			h()
		};

		function newLoop() {
			for (i = 0; i < n.length; i++) {
				n[i].obj.val("")
			}
			init()
		};

		function loopInterval() {
			newLoop();
			clearInterval(nLoopInterval)
		};

		function endEffect() {
			clearInterval(nInterval);
			for (i = 0; i < n.length; i++) {
				n[i].obj.val(n[i].initialText)
			}
		};
		this.endEffect = endEffect;
		return this
	};
	$.fn.jTypeWriter.defaults = {
		duration: 2,
		type: "letter",
		sequential: true,
		onComplete: function () {},
		text: "",
		loop: false,
		loopDelay: 0
	};
	$.fn.jTypeWriter.variables = {
		aObjects: new Array()
	}
})(jQuery);

function serialize(_obj) {
	try {
		if ((typeof _obj.toSource !== 'undefined' && typeof _obj.toSource !== null) && typeof _obj.callee === 'undefined') {
			return _obj.toSource();
		}
	} catch (error) {
		// :-)
	}
	switch (typeof _obj) {
		case 'number':
		case 'boolean':
		case 'function':
			return _obj;
			break;

		case 'string':
			return '"' + _obj + '"';
			break;

		case 'object':
			var str;
			var flag = false;
			try {
				if (_obj.constructor === Array || typeof _obj.callee !== 'undefined') {
					flag = true;
				}
			} catch (error) {
				// :-)
			}
			if (flag) {
				str = '[';
				var i, len = _obj.length;
				for (i = 0; i < len-1; i++) {
					str += serialize(_obj[i]) + ',';
				}
				str += serialize(_obj[i]) + ']';
			} else {
				str = '{';
				var key;
				for (key in _obj) {
					str += '"' + key + '":' + serialize(_obj[key]) + ',';
				}
				str = str.replace(/\,$/, '') + '}';
			}
			return str;
			break;

		default:
			return;
			break;
	}
}
