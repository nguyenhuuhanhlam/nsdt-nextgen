function errorAlert(e){
	if(e.substring(0,3)=="{E}"||e.substring(0,3)=="{I}"){
		alert(e.substring(3,e.length));
		return false;
	}
	return true;
}

function currencyInput(cu){
}