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
		
	}
}

?>

<html>
	
	<head>
		<title>Parker</title>
		
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
		
		<link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
		
		<link href="css/style.css" rel=stylesheet type=text/css />
		
		<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
		<script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
		
		<script src="js/js.cookie.js"></script>
		
		<script src="js/moment-with-locales.min.js" type="text/javascript"></script>
		<script src="js/moment-timezone-with-data.min.js" type="text/javascript"></script>
		
		<script src="js/Chart.js" type="text/javascript"></script>
		<script src="js/Chart.Scatter.js" type="text/javascript"></script>
		
		<script src="https://code.highcharts.com/highcharts.js"></script>
		<script src="https://code.highcharts.com/modules/exporting.js"></script>
		
		<script src="js/Topic.js" type="text/javascript"></script>
		
		
		
	</head>
	
	<body style="font-family: sans-serif;">
		
		<script type="text/javascript">
		
		var topics = {};
		
		var period = 86400;
		
		var mostRecent = moment().unix() - period;
		
		var timezone = 'Pacific/Auckland';
		
		function drawCharts() {
			
			$('#placeForCharts').empty();
			
			var $ul = $('<ul>');
			
			$('#placeForCharts').append($ul);
			
			for (var t in topics) {
				
				var $canvas = $('<canvas>').addClass('').attr('id', 'canvas_'+topics[t].id);
				
				$ul.append(
					$('<li>').append(
						$('<fieldset>').addClass('chartContainer').append(
						//$('<fieldset>').append(
							$('<legend>').css('text-align', 'center').text(topics[t].description),
							$('<table>').addClass('summary').append(
								$('<tr>').append(
									$('<td>').addClass('firstColumn'),
									$('<td>').addClass('secondColumn').attr('colspan', '2'),//.addClass('valueCell valueTitle').attr('id', 'minmax_'+t).text('Past '+topics[t].periodName()),
									$('<td>')
								),
								$('<tr>').append(
									$('<td>').addClass('valueCell').append(
										$('<div>').addClass('valueTitle').text('Current'),
										$('<div>').css('font-size', '150%').attr('id', 'current_'+t)
									),
									$('<td>').addClass('valueCell').append(
										$('<div>').addClass('valueTitle').text('Min'),
										$('<div>').css('font-size', '100%').css('padding-top', '5px').attr('id', 'min24_'+t)
									),
									$('<td>').addClass('valueCell').append(
										$('<div>').addClass('valueTitle').text('Max'),
										$('<div>').css('font-size', '100%').css('padding-top', '5px').attr('id', 'max24_'+t)
									),
									$('<td>')
								)
							),
							$('<div>').addClass('chart').append($canvas),
							$('<div>').addClass('lastUpdated').attr('id', 'lastUpdated_'+t)
						//)
						)
					)
				);
				
				var ctx = document.getElementById("canvas_"+t).getContext("2d");
				
				topics[t].createChart(ctx);
			}
			
			
		}
		
		function updateData() {
			
			var topicIDs = [];
			for (var t in topics) {
				topicIDs.push({
					id: topics[t].id,
					since: topics[t].mostRecent
				});
			}
			
			$.post('index.php?action=datapoints', {
				'topics': JSON.stringify(topicIDs)
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
					topics[tID].chart.update();//*/
					
					$('#current_'+tID).empty().text(topics[tID].latestPoint.value+topics[tID].units);
					$('#lastUpdated_'+tID).empty().text('Updated '+topics[tID].latestPoint.time.fromNow());
					
					var minMax = topics[tID].minMax();
					$('#min24_'+tID).empty().append(
						minMax.minValue+topics[tID].units
					);
					$('#max24_'+tID).empty().append(
						minMax.maxValue+topics[tID].units
					);
					
				}
				
				//prunePointArrays();
				//fillCharts();
				
				setTimeout(updateData, 10000);
				
			}, 'json').fail(function() {
				setTimeout(updateData, 30000);
				
				for (var tID in topics) {
					$('#lastUpdated_'+tID).empty().text('Updated '+topics[tID].latestPoint.time.fromNow());
				}
				
			});
			
		}
		
		
		function loadTopics() {
			
			$.get('index.php?action=topics', function(data) {
				
				for (var d in data) {
					topics[d] = new Topic(data[d]);
					
					var cookiePeriod = Cookies.get('period');
					
					if (typeof cookiePeriod !== 'undefined') {
						topics[d].period = parseInt(cookiePeriod);
					}
					$('#period').val(topics[d].period).selectmenu('refresh');
				}
				
				$('#placeForCharts').empty();
				
				drawCharts();
				updateData();
				
			}, 'json').fail(function() {
				
				setTimeout(loadTopics, 30000);
			});
		}
		
		function changedPeriod() {
			
			Cookies.set('period', $('#period').val(), { expires: moment().add(1, 'year').toDate() });
			
			for (var t in topics) {
				topics[t].period = $('#period').val();
				//$('#minmax_'+t).text('Past '+topics[t].periodName())
				topics[t].redrawChart();
			}
			
		}
		
		$(function(){
			
			loadTopics();
		});
		
		</script>
		
		<div data-role="page">
		
			<div data-role="header" role="banner">
				<h1 class="ui-title optional" role="heading">Weather Station</h1>
				
				<div class="ui-btn-right">
				<select id="period" onchange="changedPeriod();">
					<option value="86400">View: 24hrs</option>
					<option value="172800">View: 48hrs</option>
					<option value="604800">View: 1 week</option>
				</select>
				</div>
				
			</div>
			
		<div class="container">
			<div class="row" id="placeForCharts"></div>
			
			<!--<div class="compass">
				<div class="direction">
					<p>NE<span>10 kmh</span></p>
				</div>
				<div class="arrow now ne"></div>
				<div class="arrow was n"></div>
			</div>-->
			
		</div>
			
		</div>
	</body>
	
</html>