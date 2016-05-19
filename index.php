<?php

date_default_timezone_set('Pacific/Auckland');

require_once('includes/database.inc.php');
require_once('includes/Topic.php');
require_once 'includes/DataPoint.php';

if (array_key_exists('action', $_GET)) {
	if ($_GET['action'] == 'datapoints') {

		$topicIDs = json_decode($_POST['topics']);

		$ret = [];

		foreach ($topicIDs as $tID) {

			$ret[$tID] = DataPoint::allForTopicSince($tID, $_POST['since']);

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
		<script src="js/jquery-1.11.2.min.js" type="text/javascript"></script>
		<script src="js/Chart.js" type="text/javascript"></script>
		<script src="js/Chart.Scatter.js" type="text/javascript"></script>
		<script src="js/moment-with-locales.min.js" type="text/javascript"></script>
		<script src="js/moment-timezone-with-data.min.js" type="text/javascript"></script>
		
	</head>
	
	<body style="font-family: sans-serif;">
		
		<script type="text/javascript">
		
		var topics = [];
		
		var period = 86400;
		
		var mostRecent = moment().unix() - period;
		
		var timezone = 'Pacific/Auckland';
		
		function drawCharts() {
			
			$('#placeForCharts').empty();
			
			for (var t in topics) {
				
				var $canvas = $('<canvas>').attr('id', 'canvas_'+topics[t].id).css({width: '400px', height: '300px'});
				
				$('#placeForCharts').append(
					$('<fieldset>').css({width: '600px', height: '300px'}).append(
						$('<legend>').css('text-align', 'center').text(topics[t].description),
						$('<table>').css('width', '100%').append(
							$('<tr>').append(
								$('<td>').css('width', '400px').append($canvas),
								$('<td>').css({
									'text-align': 'center',
									'vertical-align': 'top'
								}).append(
									$('<div>').css({
										'font-weight': 'bold'
									}).text('Current'),
									$('<div>').css('font-size', '150%').attr('id', 'current_'+t),
									$('<div>').css('color', '#858585').attr('id', 'lastUpdated_'+t)
								)
							)
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
				topicIDs.push(topics[t].id);
			}
			
			$.post('index.php?action=datapoints', {
				'topics': JSON.stringify(topicIDs),
				'since': mostRecent
			}, function(data) {
				
				mostRecent = moment().unix();
				var oldest = (moment().unix() - period)*1000;
				
				for (var tID in data) {
					
					topics[tID].removePointsOlderThan(oldest);
					topics[tID].addPoints(data[tID]);
					
					//append the new datapoints onto the topic's array
					/*for (var dp in data[tID]) {
						
						
						topics[tID].chart.datasets[0].addPoint(moment(data[tID][dp].time).toDate(), data[tID][dp].value);
						latestValue = data[tID][dp].value;
						topics[tID].latestTime = moment(data[tID][dp].time);
						
					}
					topics[tID].chart.update();//*/
					
					$('#current_'+tID).empty().text(topics[tID].latestPoint.value+topics[tID].units);
					$('#lastUpdated_'+tID).empty().text('Updated '+topics[tID].latestPoint.time.fromNow());
					
				}
				
				prunePointArrays();
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
				}
				
				$('#placeForCharts').empty();
				
				drawCharts();
				updateData();
				
			}, 'json').fail(function() {
				setTimeout(loadTopics, 30000);
			});
		}
		
		$(function(){
			loadTopics();
		});
		
		</script>
		
		<div id="placeForCharts"></div>
		
	</body>
	
</html>