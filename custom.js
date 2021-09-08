function castMedia(mediaNumber) {
	var filterQueryString = location.search.replace("?","&");
	
	var seekTime = "";
	var enc = false;
	var seekInputInputs = $('.seekInput');
	for(var i=0; i<seekInputInputs.length && !enc; i++){
		if (seekInputInputs[i].value && seekInputInputs[i].value != '00:00'){
			seekTime = seekInputInputs[i].value;
			enc = true;
		}
	}

	var nextLocation = 'index.php?cast='+mediaNumber;

	if (seekTime && seekTime != ''){
		nextLocation += '&seekTime='+seekTime+':00';
	}

	location.href = nextLocation + filterQueryString;
}

function multiDeleteMedia() {
	var filterQueryString = location.search.replace("?","&");
	
	var checkboxes = $("input[type='checkbox']");

	var multiDeleteIndex = "";
	for (var i = 0; i < checkboxes.length; i++) {
		if (checkboxes[i].checked) {
			multiDeleteIndex += i + ",";
		}
	}
	multiDeleteIndex = multiDeleteIndex.substring(0, multiDeleteIndex.length-1);
	
	location.href = 'index.php?multidelete=' + multiDeleteIndex + filterQueryString;
}

function filterContent(){
	var filterData = $("#textoBuscador").val();
	location.href = 'index.php?filter=' + filterData;
}

$(document).ready(function(){
	$("#textoBuscador").keyup(function(e){
		if (13 == e.keyCode) {
			filterContent();
		}
	});
});