<?php
	$weekdays = array(1 => 'Monday',2 => 'Tuesday',3 => 'Wednesday',4 => 'Thursday',5 => 'Friday',6 => 'Saturday',7 => 'Sunday');
	
	$routeNms = array("All Routes"); // Route names
	$routeIds = array("*"); // Route ids
	
	$stopIds = array("0"); // Stop ids
	$stopRts = array("0"); // Stop routes
	$stopNms = array("All Stops"); // Stop names
	$stopLat = array("0"); // Stop latitude locations
	$stopLon = array("0"); // Stop longitude locations
	
	$scheduleIds = []; // Schedule ids
	$scheduleRts = []; // Schedule routes
	$scheduleTms = []; // Schedule times
	
	$selectedRoutes = [];
	$selectedStops = [];
	
	// Server info
	$servername = "******************************";
	$username = "centroWriter";
	$password = "centroWriter";
	$dbname = "Centro";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	
	// Check connection
	if ($conn->connect_error) {
		echo "Error: Could not connect to database!<p><p>";
	}
	else {
		// Get a list of available routes
		$sql = "SELECT id, rt, rtnm FROM routes";
		$routesResult = $conn->query($sql);
		
		// Make sure we got the route data
		if ($routesResult->num_rows > 0) {
			// Save every route
			$rtIndex = 1;
			while($routeRow = $routesResult->fetch_assoc()) {
				array_push($routeNms, $routeRow["rt"] . ": " . $routeRow["rtnm"]);
				array_push($routeIds, $routeRow["rt"]);
				$rtIndex++;
			}
		}
		
		// Get a list of available stops
		$sql = "SELECT stopid, rtId, stpnm, lat, lon FROM stops";
		$stopsResult = $conn->query($sql);
		
		// Make sure we got the stop data
		if ($stopsResult->num_rows > 0) {
			// Save every stop
			$stIndex = 1;
			while($stopRow = $stopsResult->fetch_assoc()) {
				// Ignore any duplicates (database has duplicate stops for some reason)
				$stId = $stopRow["stopid"];
				if (!in_array($stId, $stopIds)) {
					array_push($stopIds, $stId);
					array_push($stopRts, $stopRow["rtId"]);
					array_push($stopNms, $stopRow["stpnm"]);
					array_push($stopLat, $stopRow["lat"]);
					array_push($stopLon, $stopRow["lon"]);
					$stIndex++;
				}
			}
		}
		
		
		// Get a list of expected schedule times
		$sql = "SELECT rtId, stopid, scheduledTime FROM busSchedule";
		$scheduleResult = $conn->query($sql);
		
		// Make sure we got the schedule data
		if ($scheduleResult->num_rows > 0) {
			// Save every stop
			$scIndex = 1;
			while($scheduleRow = $scheduleResult->fetch_assoc()) {
				array_push($scheduleIds, $scheduleRow["stopid"]);
				array_push($scheduleRts, $scheduleRow["rtId"]);
				array_push($scheduleTms, $scheduleRow["scheduledTime"]);
				$scIndex++;
			}
		}
?>

<!DOCTYPE html>

<html>
	<head>
		<title>Centro Bus Performance</title>
		<link rel="stylesheet" type="text/css" href="./common.css?rnd=<?php echo rand(); ?>">
		<script type="text/javascript" src="func.js?rnd=<?php echo rand(); ?>"></script>
	</head>
	<body>
		<div class="header">
		<h1>Centro Bus Performance Monitor</h1>
		</div>
		<div class="filter">
			<h2>Search Filter</h2>
			<br>
			<form id="filter_form" action="./index.php">
				From Date: 
				<input class="date_input" id="date_from" type="date" name="date_from" min="2018-11-14" max="<?php echo date('Y-m-d'); ?>" value="<?php
		// Check the input date from field
		$date_from = filter_input(INPUT_GET, "date_from", FILTER_SANITIZE_STRING);
		if ($date_from != null && $date_from != false)
			echo $date_from;
?>">
				To Date: 
				<input class="date_input" id="date_to" type="date" name="date_to" min="2018-11-14" max="<?php echo date('Y-m-d'); ?>" value="<?php
		// Check the input date to field
		$date_to = filter_input(INPUT_GET, "date_to", FILTER_SANITIZE_STRING);
		if ($date_to != null && $date_to != false)
			echo $date_to;
?>">
				<hr>
				Selected Routes: 
				<p>
				<div id="selected_routes">
<?php
		// Check each route filter selection
		$rtIndex = 0;
		$route = filter_input(INPUT_GET, "route" . $rtIndex, FILTER_SANITIZE_NUMBER_INT);
		while($route != null && ($route != false || $route == "0")) {
			
			// Add the route selection to the selected list
			echo "<div class=\"route_selection\" onclick=\"deselect_route(this)\" value=\"" . $route . "\">" . $routeNms[$route] . "</div>";
			array_push($selectedRoutes, $route);
			
			$rtIndex++;
			$route = filter_input(INPUT_GET, "route" . $rtIndex, FILTER_SANITIZE_NUMBER_INT);
		}
?>				
				</div>
				<select id = "route_list" name="route" onchange="select_route()"
<?php
		if (in_array("0", $selectedRoutes))
			echo " style=\"visibility: hidden; display: none;\"";
?>
				>
					<option selected disabled value = "-1">Select a Route</option>
<?php
		// Add an option for every route
		$rtIndex = 0;
		foreach ($routeNms as $rtNm) {
			if (!in_array($rtIndex, $selectedRoutes))
				echo "<option value = \"" . $rtIndex . "\">" . $rtNm . "</option>";
			else
				echo "<option value = \"" . $rtIndex . "\" style=\"visibility: hidden; display: none;\">" . $rtNm . "</option>";
			$rtIndex++;
		}
?>

				</select>
				<hr>
				Selected Stops: 
				<p>
				<div id="selected_stops">
<?php
		// Check each stop filter selection
		$stIndex = 0;
		$stop = filter_input(INPUT_GET, "stop" . $stIndex, FILTER_SANITIZE_NUMBER_INT);
		while($stop != null && ($stop != false || $stop == "0")) {
			
			// Find the index of the stop id
			$stLoc = array_search($stop, $stopIds);
			
			// Add the stop selection to the selected list
			echo "<div class=\"stop_selection\" value=\"" . $stop . "\" data-rtid=\"" . $stopRts[$stLoc] . "\" onclick=\"deselect_stop(this)\">" . $stopNms[$stLoc] . "</div>";
			array_push($selectedStops, $stop);
			
			$stIndex++;
			$stop = filter_input(INPUT_GET, "stop" . $stIndex, FILTER_SANITIZE_NUMBER_INT);
		}
		
?>	
				</div>
				<select id = "stop_list" name="stop" onchange="select_stop()"
<?php
		if (in_array("0", $selectedStops))
			echo " style=\"visibility: hidden; display: none;\"";
?>
				>
					<option selected disabled value = "-1">Select a Stop</option>
					
<?php
		// Add an option for every stop
		$stIndex = 0;
		foreach ($stopNms as $stNm) {
			if ($stIndex == 0 || ((in_array("0", $selectedRoutes) || in_array($stopRts[$stIndex], $selectedRoutes)) && !in_array($stopIds[$stIndex], $selectedStops)))
				echo "<option value = \"" . $stopIds[$stIndex] . "\" data-rtid=\"" . $stopRts[$stIndex] . "\" style=\"visibility: visible; display: inline;\">" . $stNm . "</option>";
			else
				echo "<option value = \"" . $stopIds[$stIndex] . "\" data-rtid=\"" . $stopRts[$stIndex] . "\" style=\"visibility: hidden; display: none;\">" . $stNm . "</option>";
			$stIndex++;
		}
?>
				</select>
				<input id="btn_submit" type="button" onclick="submit_filter()" value="Submit Filter">
			</form>
		</div>
		<div class="data">
			<h2>Route Data</h2>
			<p>
<?php
		
		$filterReady = true;
		
		// Make sure that a date range was given
		if ($date_from == null || $date_from == false || $date_to == null || $date_to == false || $date_from > $date_to) {
			echo "<div class=\"data_field0\">Please select a valid date range!</div>";
			$filterReady = false;
		}
		
		// Make sure that at least one route was selected
		if (count($selectedRoutes) < 1) {
			echo "<div class=\"data_field0\">Please select at least one route!</div>";
			$filterReady = false;
		}
		
		// Make sure that at least one stop was selected
		if (count($selectedStops) < 1) {
			echo "<div class=\"data_field0\">Please select at least one stop!</div>";
			$filterReady = false;
		}
		
		// Process data only if all filters were selected
		if ($filterReady) {
			// Deconstruct the input dates
			$df = date_parse_from_format("Y-m-d", $date_from);
			$dt = date_parse_from_format("Y-m-d", $date_to);
			
			if (strlen($df["day"]) == 1)
				$df["day"] = "0".$df["day"];
			if (strlen($df["month"]) == 1)
				$df["month"] = "0".$df["month"];
			if (strlen($dt["day"]) == 1)
				$dt["day"] = "0".$dt["day"];
			if (strlen($df["month"]) == 1)
				$dt["month"] = "0".$dt["month"];
			
			// Build a selection string for the selected routes
			if ($selectedRoutes[0] == "0")
				$rtSelect = "";
			else {
				$rtSelect = "rt IN ('" . $routeIds[$selectedRoutes[0]] . "'";
				$rtSelectIndex = 1;
				while($rtSelectIndex < count($selectedRoutes)) {
					$rtSelect = $rtSelect . ",'" . $routeIds[$selectedRoutes[$rtSelectIndex]] . "'";
					$rtSelectIndex++;
				}
				$rtSelect = $rtSelect . ") AND ";
			}
			
			// Get a list of history between the date range
			$sql = "SELECT DISTINCT tmstmp, lat, lon, rt, des FROM vehicles WHERE " . $rtSelect . "(tmstmp BETWEEN '" . $df["year"] . $df["month"] . $df["day"] . " 00:00:00' AND '" . $dt["year"] . $dt["month"] . $dt["day"] . " 23:59:00')";
			$historyResult = $conn->query($sql);
			
			// Make sure we got the history data
			if ($historyResult->num_rows > 0) {
				// Save every history snapshot
				$hsIndex = 1;
				$stLast = "";
				$stHighlight = true;
				while($historyRow = $historyResult->fetch_assoc()) {
					
					// Search through all stops to see if the bus was close to any
					$stError = 0.00005; // within 5 meters
					$stIndex = 1;
					while($stIndex < count($stopIds)) {
						if ($stLast != $stopNms[$stIndex] && $historyRow["lat"] > $stopLat[$stIndex] - $stError && $historyRow["lat"] < $stopLat[$stIndex] + $stError && $historyRow["lon"] > $stopLon[$stIndex] - $stError && $historyRow["lon"] < $stopLon[$stIndex] + $stError) {
							if ($selectedStops[0] == "0" || in_array($stopIds[$stIndex], $selectedStops)) {
								// Format the timestamp
								$tmstmp = substr($historyRow["tmstmp"],0,4) . "-" . substr($historyRow["tmstmp"],4,2) . "-" . substr($historyRow["tmstmp"],6,2) . " @ " . substr($historyRow["tmstmp"],8,9);
								
								if ($stHighlight)
									echo "<div class=\"data_field1\">" . $tmstmp . " : " . $stopNms[$stIndex] . " on route " . $historyRow["des"];
								else
									echo "<div class=\"data_field2\">" . $tmstmp . " : " . $stopNms[$stIndex] . " on route " . $historyRow["des"];
								
								// Figure out what day of the week this happened
								$weekday = date('w', strtotime(substr($historyRow["tmstmp"],0,4) . substr($historyRow["tmstmp"],4,2) . substr($historyRow["tmstmp"],6,2)));
								echo " on a " . $weekdays[$weekday] . "</div>";
								
								$stHighlight = !$stHighlight;
							}
							$stLast = $stopNms[$stIndex];
						}
						$stIndex++;
					}
					
					$hsIndex++;
				}
			} else {
				echo "<div class=\"data_field0\">There is no data that matches your filter... Please choose another date range, route, or stop to filter!</div>";
			}
			
		}
?>
		</div>
	</body>
</html>
<?php
	}
	
	$conn->close();
?>

