var counter = 0;
var counter2 = 0;
var lienzo_num = 0;


function duplicar(elemento,cantidad){

	lienzos=arguments.length-2;			// la cantidad de divs a colocar campos clonados
	cantidad_total=cantidad*lienzos;		// cantidad que se permite clonar

	if(counter==cantidad_total){			// ¿Está permitido?
		return false;
		}
	
	counter++;
	
	var campo = document.createElement("input");
	campo.name="codigo_"+counter;
	campo.type="text";
	campo.size="15";

	// se controla en lienzo estÃ¡
	if(counter2==cantidad){
		counter2=0;			// se resetea el contador
		lienzo_num++;		// se cambia de div
		}
	counter2++;
	
	var insertHere = document.getElementById(arguments[lienzo_num+2]);
	insertHere.parentNode.insertBefore(campo,insertHere);
	campo.focus();
	}
