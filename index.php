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
					//print_r($userData);
                    $user = new usuarios($userData);
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
        <script type="text/javascript">
          	//Función para llamadas de AJAX a los archivos de contenido:
        	function cargarContenido(pag){
        		document.getElementById("busqueda").style.display="none";
            	document.getElementById("contenido").style.display="block";
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
        			/*case "buscar":
        				xhttp.open("GET", "app/contenido/buscar.html", true);
        				break;*/
        			case "info":
        				xhttp.open("GET", "app/contenido/info.html", true);
        				break;
        		}
        		xhttp.send();
        	}
            function VerCapaBuscar(){
            	document.getElementById("busqueda").style.display="block";
            	document.getElementById("contenido").style.display="none";
            }
        </script>
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
    					<li><a href="#"><button onClick="cargarContenido('principal')">Principal</button></a></li>
    					<li><a href="#"><button onClick="VerCapaBuscar()">Buscar eventos</button></a></li>
    					<li><a href="#"><button onClick="cargarContenido('info')">Sobre Ticket Finder</button></a></li>
    					<?php
    					   if(!isset($_SESSION["usuario"])){
    					       echo "<li><a href='app/login.html'><button>Registrarse o acceder</button></a></li>";
    					   }else{
    					       echo "<li><a href='./app/contenido/logout.php'><button>Bienvenido/a, ".$_SESSION["usuario"]->getCuenta()."</button></a></li>";
    					   }
    					?>
    				</ul>
    			</nav>
    		</header>
    		<br>
    		<!-- El bloque de búsqueda tiene que estar presente en el DOM del documento, pero se oculta
    		 hasta que se llama a su función de JS. -->
    		<div id="busqueda" class="contenedor" style="display:none;">
            	<table>
                	<tr>
                    	<td>Nombre del artista:</td>
                        <td><input type="text" id="artista" required></td>
                    </tr>
                    <tr>
                    	<td>Localidad del evento:</td>
                       	<td><input type="text" id="lugar" required></td>
                    </tr>
                    <tr>
                    	<td>Fecha:</td>	
                       	<td><input type="date" id="fecha" required></td>
                    </tr>
            	</table>
            	<details>
                	<p>Se buscarán eventos de la fecha seleccionada en adelante.</p>
                </details>
                <br>
                <button type="button" id="busca" name="busca">Buscar</button>
            </div>
    		<!-- Bloque de contenido dinámico (AJAX) -->
    		<div id="contenido" class="contenedor">
    			<?php
    			    echo file_get_contents("./app/contenido/principal.html");
    			?>
    		</div>
    	</div>
    </body>
    <script type="text/javascript">
    	//Función para la llamada a AJAX desde JQuery:
    	$('#busca').on("click", function(){
        	var key = "";
        	var artista = $("#artista").val();
			var lugar = $("#lugar").val();
			var fecha = $("#fecha").val();

			if(artista === "" || lugar === "" || fecha === ""){
				alert("Todos los campos del formulario de búsqueda tienen que tener un valor.");
				return;
			}
        	
    		var request = $.ajax({
        		url: 'https://api.songkick.com/api/30.0/events.json?apikey='+key+'&artist_name='+artista+'&location='+lugar+'&min_date='+fecha,
        		method: 'GET'
        	});
    
        	request.done(function(data){
            	//Solo en caso de haber hecho correctamente la request (aunque no devuelva nada) se 
            	//vuelve a ver el bloque de contenido en lugar del formulario de búsqueda.
        		document.getElementById("busqueda").style.display="none";
            	document.getElementById("contenido").style.display="block";
            	alert(data);
            	if(data.count <= 0){
                	var resp = "<br>No se ha encontrado ningún resultado.<br>";
                	$("#contenido").html(resp);
                }else{
                    var resultado = "<ul>";
                	for(var i = 0; i < data.results.length; i++){
                		//Generar tabla con datos...
                	}
                	resultado += "</ul>";
                	$("#contenido").html(resultado);
                }
        	});
        	request.fail(function(error){
            	alert("Error: "+error);
            });
    	}); 
    </script>
</html>