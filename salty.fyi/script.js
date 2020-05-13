// window.onerror = function(message, url, lineNumber) { return true; };

document.addEventListener("DOMContentLoaded", function(event) { 
	
	/*
	var timezone_offset_minutes = new Date().getTimezoneOffset();
	timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
	
	var element = document.querySelector('#original');
	var text = element.innerText || element.textContent;
	element.innerHTML = text;

	if(original == null) {
		return false;
	}
	
	fetch('/api/zone/' + timezone_offset_minutes + '/' + original.innerHTML)
		.then(function (response) {
			return response.json();
		})
		.then(function (data) {
			if(original.innerHTML == data) {
				var elem = document.querySelector('#is');
				elem.innerHTML = 'This is the same time in your local time zone (determined from your computer’s clock).';
				var elem = document.querySelector('#prompt');
				elem.innerHTML = 'Check another time zone:';
				var elem = document.querySelector('#converted');
				elem.parentNode.removeChild(elem);
			}
			else {
				var elem = document.querySelector('#converted');
				elem.innerHTML = data;
				var elem = document.querySelector('#prompt');
				elem.innerHTML = 'Check another time zone:';
			}
		})
		.catch(function (err) {
			//console.log("Something went wrong!", err);
		});
	*/
});
