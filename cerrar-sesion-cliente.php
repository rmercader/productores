<?PHP

session_start();
setcookie(COOKIE_ID_CLIENTE, "");
$_SESSION[CREDENCIALES_CLIENTE] = "";
header("Location: ingreso-clientes.php");
exit();

?>