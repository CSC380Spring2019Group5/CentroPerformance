function show_raw_data() {
	var raw = document.getElementById("raw_data");

	if (raw.style.visibility == "hidden") {
		raw.style.visibility = "visible";
		raw.style.display = "block";
		document.getElementById("btn_hide").innerHTML = "Hide Raw Data";
	} else {
		raw.style.visibility = "hidden";
		raw.style.display = "none";
		document.getElementById("btn_hide").innerHTML = "Show Raw Data";
	}
}

function select_route() {
	var dropdown = document.getElementById("route_list");
	var val = dropdown[dropdown.selectedIndex].getAttribute("value");
	
	// Create a new route selection
	var rt = document.createElement("div");
	rt.className = "route_selection";
	rt.innerHTML = dropdown[dropdown.selectedIndex].innerHTML;
	rt.setAttribute("value", val);
	rt.onclick = function() { deselect_route(rt); }
	
	document.getElementById("selected_routes").appendChild(rt);
	
	// Hide the selected dropdown element
	dropdown[dropdown.selectedIndex].style.visibility = "hidden";
	dropdown[dropdown.selectedIndex].style.display = "none";
	
	// Check if "All Routes" was selected
	if (val == 0) {
		// Hide the whole dropdown menu
		dropdown.style.visibility = "hidden";
		dropdown.style.display = "none";
		
		// Remove the list of selected routes except "All Routes"
		var list = document.getElementsByClassName("route_selection");
		for (var i=0; i<list.length; i++) {
			if (list[i].getAttribute("value") != 0) {
				// Reset the visibility of all dropdown elements that were selected
				if (dropdown.options[i].getAttribute("value") == list[i].getAttribute("value")) {
					for (var i = 0; i < dropdown.options.length; i++) {
						dropdown.options[i].style.visibility = "visible";
						dropdown.options[i].style.display = "inline";
					}
				}
				list[i].parentNode.removeChild(list[i]);
				i--;
			}
		}
		
		// Make all stop elements visible
		var stdropdown = document.getElementById("stop_list");
		for (var i = 0; i < stdropdown.options.length; i++) {
			stdropdown.options[i].style.visibility = "visible";
			stdropdown.options[i].style.display = "inline";
		}
	} else {
		// Make all stop elements with the selected route visible
		var stdropdown = document.getElementById("stop_list");
		for (var i = 0; i < stdropdown.options.length; i++) {
			if (stdropdown.options[i].dataset.rtid == rt.getAttribute("value")) {
				stdropdown.options[i].style.visibility = "visible";
				stdropdown.options[i].style.display = "inline";
			}
		}
	}
	
	// Deselect the dropdown
	dropdown.selectedIndex = 0;
}

function deselect_route(rt) {
	console.log("DESELECTING ROUTE: "+rt.getAttribute("value")+": "+rt.innerHTML);
	
	// Find the route element in the dropdown
	var dropdown = document.getElementById("route_list");
    for (var i = 0; i < dropdown.options.length; i++) {
		if (dropdown.options[i].getAttribute("value") == rt.getAttribute("value")) {
			// Make the route element visible
			dropdown.options[i].style.visibility = "visible";
			dropdown.options[i].style.display = "inline";
			break;
		}
    }
	
	// Make the whole dropdown menu visible
	dropdown.style.visibility = "visible";
	dropdown.style.display = "inline";
	
	// Remove any selected stops with that route
	var list = document.getElementsByClassName("stop_selection");
	for (var i=0; i<list.length; i++) {
		if (list[i].getAttribute("value") != 0 && (rt.getAttribute("value") == 0 || list[i].dataset.rtid == rt.getAttribute("value"))) {
			list[i].parentNode.removeChild(list[i]);
			i--;
		}
	}
	
	// Hide any stop elements in the dropdown with that route
	var stdropdown = document.getElementById("stop_list");
	for (var i = 0; i < stdropdown.options.length; i++) {
		if ((rt.getAttribute("value") == 0 || stdropdown.options[i].dataset.rtid == rt.getAttribute("value")) && stdropdown.options[i].getAttribute("value") != 0 && stdropdown.options[i].getAttribute("value") != -1) {
			stdropdown.options[i].style.visibility = "hidden";
			stdropdown.options[i].style.display = "none";
		}
	}
	
	// Remove the route from the selected routes list
	rt.parentNode.removeChild(rt);
}

function select_stop() {
	var dropdown = document.getElementById("stop_list");
	var val = dropdown[dropdown.selectedIndex].getAttribute("value");
	
	// Create a new stop selection
	var st = document.createElement("div");
	st.className = "stop_selection";
	st.innerHTML = dropdown[dropdown.selectedIndex].innerHTML;
	st.setAttribute("value", val);
	st.dataset.rtid = dropdown[dropdown.selectedIndex].dataset.rtid;
	st.onclick = function() { deselect_stop(st); }
	
	document.getElementById("selected_stops").appendChild(st);
	
	// Hide the selected dropdown element
	dropdown[dropdown.selectedIndex].style.visibility = "hidden";
	dropdown[dropdown.selectedIndex].style.display = "none";
	
	// Check if "All Stops" was selected
	if (val == 0) {
		// Hide the whole dropdown menu
		dropdown.style.visibility = "hidden";
		dropdown.style.display = "none";
		
		// Remove the list of selected stops except "All Stops"
		var list = document.getElementsByClassName("stop_selection");
		for (var i=0; i<list.length; i++) {
			if (list[i].getAttribute("value") != 0) {
				// Reset the visibility of all dropdown elements that were selected
				if (dropdown.options[i].getAttribute("value") == list[i].getAttribute("value")) {
					for (var i = 0; i < dropdown.options.length; i++) {
						dropdown.options[i].style.visibility = "visible";
						dropdown.options[i].style.display = "inline";
					}
				}
				list[i].parentNode.removeChild(list[i]);
				i--;
			}
		}
		
		
	}
	
	// Deselect the dropdown
	dropdown.selectedIndex = 0;
}

function deselect_stop(st) {
	// Find the stop element in the dropdown
	var dropdown = document.getElementById("stop_list");
    for (var i = 0; i < dropdown.options.length; i++) {
		if (dropdown.options[i].getAttribute("value") == st.getAttribute("value")) {
			// Make the stop element visible
			dropdown.options[i].style.visibility = "visible";
			dropdown.options[i].style.display = "inline";
			break;
		}
    }
	
	// Make the whole dropdown menu visible
	dropdown.style.visibility = "visible";
	dropdown.style.display = "inline";
	
	// Remove the stop from the selected stops list
	st.parentNode.removeChild(st);
}

function submit_filter() {
	// Create a new form to send
	const form = document.createElement('form');
	form.method = "get";
	form.action = "./index.php";
	
	// Create a date from input
	const dateFromField = document.createElement('input');
	dateFromField.type = 'hidden';
	dateFromField.name = "date_from";
	dateFromField.value = document.getElementById("date_from").value;
	form.appendChild(dateFromField);
	
	// Create a date to input
	const dateToField = document.createElement('input');
	dateToField.type = 'hidden';
	dateToField.name = "date_to";
	dateToField.value = document.getElementById("date_to").value;
	form.appendChild(dateToField);
	
	// Create a route input for every selected route
	var list = document.getElementsByClassName("route_selection");
	for (var i=0; i<list.length; i++) {
		var routeField = document.createElement('input');
		routeField.type = 'hidden';
		routeField.name = "route"+i;
		routeField.value = list[i].getAttribute("value");
		form.appendChild(routeField);
	}
	
	// Create a stop input for every selected stop
	var list = document.getElementsByClassName("stop_selection");
	for (var i=0; i<list.length; i++) {
		var stopField = document.createElement('input');
		stopField.type = 'hidden';
		stopField.name = "stop"+i;
		stopField.value = list[i].getAttribute("value");
		form.appendChild(stopField);
	}

	// Submit the form
	document.body.appendChild(form);
	form.submit();
}