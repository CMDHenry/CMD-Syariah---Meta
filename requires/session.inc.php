<?

if (!ini_get("session.auto_start")){
//	ini_set("session.cookie_lifetime",300);
    session_name("capella");
    session_start();
}
?>