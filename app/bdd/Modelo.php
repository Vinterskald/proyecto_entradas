<?php
    class Modelo{
        private static $dbh = null; 
        private static $consulta_user = "SELECT Cuenta FROM usuarios WHERE Cuenta = ?";
        private static $password = "SELECT Contraseña FROM usuarios WHERE Cuenta = ?";
        private static $consulta_correo = "SELECT Correo FROM usuarios WHERE Correo = ?";
        
        public static function init(){
            if(self::$dbh == null){
                try{
                    $dsn = "mysql:host=localhost;dbname=proyecto_entradas;charset=utf8";
                    self::$dbh = new PDO($dsn, "root", "root");
                    // Si se produce un error se genera una excepción;
                    self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }catch(PDOException $e){
                    echo "Error de conexión con la base de datos.".$e->getMessage();
                    exit();
                }
            } 
        }
        
        //Comprueba que usuario y contraseña son correctos
        public static function checkUser($user,$pass){
            $userLC = strtolower($user);
            $stmt = self::$dbh->prepare(self::$consulta_user);
            $stmt->bindValue(1,$userLC);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $fila = $stmt->fetch();
                if(this.checkPass($user, $fila[$user])){
                    return true;
                }
            }
            return false;
        }
        
        //Comprueba la contraseña (sin cifrado) contra la que se pase por parámetro en el formulario:
        public static function checkPass(string $user, string $pass):bool{
            $userLC = strtolower($user);
            $stmt = self::$dbh->prepare(self::$password);
            $stmt->bindValue(1, $userLC);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                return true;
            }
            return false;
        }
        
        //Comprueba si ya existe un usuario con ese nombre de usuario:
        public static function existeCuenta(String $user):bool{
            $userLC = strtolower($user);
            $stmt = self::$dbh->prepare(self::$consulta_user);
            $stmt->bindValue(1, $userLC);
            $stmt->execute();
            if($stmt->rowCount() > 0){ 
                return true;
            }
            return false;
        }
        
        //Comprueba si existe la dirección de correo en la BD:
        public static function existeCorreo(String $user):bool{
            $userLC = strtolower($user);
            $stmt = self::$dbh->prepare(self::$consulta_correo);
            $stmt->bindValue(1, $userLC);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                return true;
            }
            return false;
        }
        
        //Añadir un nuevo usuario:
        public static function userAdd(string $user, string $pass, string $pass2, string $nom, $apell, string $correo, string $direc){
            $error = self::errorValoresAlta($user, $pass, $pass2, $nom, $apell, $correo, $direc);
            if($error == false){
                $query = "INSERT INTO usuarios VALUES(NULL, ?, ?, ?, ?, ?, ?)";
                $stmt = self::$dbh->prepare($query);
                $stmt->bindValue(1, $pass);
                $stmt->bindValue(2, $nom);
                $stmt->bindValue(3, $apell);
                $stmt->bindValue(4, $user);
                $stmt->bindValue(5, $correo);
                $stmt->bindValue(6, $direc);
                if($stmt->execute()){
                    return true;
                }
                return false;
            }
            return $error;
        }
        
        /*
         * Comprueba que la contraseña es segura
         */
        public static function esClaveSegura(String $clave):bool{
            if(empty($clave)) return false;
            if(strlen($clave) < 8) return false;
            if(!self::hayMayusculas($clave) || !self::hayMinusculas($clave)) return false;
            if(!self::hayDigito($clave)) return false;
            if(!self::hayNoAlfanumeric($clave)) return false;
            return true;
        }
        
        //Chequeo de valores para añadir el usuario:
        public static function errorValoresAlta($user, $pass1, $pass2, $nombre, $apellidos, $email, $direccion){
            if(strlen($user) <= 0 || strlen($user > 50))      return "Longitud de nombre de usuario no válida.";
            if(self::existeCorreo($user))                     return "Ya existe el correo introducido.";
            if(preg_match("/^[a-zA-Z0-9]+$/", $user) == 0)    return "El nombre de usuario no es válido.";
            if($clave1 != $clave2)                            return "Las contraseñas no coinciden.";
            if(!self::esClaveSegura($clave1))                 return "La contraseña no es válida (mínimo 8 caracteres, 1 dígito, 1 carácter alfanumérico, 1 mayúscula y 1 minúscula).";
            if(!filter_var($email, FILTER_VALIDATE_EMAIL))    return "El correo introducido no es válido."; //Filtro de variables nativo de PHP.
            return false;
        }
        
        //Borrar un usuario (boolean)
        public static function userDel($user):bool{
            $query = "DELETE FROM usuarios WHERE Cuenta = ?";
            $stmt = self::$dbh->prepare($query);
            $stmt->bindValue(1, strtolower($user));
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            if($stmt->execute()){
                return true;
            }
            return false;
        }
        
        //Tabla de todos los usuarios para visualizar:
        public static function getAll():array{
            // Genero los datos para la vista que no muestra la contraseña ni los códigos de estado o plan
            // sino su traducción a texto  PLANES[$fila['plan']],
            $stmt = self::$dbh->query("SELECT * FROM usuarios");
            
            $tUserVista = [];
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            while ($fila = $stmt->fetch()){
                $datosuser = [
                    $fila['Cuenta'],
                    $fila['Correo'],
                ];
                $tUserVista[$fila['ID']] = $datosuser;
            }
            return $tUserVista;
        }
        
        //Datos de un único usuario para visualizar:
        public static function userGet($cuenta){
            $stmt = self::$dbh->prepare("SELECT * FROM usuarios where Cuenta = ?");
            $stmt->bindValue(1, strtolower($cuenta));
            $userVista = [];
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                while($fila = $stmt->fetch()){
                    $userVista = [$fila["ID"], $fila["Contraseña"], $fila["Nombre"], $fila["Apellidos"], $fila["Cuenta"], $fila["Correo"], $fila["Dirección"]];
                }
                return $userVista;
            }
            return false;
        }
        
        //Cerrrar la conexión abierta (dejar la instancia a nulo).
        public static function closeDB(){
            self::$dbh = null;
        }
        //----------------------------------------------------------------------------------------------------------------------
        //Funciones para el checkeo de la contraseña:
        function hayMayusculas($clave){
            if(strtolower($clave) != $clave){
                return true;
            }
            return false;
        }
        
        function hayMinusculas($clave){
            if(strtoupper($clave) != $clave){
                return true;
            }
            return false;
        }
        
        function hayDigito($clave){
            return preg_match("/\d/", $clave);
        }
        
        function hayNoAlfanumeric($clave){
            if(!ctype_alnum($clave)){ //Si todos los caracteres fueran alfanuméricos, no sería correcto.
                return true;
            }
            return false;
        }
    }
?>