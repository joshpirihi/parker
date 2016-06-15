<?php

date_default_timezone_set('Pacific/Auckland');

require_once('includes/database.inc.php');
require_once('includes/Topic.php');
require_once 'includes/DataPoint.php';

if (array_key_exists('action', $_GET)) {
	if ($_GET['action'] == 'datapoints') {

		$topicIDs = json_decode($_POST['topics'], true);

		$ret = [];

		foreach ($topicIDs as $t) {

			$ret[$t['id']] = DataPoint::allForTopicSince($t['id'], $t['since']);

		}

		exit(json_encode($ret, JSON_PRETTY_PRINT));
	} else if ($_GET['action'] == 'topics') {
		
		exit(json_encode(Topic::all(), JSON_PRETTY_PRINT));
		
	} else if ($_GET['action'] == 'views') {
		
		exit(json_encode(View::all(), JSON_PRETTY_PRINT));
		
	}
}

?>

<html>
	
	<head>
		<title>Parker</title>
		
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
		
		<!--<link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />-->
		
		<link href="https://fonts.googleapis.com/css?family=Roboto:300" rel="stylesheet">
		
		<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
		<!--<script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>-->
		
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="css/bootstrap.min.css">

		<!-- Optional theme -->
		<link rel="stylesheet" href="css/bootstrap-theme.min.css">

		<!-- Latest compiled and minified JavaScript -->
		<script src="js/bootstrap.min.js"></script>
		
		<link href="css/style.css" rel=stylesheet type=text/css />
		
		<script src="js/js.cookie.js"></script>
		
		<script src="js/moment-with-locales.min.js" type="text/javascript"></script>
		<script src="js/moment-timezone-with-data.min.js" type="text/javascript"></script>
		
		<script src="js/Chart.js" type="text/javascript"></script>
		<script src="js/Chart.Scatter.js" type="text/javascript"></script>
		
		<script src="https://code.highcharts.com/highcharts.js"></script>
		<!--<script src="https://code.highcharts.com/modules/exporting.js"></script>-->
		<script src="https://code.highcharts.com/highcharts-more.js"></script>
		<script src="https://code.highcharts.com/modules/solid-gauge.js"></script>
		
		<script src="js/Topic.js" type="text/javascript"></script>
		<script src="js/GaugeController.js" type="text/javascript"></script>
		<script src="js/ScatterChartController.js" type="text/javascript"></script>
		
		
	</head>
	
	<body style="">
		
		<script type="text/javascript">
		
		var topics = {};
		var topicIDs = [];
		
		var views = [];
		var view = null;
		
		var chartControllers = [];
		var gaugeControllers = [];
		
		var period = 86400;
		
		var mostRecent = moment().unix() - period;
		
		var timezone = 'Pacific/Auckland';
		
		function updateData() {
			
			if (topicIDs.length == 0) {
				setTimeout(updateData, 500);
				return;
			}
			
			//go through the topicIDs array, and figure out the most recent datapoint we have for each
			var topicsToGet = [];
			var earliest = moment().subtract(7, 'days').unix();
			
			for (var t in topicIDs) {
				
				var toGet = {
					id: topicIDs[t],
					since: earliest 
				};
				
				if (topics.hasOwnProperty(topicIDs[t]) && topics[topicIDs[t]].mostRecent > toGet.since) {
					toGet.since = topics[topicIDs[t]].mostRecent;
				}
				
				topicsToGet.push(toGet);
			}
			
			$.post('index.php?action=datapoints', {
				'topics': JSON.stringify(topicsToGet)
			}, function(data) {
				
				mostRecent = moment().unix();
				var oldest = moment().subtract(period, 'seconds');
				
				for (var tID in data) {
					
					topics[tID].addPoints(data[tID]);
					topics[tID].removeOldPoints();
					
					topics[tID].mostRecent = topics[tID].latestPoint.time.unix();
					
					//append the new datapoints onto the topic's array
					/*for (var dp in data[tID]) {
						
						
						topics[tID].chart.datasets[0].addPoint(moment(data[tID][dp].time).toDate(), data[tID][dp].value);
						latestValue = data[tID][dp].value;
						topics[tID].latestTime = moment(data[tID][dp].time);
						
					}
					topics[tID].chart.update();
					
					//*/
					
				}
				
				for (var g in gaugeControllers) {
					gaugeControllers[g].update();
				}
				
				for (var g in chartControllers) {
					chartControllers[g].update();
				}
				
				setTimeout(updateData, 10000);
				
			}, 'json').fail(function() {
				setTimeout(updateData, 30000);
				
				//make sure we still update the Last Updated field
				for (var g in gaugeControllers) {
					gaugeControllers[g].update();
				}
				
				for (var g in chartControllers) {
					chartControllers[g].update();
				}
				
			});
			
		}
		
		
		function loadTopics() {
			
			$.get('index.php?action=topics', function(data) {
				
				for (var d in data) {
					topics[d] = new Topic(data[d]);
				}
				
				$('#placeForCharts').empty();
				
				//drawCharts();
				//updateData();
				
			}, 'json').fail(function() {
				
				setTimeout(loadTopics, 30000);
			});
		}
		
		function changedPeriod() {
			
			Cookies.set('period', $('#period').val(), { expires: moment().add(1, 'year').toDate() });
			
			period = parseInt($('#period').val());
			
			for (var c in chartControllers) {
				chartControllers[c].redrawChart();
			}
			
		}
		
		function loadViews() {
			
			$.get('index.php?action=views', function(data) {
				
				views = data;
				if (view == null) {
					view = views[0];
				} else {
					for (var v in views) {
						if (views[v].id == view.id) {
							view = views[v];
							break;
						}
					}
				}
				displayView();
				
			}, 'json').fail(function() {
				
				setTimeout(loadTopics, 30000);
			});
		}
		
		function displayView() {
			
			gaugeControllers = [];
			$('#placeForGauges').empty();
			
			chartControllers = [];
			$('#placeForCharts').empty();
			
			//reset the topicIDs array
			
			topicIDs = [];
			for (var t in view.viewTopics) {
				topicIDs.push(view.viewTopics[t].topicID);
				
				if (view.viewTopics[t].gauge) {
					//make some new gauges
					var containerID = 'gauge_'+view.viewTopics[t].topicID;
					$('#placeForGauges').append(
						$('<div>').attr('id', containerID).addClass('gauge')
					);

					var gc = new GaugeController(view.viewTopics[t], containerID);
					gc.draw();
					
					gaugeControllers.push(gc);
				}
				
				//add a chart if thats what they want
				if (view.viewTopics[t].chart) {

					var cc = new ScatterChartController(view.viewTopics[t]);
					cc.draw();
					
					chartControllers.push(cc);
				}
				
			}
			
		}
		
		function round(value, decimals) {
			return Number(Math.round(value+'e'+decimals)+'e-'+decimals);
		}
		
		$(function(){
			
			var cookiePeriod = Cookies.get('period');
					
			if (typeof cookiePeriod !== 'undefined') {
				period = parseInt(cookiePeriod);
			}
			
			$('#period').val(period);//.selectmenu('refresh');
			 
			loadTopics();
			loadViews();
			updateData();
		});
		
		</script>
		
		<nav class="navbar navbar-default">
			<div class="container">
				
				<div class="navbar-header">
					
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					
					<a class="navbar-brand" target="#">Weather Station</a>
					
					<ul
					
				</div>
				
				
			</div>
			
			<div id="navbar" class="navbar-collapse collapse">
				
				<ul class="nav navbar-nav">
					
				</ul>
				
				<ul class="nav navbar-nav navbar-right">
					<li class="">
						
					</li>
				</ul>
				
			</div>
			
		</nav>
		
		<div class="container">
			<div class="">
				<select id="period" onchange="changedPeriod();">
					<option value="86400">View: 24hrs</option>
					<option value="172800">View: 48hrs</option>
					<option value="604800">View: 1 week</option>
				</select>
				</div>
			<div class="row" id="placeForCharts"></div>
			<div class="row" id="placeForGauges"></div>
			
			<!--<div class="compass">
				<div class="direction">
					<p>NE<span>10 kmh</span></p>
				</div>
				<div class="arrow now ne"></div>
				<div class="arrow was n"></div>
			</div>-->
			
		</div>
			
	</body>
	
</html>