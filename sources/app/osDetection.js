
	var os = navigator.platform;
	if(os.indexOf("Linux") != -1){

		var link = document.createElement("link");
		link.rel = "stylesheet";
		link.setAttribute("type", "text/css");
		link.setAttribute("href", "./css/androidMain.css");		
		document.head.appendChild(link);
	}
	else{
		var link = document.createElement("link");
		link.rel = "stylesheet";
		link.setAttribute("type", "text/css");
		link.setAttribute("href", "./css/main.css");	
		document.head.appendChild(link);

	}
