<?PHP

// Registro session
session_start();

// Destruyo sesion
if (isset($_SESSION["activa"])) {
   // Borro Variables
   $_SESSION = array();
   session_destroy();
}

// Redirecciono
header("Location: index.php");
exit();
?>