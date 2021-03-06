<?php
session_start();

date_default_timezone_set('Pacific/Auckland');

//peek ahead to see if we need to open the database in rw
$dbrw = true;
if (array_key_exists('action', $_GET) && $_GET['action'] == 'saveTopic') {
	$dbrw = true;
}

require_once('includes/database.inc.php');
require_once('includes/Topic.php');
require_once 'includes/DataPoint.php';
require_once('includes/User.php');

if (array_key_exists('action', $_GET)) {
	
	header('Content-Type: application/json');
	
	if ($_GET['action'] == 'login') {

		$user = User::verify($_POST['username'], $_POST['password']);

		if ($user) {

			$_SESSION['user'] = $user;
			$_SESSION['loggedIn'] = true;

			exit(json_encode([
				'result' => 'success',
				'user' => $user
			]));
		} else {

			$_SESSION['user'] = false;
			$_SESSION['loggedIn'] = false;

			exit(json_encode(['result' => 'fail']));
		}
	} else if ($_GET['action'] == 'checkLogin') {

		if ($_SESSION['loggedIn'] === true) {

			exit(json_encode([
				'loggedIn' => true,
				'user' => $_SESSION['user']
			]));
		} else {
			exit(json_encode([
				'loggedIn' => false
			]));
		}
	} else if ($_GET['action'] == 'logout') {
		
		$_SESSION['user'] = false;
		$_SESSION['loggedIn'] = false;
		
		exit(json_encode([
			'result' => 'success'
		]));
		
	} else if ($_GET['action'] == 'datapoints') {

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
	} else if ($_GET['action'] == 'saveTopic') {
		
		exit(json_encode(Topic::saveTopic($_POST), JSON_PRETTY_PRINT));
		
	} else if ($_GET['action'] == 'saveView') {
		
		exit(json_encode(View::saveView($_POST), JSON_PRETTY_PRINT));
		
	}
}
?>

<html>

	<head>
		<title><?php echo STATIONNAME; ?></title>

		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">

		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="mobile-web-app-capable" content="yes">

		<!--<link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />-->

		<link href="https://fonts.googleapis.com/css?family=Roboto:300" rel="stylesheet">

		<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
		<!--<script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>-->

		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="css/bootstrap.min.css">

		<!-- Optional theme -->

		<!-- Latest compiled and minified JavaScript-->
		<script src="js/bootstrap.min.js"></script>
		
		
		
		<link rel="stylesheet" href="css/bootstrap-theme.min.css">
		
		<link href="css/style.css" rel=stylesheet type=text/css />

		<script src="js/js.cookie.js"></script>
		
		<script src="js/formHelpers.js"></script>

		<script src="js/moment-with-locales.min.js" type="text/javascript"></script>
		<script src="js/moment-timezone-with-data.min.js" type="text/javascript"></script>

		<script src="js/Chart.js" type="text/javascript"></script>
		<script src="js/Chart.Scatter.js" type="text/javascript"></script>

		<script src="https://code.highcharts.com/highcharts.js"></script>
		<!--<script src="https://code.highcharts.com/modules/exporting.js"></script>-->
		<script src="https://code.highcharts.com/highcharts-more.js"></script>
		<script src="https://code.highcharts.com/modules/solid-gauge.js"></script>

		<script src="js/Auth.js" type="text/javascript"></script>
		<script src="js/Topic.js" type="text/javascript"></script>
		<script src="js/GaugeController.js" type="text/javascript"></script>
		<script src="js/ChartController.js" type="text/javascript"></script>
		<script src="js/manageTopics.js" type="text/javascript"></script>
		<script src="js/manageViews.js" type="text/javascript"></script>


	</head>

	<body>

		<script type="text/javascript">

			var auth = new Auth();

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
				}, function (data) {

					mostRecent = moment().unix();
					var oldest = moment().subtract(period, 'seconds');

					for (var tID in data) {

						topics[tID].addPoints(data[tID]);
						topics[tID].removeOldPoints();
						
						if (topics[tID].latestPoint != null) {
							topics[tID].mostRecent = topics[tID].latestPoint.time.unix();
						} else {
							topics[tID].mostRecent = null;
						}
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

				}, 'json').fail(function () {
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

				$.get('index.php?action=topics', function (data) {

					for (var d in data) {
						topics[d] = new Topic(data[d]);
					}

					$('#placeForCharts').empty();

					//drawCharts();
					//updateData();

				}, 'json').fail(function () {

					setTimeout(loadTopics, 30000);
				});
			}

			var periods = {
				86400: '24hrs',
				172800: '48hrs',
				604800: '1 week'
			};

			function changedPeriod(newPeriod) {

				Cookies.set('period', newPeriod, {expires: moment().add(1, 'year').toDate()});

				period = parseInt(newPeriod);

				for (var c in chartControllers) {
					chartControllers[c].update();
					chartControllers[c].redrawChart();
				}

				$('#periodLabel').empty().text(periods[period]);

			}

			function loadViews() {

				$.get('index.php?action=views', function (data) {

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

				}, 'json').fail(function () {

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
				var order = {};
				for (var t in view.viewTopics) {
					order[view.viewTopics[t].order] = t;
				}

				for (var o in order) {
					t = order[o];
					topicIDs.push(view.viewTopics[t].topicID);

					if (view.viewTopics[t].gauge) {
						//make some new gauges
						var containerID = 'gauge_' + view.viewTopics[t].topicID;
						$('#placeForGauges').append(
								$('<div>').attr('id', containerID).addClass('gauge')
								);

						var gc = new GaugeController(view.viewTopics[t], containerID);
						gc.draw();

						gaugeControllers.push(gc);
					}

					//add a chart if thats what they want
					if (view.viewTopics[t].chart) {

						var cc = new ChartController(view.viewTopics[t]);
						cc.draw();

						chartControllers.push(cc);
					}

				}

			}

			function round(value, decimals) {
				return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
			}

			auth.onLogin = function () {
				$('#nav-login-button').hide();
				$('#nav-manage-button').show();
				$('#loginModal').modal('hide');
				
				$('#loggedInLabel').text('Logged in as ' + auth.user.username);
				
			}

			auth.onLogout = function () {
				$('#nav-login-button').show();
				$('#nav-manage-button').hide();
			}
			
			$('#topicsModal').on('show.bs.modal', function() {
				drawTopicsTable();
			});
			
			$(function () {

				auth.checkLogin();

				var cookiePeriod = Cookies.get('period');

				if (typeof cookiePeriod !== 'undefined') {
					period = parseInt(cookiePeriod);
				}

				changedPeriod(period);

				loadTopics();
				loadViews();
				updateData();
			});

		</script>

		<nav class="navbar navbar-default navbar-inverse">
			<div class="container">

				<div class="navbar-header">

					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-navbar" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>

					<a class="navbar-brand" target="#">Weather Station</a>



				</div>

				<div id="main-navbar" class="collapse navbar-collapse">

					<ul class="nav navbar-nav">
						<li class="dropdown" style="display: none;">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"><span class="glyphicon glyphicon-th navbar-icon"></span><span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li class="dropdown-header" id="viewMenuHeader">Views</li>

								<li role="separator" class="divider"></li>
								<li><a href="#">Manual data entry</a></li>
							</ul>

						</li>
					</ul>

					<ul class="nav navbar-nav navbar-right">
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button">View: <span id="periodLabel"></span><span class="caret"></span>
							</a>
							<ul class="dropdown-menu">
								<li>
									<a href="#" onclick="changedPeriod(86400);">24 hours</a>
								</li>
								<li>
									<a href="#" onclick="changedPeriod(172800);">48 hours</a>
								</li>
								<li>
									<a href="#" onclick="changedPeriod(604800);">1 week</a>
								</li>
							</ul>
						</li>
						<li id="nav-login-button" style="/*display: none;*/">
							<a href="#" data-toggle="modal" data-target="#loginModal" role="button"><span class="glyphicon glyphicon-user navbar-icon"></span></a>
						</li>
						<li class="dropdown" id="nav-manage-button" style="/*display: none;*/">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"><span class="glyphicon glyphicon-user navbar-icon"></span><span class="caret"></span></a>
							<ul class="dropdown-menu">
								<li><a href="#" data-toggle="modal" data-target="#topicsModal" role="button" onclick="drawTopicsTable();">Manage topics</a></li>
								<li><a href="#" data-toggle="modal" data-target="#viewsModal" role="button" onclick="fillViewsModal();">Manage views</a></li>
								
								<li role="separator" class="divider"></li>
								<li class="dropdown-header" id="loggedInLabel"></li>
								<li><a href="#" onclick="auth.logout();">Logout</a></li>
							</ul>
						</li>
					</ul>

				</div>
			</div>
		</nav>

		<div class="container">

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

		<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="loginLabel">Login</h4>
					</div>
					<div class="modal-body">
						<form>
							<div class="form-group">
								<label for="inputUsername">Username</label>
								<input type="text" class="form-control" id="inputUsername" placeholder="Username">
							</div>
							<div class="form-group">
								<label for="inputPassword">Password</label>
								<input type="password" class="form-control" id="inputPassword" placeholder="Password">
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-primary" onclick="auth.login($('#inputUsername').val(), $('#inputPassword').val());">Login</button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade" id="topicsModal" tabindex="-1" role="dialog" aria-labelledBy="topicsLabel">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="topicsLabel">Manage Topics</h4>
					</div>
					<div class="modal-body">
						
						<div id="placeForTopicsTable"></div>
						
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade" id="viewsModal" tabindex="-1" role="dialog" aria-labelledBy="viewsLabel">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="viewsLabel">Manage Views</h4>
					</div>
					<div class="modal-body">
						
						<div id="placeForViews">
							
							View: <select name="viewToEdit" id="viewToEdit" onchange="editView(this.value);">
							</select>
							
						</div>
						
						<div id="placeForViewsForm"></div>
						
					</div>
				</div>
			</div>
		</div>
		
	</body>

</html>