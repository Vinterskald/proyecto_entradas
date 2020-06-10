<?php 
    include_once 'app/bdd/Modelo.php';
    include_once 'app/bdd/usuarios.php';
    
    if(session_status() != PHP_SESSION_ACTIVE || session_status() == PHP_SESSION_NONE){
        session_start();
    }
    
    //Inicializo una instancia del modelo.
    Modelo::init();
    
    /*Desde aquí, como controlador e índice (el cual carga también la hoja de estilo
     *para la mayor parte de la vista), se maneja qué página debería estar activa según la información
     *actual sobre la misma; si no hay información, se cargará la página principal (equivalente a "home").
    */    
    
    //Inicio de sesión:
    if(isset($_REQUEST["username"]) && isset($_REQUEST["pass"])){
        if(!empty($_REQUEST["username"]) && !empty($_REQUEST["pass"])){
            $cuenta = $_REQUEST["username"];
            $contra = $_REQUEST["pass"];
            if(!Modelo::checkPass($cuenta, $contra) || !Modelo::existeCuenta($cuenta)){
                echo "
                     <script>
                        alert('Datos incorrectos.');
                     </script>
                ";
            }else{
                if(!Modelo::userGet($cuenta)){
                    echo "
                         <script>
                            alert('Error al recuperar datos del usuario.');
                         </script>
                    ";
                }else{
                    $userData = Modelo::userGet($cuenta);
                    $user [] = new usuarios($userData);
                    $_SESSION["usuario"] = $user;
                }
            }
        }else{
            echo "
                 <script>
                    alert('No has introducido nombre y/o contraseña.');
                 </script>
            ";
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
    	<meta charset="UTF-8">
    	<title>Ticket Finder - Proyecto Víctor Viloria Iberti 2DAW</title>
    	<link href="./web/css/estilos.css" rel="stylesheet" type="text/css"/>
        <script type="text/javascript" src="./web/js/jquery-3.5.1.js"></script>
    </head>
    <body>
    	<div id="interfaz">
    		<!-- Bloque del espacio de cabecera -->
    		<header>
    			<!--<img alt="Logo" src="img/logo1.png">-->
    			<h2 id="titulo">Ticket Finder</h2>
    			<h4 class="texto">Encuentra tu entrada en un par de clicks.</h4>
    			<!-- Bloque para la barra de navegación -->
    			<nav id="menu">
    				<ul>
    					<li><a href=""><button onClick="cargarContenido('principal')">Principal</button></a></li>
    					<li><a href=""><button onClick="cargarContenido('buscar')">Buscar eventos</button></a></li>
    					<li><a href=""><button onClick="cargarContenido('info')">Sobre Ticket Finder</button></a></li>
    					<?php
    					   if(!isset($_SESSION["usuario"])){
    					       echo "<li><a href='app/login.html'><button>Registrarse o acceder</button></a></li>";
    					   }else{
    					       echo "<li><a href=''><button onClick='logout()'>Bienvenido/a, ".$_SESSION["usuario"]["cuenta"]."</button></a></li>";
    					   }
    					?>
    				</ul>
    			</nav>
    		</header>
    		<br>
    		<!-- Bloque de contenido dinámico (AJAX) -->
    		<div id="contenido" class="contenedor">
    			<?php
    			     include_once("app/contenido/principal.html"); 
    			?>
    		</div>
    	</div>
    </body>
    <script type="text/javascript">
    	//Función para el resto de llamadas de AJAX:
    	function cargarContenido(pag){
    		//alert(pag);
    		var xhttp = new XMLHttpRequest();
    		xhttp.onreadystatechange = function(){
    			//alert(this.readyState);
    			//alert(this.status);
    			if(this.readyState == 4 && this.status == 200){
    				document.getElementById("contenido").innerHTML = this.responseText;
    			}
    		};
    		switch(pag){
    			case "principal":
    				xhttp.open("GET", "app/contenido/principal.html", true);
    				break;
    			case "buscar":
    				xhttp.open("GET", "app/contenido/buscar.php", true);
    				break;
    			case "info":
    				xhttp.open("GET", "app/contenido/info.html", true);
    				break;
    		}
    		xhttp.send();
    	}

    	function logout(){
        	window.location.href = "./app/contenido/logout.php";		
        }
    	//------------------------------------------------------------------------
    	//Bloque para la llamada a AJAX desde JQuery:
    	var request = $.ajax({
        		url: 'https://api.songkick.com/api/30.0/events/{event_id}.json?apikey={key}',
        		method: 'GET'
        	});

    	request.done(function(data){
        	alert(data);
        	if(data.count <= 0){
            	//
            }else{
            	for(var i = 0; i < data.results.length; i++){
            		//
            	}
            }
    	});

    	request.fail(function(error){
        	alert("Error: "+error);
        });
    </script>
</html>