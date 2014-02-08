var xmlDoc = null ;	// El objeto url
valor_load = null ;	// El valor que se consigue
var lock_valor_load = false; // Para que no se sobreescriba valor_load
  
function load(url) {
	if (typeof window.ActiveXObject != 'undefined' ) {
		xmlDoc = new ActiveXObject("Microsoft.XMLHTTP");
		xmlDoc.onreadystatechange = process ;
		}
	else {
		xmlDoc = new XMLHttpRequest();
		xmlDoc.onload = process ;
		}
	xmlDoc.open( "GET", url, true );
	xmlDoc.send( null );
	}
  
function process() {
	if (xmlDoc.readyState == 4 ){
		if(xmlDoc.status==200){
			valor_load=xmlDoc.responseText;
		}
	}
	else{
		return;
	}
}

function cargar_php(TBL,CBUS,CRES,VBUS,FORMU,CID,CNOM){

	// VARIABLES
	// TBL - Tabla en d�de se realiza la bsqueda
	// CBUS - Campo id
	// CRES - Campo a devolder
	// VBUS - Valor del id a buscar�	
	// FORMU - Formulario en d�de se trabaja
	// CID - Campo que contiente el id
	// CNOM - Campo a cargar el resultado	

	
	// controla que no venga vac�
	if(VBUS==""){
		return false;
		}
	valor_load = null;
	// Se llama a la consulta de la pagina
	load("get_datojs.php?TBL="+TBL+"&CBUS="+CBUS+"&CRES="+CRES+"&VBUS="+VBUS);

		
	// Se realiza una pequeña espera
	FORMU2=FORMU;
	CID2=CID;
	CNOM2=CNOM;
	document.forms[FORMU].elements[CNOM].value="cargando...";
	window.setTimeout("cargar_lento(FORMU2,CID2,CNOM2);",1000);
		
	}
	
function cargar_lento(FORMU,CID,CNOM){

	if(valor_load == "ERROR"){
		document.forms[FORMU].elements[CID].value="";
		document.forms[FORMU].elements[CNOM].value="";
		alert("No se recibio ningun valor. El codigo ingresado no corresponde a ningun registro.");
	}
	else if(valor_load == "" || valor_load == null){
			FORMU2 = FORMU;
			CID2 = CID;
			CNOM2 = CNOM;
			document.forms[FORMU].elements[CNOM].value="cargando...";
			window.setTimeout("cargar_lento(FORMU2,CID2,CNOM2);",1000);
	}	
		else{
			document.forms[FORMU].elements[CNOM].value=valor_load;
		}	
}