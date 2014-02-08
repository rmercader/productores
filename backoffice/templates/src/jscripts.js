//::: Coloca al elemento de di aQuien la clase clase :::::::::::::::::
function cc(aQuien,clase){
	if(!document.layers){	//No es NN
		elemento=document.getElementById(aQuien)
		elemento.className=clase
	}
}	

//::: Coloca al elemento de di aQuien la clase clase :::::::::::::::::
function cc_swap(aQuien,clase,clase1){
	if(!document.layers){	//No es NN
		elemento=document.getElementById(aQuien);
		ClaseAct = elemento.className;
		if (ClaseAct == clase){
			elemento.className=clase1;
		}
		else{
			elemento.className=clase;
		}
	}	
}

function MostrarSpinner(SPINNER){
	var spinner = document.getElementById(SPINNER);
	spinner.innerHTML = '<img src="templates/img/spinner.gif"/>';
}

function TestForEnter(event){
	if (event.keyCode == 13) 
	{
		event.cancelBubble = true;
		event.returnValue = false;
  }
}

function pulsar(e) {
  tecla = (document.all) ? e.keyCode :e.which;
  return (tecla!=13);
}