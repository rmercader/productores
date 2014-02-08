<?PHP
/*--------------------------------------------------------------------------
   Archivo: nyiLIB.php
   Descripcion: Funciones varias
   Fecha de Creaci�: 20/11/2004
   Ultima actualizacion: 18/12/2004

   Este archivo es parte del FrameWork nyi
   Copyright (c) 2004 Pablo Erartes pejota@internet.com.uy

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
  --------------------------------------------------------------------------*/

/*--------------------------------------------------------------------------
  Funcion: GeneroClave($Tam,$Separador,$TamL,$TamN);
  Genera una clave y devuelve string
 --------------------------------------------------------------------------*/
 function GeneroClave($TamL=7,$TamN=2,$Separador='.'){
         // Caracteres
        $Letras  = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        $Numeros = array('0','1','2','3','4','5','6','7','8','9');

        // Entrevero
        srand((double)microtime()*1000000);
           shuffle($Letras);
           shuffle($Numeros);

        // Genero clave
        $Doy = '';
        // Letras
        if ($TamL > 0){
                for ($i = 0; $i < $TamL; $i++) $Doy .= $Letras[$i];
        }
        // Separador
        if (($TamL > 0) && ($TamN > 0) && ($Separador <> ''))
                $Doy .= $Separador;

        // Numeros
        if ($TamN > 0)
                for ($i = 0; $i < $TamN; $i++) $Doy .= $Numeros[$i];

        // Devuelvo
        return($Doy);
}

/*--------------------------------------------------------------------------
  Funcion: DiaLargo($Fecha)
  Devuelve : Lunes 1 de Noviembre de 2003
 --------------------------------------------------------------------------*/
function DiaLargo($Fecha=''){
   // Definicion
   $aMes = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo',
                 'Junio', 'Julio', 'Agosto', 'Setiembre',
                 'Octubre', 'Noviembre', 'Diciembre');
   $aDia = array('Domingo', 'Lunes', 'Martes', 'Mi&eacute;coles', 'Jueves', 'Viernes', 'S&aacute;bado');

   // Si es nulo
   if ($Fecha == '') {
      $DiaS = $aDia[date('w')];
      $Dia  = date('d');
      $Mes  = $aMes[date('m')-1];
      $Anio = date('Y');
   }
   else {
      $DiaS = $aDia[date('w',$Fecha)];
      $Dia  = date('d',$Fecha);
      $Mes  = $aMes[date('m',$Fecha)-1];
      $Anio = date('Y',$Fecha);
   }
   return("$DiaS, $Dia de $Mes de $Anio");
}

/*--------------------------------------------------------------------------
  Funcion: days_in_month($Anio,$Mes)
  Devuelve : Segun el año y el mes, cuantos dias tiene el mes.
 --------------------------------------------------------------------------*/
function days_in_month($Anio,$Mes){
	return(date("t", mktime(0,0,0,$Mes,1,$Anio)));
}
?>
